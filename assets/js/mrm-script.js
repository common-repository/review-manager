jQuery(document).ready(function ($) {

    var rmtop = $("#rmtop");
    var rmbottom = $("#rmbottom");
    var slidesPerPage = 4; //globaly define number of elements per page
    var syncedSecondary = true;

    rmtop.owlCarousel({
        items: 1,
        slideSpeed: 2000,
        nav: true,
        autoplay: false,
        dots: false,
        loop: true,
        responsiveRefreshRate: 200,
        navText: ["<img src='/wp-content/plugins/review-manager/assets/images/angle-pre.png'>", "<img src='/wp-content/plugins/review-manager/assets/images/angle-next.png'>"],
    }).on('changed.owl.carousel', syncPosition);

    rmbottom
            .on('initialized.owl.carousel', function () {
                rmbottom.find(".owl-item").eq(0).addClass("current");
            })
            .owlCarousel({
                items: slidesPerPage,
                dots: false,
                nav: false,
                smartSpeed: 200,
                slideSpeed: 500,
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 1
                    },
                    1000: {
                        items: 4
                    }
                },
                slideBy: slidesPerPage, //alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
                responsiveRefreshRate: 100
            }).on('changed.owl.carousel', syncPosition2);

    function syncPosition(el) {
        //if you set loop to false, you have to restore this next line
        //var current = el.item.index;

        //if you disable loop you have to comment this block
        var count = el.item.count - 1;
        var current = Math.round(el.item.index - (el.item.count / 2) - .5);

        if (current < 0) {
            current = count;
        }
        if (current > count) {
            current = 0;
        }

        //end block

        rmbottom
                .find(".owl-item")
                .removeClass("current")
                .eq(current)
                .addClass("current");
        var onscreen = rmbottom.find('.owl-item.active').length - 1;
        var start = rmbottom.find('.owl-item.active').first().index();
        var end = rmbottom.find('.owl-item.active').last().index();

        if (current > end) {
            rmbottom.data('owl.carousel').to(current, 100, true);
        }
        if (current < start) {
            rmbottom.data('owl.carousel').to(current - onscreen, 100, true);
        }
    }

    function syncPosition2(el) {
        if (syncedSecondary) {
            var number = el.item.index;
            rmtop.data('owl.carousel').to(number, 100, true);
        }
    }

    rmbottom.on("click", ".owl-item", function (e) {
        e.preventDefault();
        var number = $(this).index();
        rmtop.data('owl.carousel').to(number, 300, true);
    });

// Popup

    $(".rm-onclick").on('click', function () {
        var itemid = $(this).attr('itemid');
        $("#rm-modal-" + itemid).addClass('model-open');
    });
    $(".rm-close-btn, .bg-overlay").click(function () {
        $(".rm-model").removeClass('model-open');
    });
});