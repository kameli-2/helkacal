$().ready(function(){
	$("#fb_import").hide();

	$("#open_fb_import").click(function(e){
		e.preventDefault();
		$("#fb_import").toggle();
	});

	/* If start day is set and end day is before start day,
	   set start day as end day too. Also vice versa. */
	$('#addEventFields_startday').on('inputchange', function(){
		var startday = new Date($(this).val());
		var endday = new Date($('#addEventFields_endday').val());
		if (Number(startday) > Number(endday) || !Number(endday)) $('#addEventFields_endday').val($(this).val());
	});
	$('#addEventFields_endday').on('inputchange', function(){
		var startday = new Date($('#addEventFields_startday').val());
		var endday = new Date($(this).val());
		if (Number(startday) > Number(endday) || !Number(startday)) $('#addEventFields_startday').val($(this).val());
	});

	/* Don't submit form on enter */
	$(window).keydown(function(event) {
		if(event.keyCode == 13) {
			event.preventDefault();
			/* Search map on enter */
			if ($(".gllpSearchField").is(":focus")) {
				$(".gllpSearchButton").click();
			}
			else if ($("#addEventFields_contact").is(":focus")) {
				$("#addEventFields_contact").val($("#addEventFields_contact").val()+"\n");
			}
			else if ($("#addEventFields_eventDesc").is(":focus") && !$("#addEventFields_eventDesc_parent")[0]) {
				$("#addEventFields_eventDesc").val($("#addEventFields_eventDesc").val()+"\n");
			}
			return false;
		}
	});
});
