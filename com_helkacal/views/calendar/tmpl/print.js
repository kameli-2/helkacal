$(document).ready(function(){
	$(".helkacal_event").mouseup(function(e){
		switch (e.which) {
			case 1:
				$(this).remove();
				arrangeEvents();
				break;
			case 2:
			case 3:
				$(this).toggleClass("helkacal_featured");
				arrangeEvents();
				break;
		}
	});
	$(".helkacal_continuous_title, .helkacal_eventdate").click(function(){
		$(this).parent().remove();
		arrangeEvents();
	});

	$(".helkacal_continuous_title, .helkacal_eventdate").mouseenter(function(){
		$(this).parent().addClass("helkacal_print_hover");
	});
	$(".helkacal_continuous_title, .helkacal_eventdate").mouseleave(function(){
		$(this).parent().removeClass("helkacal_print_hover");
	});

	setTimeout(function(){
		arrangeEvents();
		justifyLetters($("#helkacal_print_month"));
	}, 1000);
});

function justifyLetters(element) {
	var spacing = 0.01;
	var maxwidth = 323;

	$(element).css("letter-spacing", spacing+"em");		

	while ($(element).width() < maxwidth) {
		spacing += 0.005;
		$(element).css("letter-spacing", spacing+"em");
	}
	spacing -= 0.005;
	$(element).css("letter-spacing", spacing+"em");
}

function arrangeEvents() {
	var margin = 30;
	var startheight = $("#helkacal_print_header").outerHeight()+margin+1;
	var x = margin;
	var y = startheight;
	var columnwidth = ($("body").width()-2*margin)/3+3;
	var maxheight = $("body").height()-margin-15;
	/* Remove dividers and recreate them */
	$(".helkacal_divider").remove();
	$(".helkacal_eventsofoneday:not(.helkacal_continuous), .helkacal_continuous > div").each(function(){
		if ($(this).hasClass("helkacal_event")) $(this).css("border-top", "");
		if (y + $(this).outerHeight() > maxheight && y != startheight) {
			x += columnwidth;
			y = startheight;
			var divider = document.createElement("DIV");
			$(this).after(divider);
			$(divider).addClass("helkacal_divider");
			$(divider).css("left", x-6);
			$(divider).css("top", y);
			$(divider).css("height", maxheight-startheight);
			if ($(this).hasClass("helkacal_event")) $(this).css("border-top", "none");
		}
		$(this).css("position", "absolute");
		$(this).css("left", x);
		$(this).css("top", y);
/*		if (x <= columnwidth+margin) $(this).addClass("right-border");
		else $(this).removeClass("right-border");
*/		y += $(this).outerHeight();
		if ($(this).hasClass("helkacal_event")) y += 10;
	});
}
