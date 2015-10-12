var a;
jQuery( document ).ready(function( $ ){
        /* Read get variables */
        var parts = window.location.search.substr(1).split("&");
        var $_GET = {};
        for (var i = 0; i < parts.length; i++) {
                var temp = parts[i].split("=");
                $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
        }

	/* Publish or delete event */
	$(".helkacal_publish, .helkacal_delete, .helkacal_edit").click(function(event){
		/* Don't hide eventbox when clicking on publishing buttons */
		event.stopPropagation();

		a = $(this).attr("id").split("_");

		var r = true;
		if (a[0] == 'delete') {
			r = confirm("Haluatko varmasti poistaa tapahtuman?");
		}

		if (r) {
			$(this).addClass("helkacal_loading");

			var headers = new Array();
			switch (a[0]) {
				case 'publish':
					headers['action'] = 'changeState';
					headers['state'] = 1;
					break;
				case 'delete':
					headers['action'] = 'changeState';
					headers['state'] = -2;
					break;
				case 'edit':
					headers['action'] = 'showEditForm';
					break;
			}
			headers['articleid'] = a[1];

			if (a[0] == 'edit') {
				$("#helkacal_editform_articleid").val(a[1]);
				$("#helkacal_editform_returnurl").val(document.URL);
				$("#helkacal_editform").submit();
			}
			else {
				$.ajax({
					type: "GET",
					url: getPath()+"php/ajax.php",
					data: {
						action: 'changeState',
						state: headers['state'],
						articleid: a[1]
					}
				}).always(function(data){
					if (a[0] == 'delete') {
						$("#"+a[0]+"_"+a[1]).parents("div.helkacal_event").hide('slow', function(){$("#"+a[0]+"_"+a[1]).parents("div.helkacal_event").remove()});
						$("#"+a[0]+"_"+a[1]).addClass("helkacal_success");
					}
					if (a[0] == 'publish') {
						$("#"+a[0]+"_"+a[1]).addClass("helkacal_success");
						$("#"+a[0]+"_"+a[1]).attr("id", "");
					}
				});
			}
		}
	});


	/* Category filter */
	$("#helkacal_catfiltercontainer .helkacal_category").click(function(){
		a = $(this).attr("id").split("-");
		a = a[1];

		/* When clicking on the "all"-symbol or the currently chosen category, show all events */
		if (a == 'all' || (!$(this).hasClass("helkacal_catinactive") && $(".helkacal_catinactive")[0])) {
			$("#helkacal_catfiltercontainer .helkacal_category").removeClass("helkacal_catinactive");
			$(".helkacal_event").show('fast');
		}
		/* Otherwise show only the chosen category */
		else {
			$("#helkacal_catfiltercontainer .helkacal_category").addClass("helkacal_catinactive");
			$(this).removeClass("helkacal_catinactive");
			$(".helkacal_category"+a).show('fast');
			$(".helkacal_event").not(".helkacal_category"+a).hide('fast');
		}
	});

	/* Put event image as background image for date */
	$(".helkacal_event").each(function(){
                var image = $(this).find(".helkacal_introtext img");
                if( image.length > 0){
                        if(image.length >= 2){
                                image=$(image[0]);
                        }
			$(this).find(".helkacal_date").css("background-image", "url("+image.attr("src")+")").addClass("helkacal_bgimage");
		}
	});


	/* Classify event images as large or small */
	$(".helkacal_eventpage p img").each(function(){
		var image = $(this);
		if( image.length > 0){
			if(image.length >= 2){
				image=$(image[0]);
			}
			image.on('load', function(){
				image.removeAttr("height");
				if(image.width() <= 250){
					image.addClass("small-picture");
					$(this).addClass("small-picture");
				} else {
				console.log(image.attr("src"));
					image.addClass("large-picture");
					$(this).addClass("large-picture");
				}

				$(this).before(image);
				$(this).find("img").remove();
			});
		}
	});


	/* Social media buttons hover effect */

	$(".helkacal_some img.twitter-logo, .helkacal_some img.fb-logo").hover(function(){
		changeImage(this, true)
		}, function(){
		changeImage(this, false);
	});

	if (typeof $_GET['chosencategory'] != null) $("#helkacal_catfilter-"+$_GET['chosencategory']).click();


});

function changeImage(e, color) {
    var src = e.src;
    var path = src.substring(0,src.lastIndexOf('/'));
    var filename = src.substring(src.lastIndexOf('/'), src.lastIndexOf('.'));
    var filetype = src.substring(src.lastIndexOf('.'));
    if(color) {
        e.src = path  + filename + "_color" + filetype;
    } else {
        e.src = path  + filename.replace('_color', '') + filetype;
    }
}
