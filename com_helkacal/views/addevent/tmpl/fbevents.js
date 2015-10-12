// This is called with the results from from FB.getLoginStatus().
function statusChangeCallback(response) {
//    console.log('statusChangeCallback');
//    console.log(response);
    // The response object is returned with a status field that lets the
    // app know the current login status of the person.
    // Full docs on the response object can be found in the documentation
    // for FB.getLoginStatus().
    if (response.status === 'connected') {
      // Logged into your app and Facebook.
      FB.api('/me', function(response) {
        document.getElementById('status').innerHTML =
        'Olet kirjautuneena sis&auml;&auml;n nimell&auml; ' + response.name + '.';
      });
      $("#fb-import-help, #hae, #fb-event-id").show();
    } else if (response.status === 'not_authorized') {
      // The person is logged into Facebook, but not your app.
      document.getElementById('status').innerHTML = 'Kirjaudu sis&auml;&auml;n ' +
        'Kaupunginosat.net-applikaatioon k&auml;ytt&auml;&auml;ksesi ' +
	't&auml;t&auml; toimintoa.';
    } else {
      // The person is not logged into Facebook, so we're not sure if
      // they are logged into this app or not.
      document.getElementById('status').innerHTML = 'Kirjaudu sis&auml;&auml;n ' +
        'Facebookiin k&auml;ytt&auml;&auml;ksesi ' +
        't&auml;t&auml; toimintoa.';
    }
}

// This function is called when someone finishes with the Login
// Button.  See the onlogin handler attached to it in the sample
// code below.
function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
}

window.fbAsyncInit = function() {
  FB.init({
    appId      : '524317161013248',
    cookie     : true,  // enable cookies to allow the server to access 
                        // the session
    xfbml      : true,  // parse social plugins on this page
    version    : 'v2.3' // use version 2.3
  });

  // Now that we've initialized the JavaScript SDK, we call 
  // FB.getLoginStatus().  This function gets the state of the
  // person visiting this page and can return one of three states to
  // the callback you provide.  They can be:
  //
  // 1. Logged into your app ('connected')
  // 2. Logged into Facebook, but not your app ('not_authorized')
  // 3. Not logged into Facebook and can't tell if they are logged into
  //    your app or not.
  //
  // These three cases are handled in the callback function.

  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });

};

// Load the SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

$(document).ready(function(){
  $("#hae").click(function(){
    $("#errormessage").hide();
    var eventid = $("#fb-event-id").val().split("?")[0].split("/");
    var i = 0;
    var found = false;
    eventid.some(function(possibleid) {
	i++;
	if (possibleid == parseInt(possibleid)) {
		FB.api('/v2.3/'+possibleid+'?fields=name,description,owner,start_time,end_time,is_date_only,place', function(response) {
			if (typeof response != 'undefined') {
			    if (typeof response.name != 'undefined') {
				found = true;
				$("#errormessage").hide();

				$("#addEventFields_eventName").val(response.name);
				if (typeof response.description != 'undefined') {
					// If user is using TinyMCE (or JCE), check if editor is being used or not.
					// If it is, we must click "toggle editor" before and after inserting event description.
					var visible = false;
					if ($("#addEventFields_eventDesc").is(':visible')) visible = true;
					if (!visible) $("#wf_editor_addEventFields_eventDesc_toggle").click();
					$("#addEventFields_eventDesc").val(response.description);
					if (!visible) $("#wf_editor_addEventFields_eventDesc_toggle").click();
				}
				if (typeof response.owner != 'undefined') $("#addEventFields_contact").val(response.owner.name);
				if (typeof response.start_time != 'undefined') {
					var starttime = response.start_time.split("T");
					$("#addEventFields_startday").val(starttime[0]);
					if (typeof starttime[1] != 'undefined') {
						var startclock = starttime[1].split("+")[0].split(":");
						$("#addEventFields_starthour").val(startclock[0]);
						$("#addEventFields_startminute").val(startclock[1]);
						$("#addEventFields_allday").val(false);
					}
					else {
						$("#addEventFields_allday").val(true);
					}
				}
				if (typeof response.end_time != 'undefined') {
					var endtime = response.end_time.split("T");
					$("#addEventFields_endday").val(endtime[0]);
					if (typeof endtime[1] != 'undefined') {
						var endclock = endtime[1].split("+")[0].split(":");
						$("#addEventFields_endhour").val(endclock[0]);
						$("#addEventFields_endminute").val(endclock[1]);
					}
				}
				if (typeof response.is_date_only != 'undefined') $("#addEventFields_allday").val(response.is_date_only);
				if (typeof response.place != 'undefined') {


					if (typeof response.place.location.latitude != 'undefined' && typeof response.place.location.longitude != 'undefined') {
						$(".gllpSearchField").val(response.place.location.latitude+','+response.place.location.longitude);
						$(".gllpSearchButton").click();
					}

//					if (typeof response.place.location.latitude != 'undefined') $(".gllpLatitude").val(response.place.location.latitude);
//					if (typeof response.place.location.longitude != 'undefined') $(".gllpLatitude").val(response.place.location.longitude);
					
					var placename = '';
					if (typeof response.place.name != 'undefined') placename = response.place.name;
					else {
						if (typeof response.place.location.street != 'undefined') placename = response.place.location.street;
						if (typeof response.place.location.zip != 'undefined') placename += ', '+response.place.location.zip;
						if (typeof response.place.location.city != 'undefined') placename += ' '+response.place.location.city;
					}
					if (placename != '') setTimeout(function(){ $("#location_street_address").val(placename); }, 250);
					$(".gllpZoom").val(11);
				}
				else $("[name=no_location]").val(true);

				$("#addEventFields_url").val('http://www.facebook.com/events/'+possibleid);
				return true;
			    }
			}
		});
	}
	setTimeout(function(){
		if (i >= eventid.length && !found) {
		        $("#errormessage").html("Tapahtumaa ei l&ouml;ytynyt. Tarkista, ett&auml; sy&ouml;tit oikean osoitteen ja ett&auml; tapahtuma on julkinen.");
			$("#errormessage").show();
		}
	}, 250);
    });
  });
});
