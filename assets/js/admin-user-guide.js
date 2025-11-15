/** User Guide JavaScript - @package FlowQ */
(function($) {
jQuery(document).ready(function($) {
    // Smooth scrolling for TOC links
    $('.toc-list a, .guide-back-to-top a').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');

        if (target === '#top') {
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        } else {
            var targetElement = $(target);
            if (targetElement.length) {
                $('html, body').animate({
                    scrollTop: targetElement.offset().top - 50
                }, 500);
            }
        }
    });

    // Highlight active section in TOC
    $(window).on('scroll', function() {
        var scrollPos = $(window).scrollTop() + 100;

        $('.guide-content-inner h2').each(function() {
            var section = $(this);
            var sectionTop = section.offset().top;
            var sectionBottom = sectionTop + section.outerHeight();
            var sectionId = section.attr('id');

            if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                $('.toc-list a').removeClass('active');
                $('.toc-list a[href="#' + sectionId + '"]').addClass('active');
            }
        });
    });
});
})(jQuery);
