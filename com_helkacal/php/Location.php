<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tydlyn
 *
 * @copyright   Copyright (C) 2005 - 2013 Habib MAALEM, Inc. All rights reserved.
 * @license     The MIT License (MIT); see	LICENSE
 */

defined('_JEXEC') or die;

/**
 * Location field for the Tydlyn package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tydlyn
 * @since       2.5
 */
class JFormFieldLocation extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Location';

	protected $lat  = '60.173479';

	protected $lng  = '24.941041';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = array();

		$lat            = $this->element['lat'] ? (string) $this->element['lat'] : $this->lat;
		$lng            = $this->element['lng'] ? (string) $this->element['lng'] : $this->lng;
		$autocomplete   = ((string) $this->element['autocomplete'] == 'true') ? true : false;
		$typeahead      = $autocomplete ? 'data-provide="typeahead" autocomplete="off"' : '';
		$class          = $autocomplete ? '' : 'class="input-append"';
		$prefix         = $this->formControl . '_';

		$html[]         = '<div ' . $class . '>';
		$html[]         = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="" ' . $typeahead . ' placeholder="Type a location" />';
		if(!$autocomplete)
			$html[]         = '<button type="button" id="' . $this->id . '-load" class="btn btn-success">Search</button>';
		$html[]         = '</div>';
		$html[]         = '<input type="hidden" name="' . $prefix . 'lat" id="' . $prefix . 'lat" value="' . $lat . '" />';
		$html[]         = '<input type="hidden" name="' . $prefix . 'lng" id="' . $prefix . 'lng" value="' . $lng . '" />';

		return implode("\n", $html);
	}

	/**
	 * Method to get the field map.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	public function getMap()
	{
		$html = array();

		// Initialize some field attributes.
		$class          = $this->element['class'] ? ' class="map ' . (string) $this->element['class'] . '"' : ' class="map"';
		$lat            = $this->element['lat'] ? (string) $this->element['lat'] : $this->lat;
		$lng            = $this->element['lng'] ? (string) $this->element['lng'] : $this->lng;
		$autocomplete   = ((string) $this->element['autocomplete'] == 'true') ? true : false;
		$zoom           = $this->element['zoom'] ? (int) $this->element['zoom'] : 10;
		$width          = $this->element['width'] ? (int) $this->element['width'] : 300;
		$height         = $this->element['height'] ? (int) $this->element['height'] : 300;
		$prefix         = $this->formControl . '_';

		$javascript[]   = 'var map;';
		$javascript[]   = 'jQuery(function() {';
		$javascript[]   = '    var service = new google.maps.places.AutocompleteService();';
		$javascript[]   = '    var geocoder = new google.maps.Geocoder();';
		$javascript[]   = '    var hasLocation = false;';
		$javascript[]   = '    var latlng = new google.maps.LatLng(' . $lat . ', ' . $lng . ');';
		$javascript[]   = '    var marker = "";';
		$javascript[]   = '    var options = {';
		$javascript[]   = '        zoom: ' . $zoom . ',';
		$javascript[]   = '        center: latlng,';
		$javascript[]   = '        mapTypeId: google.maps.MapTypeId.ROADMAP';
		$javascript[]   = '    };';
		$javascript[]   = '    if(jQuery("#' . $this->id . '_map").length > 0) {';
		$javascript[]   = '        map = new google.maps.Map(document.getElementById("' . $this->id . '_map"), options);';
		$javascript[]   = '        if(!hasLocation) {';
		$javascript[]   = '            map.setZoom(4);';
		$javascript[]   = '        }';
		$javascript[]   = '        google.maps.event.addListener(map, "click", function(event) {';
		$javascript[]   = '            reverseGeocode(event.latLng);';
		$javascript[]   = '        })';
		$javascript[]   = '        jQuery("#' . $this->id . '-load").click(function() {';
		$javascript[]   = '            if(jQuery("#' . $this->id . '").val() != "") {';
		$javascript[]   = '                geocode(jQuery("#' . $this->id . '").val());';
		$javascript[]   = '                return false;';
		$javascript[]   = '            } else {';
		$javascript[]   = '                marker.setMap(null);';
		$javascript[]   = '                return false;';
		$javascript[]   = '            }';
		$javascript[]   = '            return false;';
		$javascript[]   = '        })';
		$javascript[]   = '        jQuery("#' . $this->id . '").keyup(function(e) {';
		$javascript[]   = '            if(e.keyCode == 13)';
		$javascript[]   = '                jQuery("#' . $this->id . '-load").click();';
		$javascript[]   = '        })';
		$javascript[]   = '    }';
		if($autocomplete)
		{
			$javascript[]   = '    jQuery("#' . $this->id . '").typeahead({';
			$javascript[]   = '        source: function(query, process){';
			$javascript[]   = '            service.getPlacePredictions({ input: query }, function(predictions, status) {';
			$javascript[]   = '                if (status == google.maps.places.PlacesServiceStatus.OK) {';
			$javascript[]   = '                    process(jQuery.map(predictions, function(prediction) {';
			$javascript[]   = '                        return prediction.description;';
			$javascript[]   = '                    }))';
			$javascript[]   = '                }';
			$javascript[]   = '            })';
			$javascript[]   = '        },';
			$javascript[]   = '        updater: function (item) {';
			$javascript[]   = '            geocode(results[0].geometry.location);';
			$javascript[]   = '            return item;';
			$javascript[]   = '        }';
			$javascript[]   = '    })';
		}
		$javascript[]   = '    function placeMarker(location) {';
		$javascript[]   = '        if (marker == "") {';
		$javascript[]   = '            marker = new google.maps.Marker({';
		$javascript[]   = '                position: latlng,';
		$javascript[]   = '                map: map,';
		$javascript[]   = '                title: "Job Location"';
		$javascript[]   = '            })';
		$javascript[]   = '        }';
		$javascript[]   = '        marker.setPosition(location);';
		$javascript[]   = '        map.setCenter(location);';
		$javascript[]   = '        map.setZoom(12);';
		$javascript[]   = '        if((location.lat() != "") && (location.lng() != "")) {';
		$javascript[]   = '            jQuery("#' . $prefix . 'lat").val(location.lat());';
		$javascript[]   = '            jQuery("#' . $prefix . 'lng").val(location.lng());';
		$javascript[]   = '        }';
		$javascript[]   = '    }';
		$javascript[]   = '    function geocode(address) {';
		$javascript[]   = '        if (geocoder) {';
		$javascript[]   = '            geocoder.geocode({"address": address}, function(results, status) {';
		$javascript[]   = '                if (status != google.maps.GeocoderStatus.OK) {';
		$javascript[]   = '                    alert("Cannot find address");';
		$javascript[]   = '                    return;';
		$javascript[]   = '                }';
		$javascript[]   = '                placeMarker(results[0].geometry.location);';
		$javascript[]   = '                reverseGeocode(results[0].geometry.location);';
		$javascript[]   = '                if(!hasLocation) {';
		$javascript[]   = '                    map.setZoom(12);';
		$javascript[]   = '                    hasLocation = true;';
		$javascript[]   = '                }';
		$javascript[]   = '            })';
		$javascript[]   = '        }';
		$javascript[]   = '    }';
		$javascript[]   = '    function reverseGeocode(location) {';
		$javascript[]   = '        if (geocoder) {';
		$javascript[]   = '            geocoder.geocode({"latLng": location}, function(results, status) {';
		$javascript[]   = '                if (status == google.maps.GeocoderStatus.OK) {';
		$javascript[]   = '                    var address, city, country, state;';
		$javascript[]   = '                    for ( var i in results ) {';
		$javascript[]   = '                        var address_components = results[i]["address_components"];';
		$javascript[]   = '                        for ( var j in address_components ) {';
		$javascript[]   = '                            var types = address_components[j]["types"];';
		$javascript[]   = '                            var long_name = address_components[j]["long_name"];';
		$javascript[]   = '                            var short_name = address_components[j]["short_name"];';
		$javascript[]   = '                            if ( jQuery.inArray("locality", types) >= 0 && jQuery.inArray("political", types) >= 0 ) {';
		$javascript[]   = '                                city = long_name;';
		$javascript[]   = '                            }';
		$javascript[]   = '                            else if ( jQuery.inArray("administrative_area_level_1", types) >= 0 && jQuery.inArray("political", types) >= 0 ) {';
		$javascript[]   = '                                state = long_name;';
		$javascript[]   = '                            }';
		$javascript[]   = '                            else if ( jQuery.inArray("country", types) >= 0 && jQuery.inArray("political", types) >= 0 ) {';
		$javascript[]   = '                                country = long_name;';
		$javascript[]   = '                            }';
		$javascript[]   = '                        }';
		$javascript[]   = '                    }';
		$javascript[]   = '                    if((city) && (state) && (country))';
		$javascript[]   = '                        address = city + ", " + state + ", " + country;';
		$javascript[]   = '                    else if((city) && (state))';
		$javascript[]   = '                        address = city + ", " + state;';
		$javascript[]   = '                    else if((state) && (country))';
		$javascript[]   = '                        address = state + ", " + country;';
		$javascript[]   = '                    else if(country)';
		$javascript[]   = '                        address = country;';
		$javascript[]   = '                    jQuery("#' . $this->id . '").val(address);';
		$javascript[]   = '                    placeMarker(location);';
		$javascript[]   = '                    return true;';
		$javascript[]   = '                }';
		$javascript[]   = '            })';
		$javascript[]   = '        }';
		$javascript[]   = '        return false;';
		$javascript[]   = '    }';
		$javascript[]   = '})';

		// Fix Google Map Corrupted Controls in Twitter Bootstrap Modal
		$css[]          = '#' . $this->id . '_map img {';
		$css[]          = '    max-width: none';
		$css[]          = '}';
		$css[]          = '#' . $this->id . '_map label {';
		$css[]          = '    width: auto; display:inline;';
		$css[]          = '}';

		$document       = JFactory::getDocument();
		$document->addScript('http://maps.google.com/maps/api/js?sensor=false&libraries=places');
		$document->addScriptDeclaration(implode("\n", $javascript));
		$document->addStyleDeclaration(implode("\n", $css));

		$html[]         = '<div id="' . $this->id . '_map" ' . $class . 'style="height: ' . $height . 'px; width:"' . $width . 'px;""></div>';

		return implode("\n", $html);
	}
}
?>
