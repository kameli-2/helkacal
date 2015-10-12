jQuery(document).ready(function($) {
    $('img').one('error', function() {
        $(this).parent().addClass("noimage");
        $(this).remove();
    });
    $(function() {
        $("#container").quickPager({
            pageSize: pages,
            holder: ".pagination",
        });
        var num_only = "li:not(.pagination-next, .pagination-prev, .pagination-start, .pagination-end)"
        var parent = $(" .pagination .currentPage");

        $(".pagination ul").append("<li class='pagination-next'><span>&gt;</span></li>");
        $(".pagination ul").prepend("<li class='pagination-prev'><span>&lt;</span></li>");

        $(".pagination .pagination-next").click(function(){
            $(".pagination .next a").click();
        });
        $(".pagination .pagination-prev").click(function(){
            $(".pagination .prev a").click();
        });

    });
});