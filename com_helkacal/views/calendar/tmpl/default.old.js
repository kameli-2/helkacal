var a;
$().ready(function(){
	/* Make category symbol equally high as they are wide */
/*	var cw = $('.helkacal_category').width();
	$('.helkacal_category').css({'height':cw+'px'});
*/
	/* Hide event descriptions at the beginning and show on click */
	$(".helkacal_event .helkacal_eventdesc").hide();
	$(".helkacal_event").click(function(){
		$(".helkacal_eventdesc", this).toggle('fast');
	});

	/* Don't toggle event description when clicking a link */
	$(".helkacal_event a").click(function(event){
		event.stopPropagation();
	});

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
        $("#helkacal_catfiltercontainer .helkacal_category").not("#helkacal_catfilter-all").click(function(){
                $(this).toggleClass("helkacal_catinactive");
                a = $(this).attr("id").split("-");
                $(".helkacal_category"+a[1]).toggle('fast');
                if ($(".helkacal_catinactive").not("#helkacal_catfilter-all").length) $("#helkacal_catfilter-all").addClass("helkacal_catinactive");
                else $("#helkacal_catfilter-all").removeClass("helkacal_catinactive");
        });
        $("#helkacal_catfilter-all").click(function() {
                $(this).removeClass("helkacal_catinactive");
                $("#helkacal_catfiltercontainer .helkacal_category.helkacal_catinactive").not("#helkacal_catfilter-all").click();
        });

});
