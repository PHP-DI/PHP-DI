$(document).scroll(function(){
    // If has not activated (has no attribute "data-top"
    if (!$('.subnav').attr('data-top')) {
        // If already fixed, then do nothing
        if ($('.subnav').hasClass('subnav-fixed')) return;
        // Remember top position
        var offset = $('.subnav').offset();
        $('.subnav').attr('data-top', offset.top);
    }

    if ($('.subnav').attr('data-top') + $('.subnav').outerHeight() <= $(this).scrollTop())
        $('.subnav').addClass('subnav-fixed');
    else
        $('.subnav').removeClass('subnav-fixed');
});

$(function() {
	$("section h1").wrap('<div class="page-header" />');
	$("section p:first-child").addClass('lead');
	$("pre").addClass('prettyprint');
	prettyPrint();
});