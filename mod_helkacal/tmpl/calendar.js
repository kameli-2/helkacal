$().ready(function(){
	/* Read get variables */
	var parts = window.location.search.substr(1).split("&");
	var $_GET = {};
	for (var i = 0; i < parts.length; i++) {
		var temp = parts[i].split("=");
		$_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
	}

	/* Hide all calendars except current month */
	var today = new Date();
	var currentmonth = '';
	currentmonth = today.getMonth()+1;
	if (currentmonth < 10) currentmonth = '0'+currentmonth;

	if (typeof $_GET['date'] != 'undefined') {
	        $date = $_GET['date'].split("-");
        	if (typeof $date[1] != 'undefined') showMonth($date[0], $date[1]);
	        else {
                	if ($date[0] == today.getFullYear()) showMonth($date[0], currentmonth);
        	        else showMonth($date[0], '01');
	        }
	}
	else showMonth(today.getFullYear(), currentmonth);

	/* Click on left- or rightarrow */
	$(".mod_helkacal_leftarrow, .mod_helkacal_rightarrow").click(function(){
		var scope = $(this).parent().attr("class").split("-");
		scope = scope[1];

		var year = $(this).parents(".mod_helkacal_calendar").attr("class").split("-");
		var month = parseInt(year[2]);
		year = parseInt(year[1]);

		var change = 1;
		if ($(this).hasClass("mod_helkacal_leftarrow")) change = -1;

		if (scope == "month") var month = parseInt(month)+change;
		else year = parseInt(year)+change;

		if (month < 1) {
			month += 12;
			year -= 1;
		}
		if (month > 12) {
			month -= 12;
			year += 1;
		}
		if (month < 10) month = "0"+month;

		showMonth(year, month);
	});
});

function showMonth(year, month) {
	if ($(".mod_helkacal_month-"+year+"-"+month)[0]) {
		$(".mod_helkacal_calendar").hide();
		$(".mod_helkacal_month-"+year+"-"+month).show();
	}
}
