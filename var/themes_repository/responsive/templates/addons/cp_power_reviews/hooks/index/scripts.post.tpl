{script src="js/addons/cp_power_reviews/mCustomScrollbar.min.js"}
{if $cp_pr_need_yt_script}
    <script src="//www.youtube.com/iframe_api"></script>
{/if}
<script type="text/javascript">
    (function(_, $) {
        $(document).on('click', '.content-discussion .ab-smc-more', function() {
            $('.content-discussion').css('max-height', 'none');
        });
        $.ceEvent('on', 'dispatch_event_pre', function (e, jelm, processed) {
            if (!processed.status && e.type == 'click') {
                if (jelm.hasClass('cp-pr-previewer') || jelm.parent().hasClass('cp-pr-previewer')) {
                    var lnk = jelm.hasClass('cp-pr-previewer') ? jelm : jelm.parent();
                    lnk.cePreviewer('display');
                    e.stopPropagation();
                    processed.status = true;
                    processed.to_return = false
                }
            }
        });
        var players = [];
        $(".cp-pr__need-scrollbar").mCustomScrollbar({
            theme:"minimal-dark",
            autoHideScrollbar: true
        });
        $(document).on('click', '.cp-pr__slider-images .ty-scroller-list__item a', function() {
            var yt_ids = [];
            $('.cp-pr__slider-images .owl-item').removeClass('active');
            $(this).closest('.owl-item').toggleClass('active');
            $('.cp-pr__slider-images .cp-post-bl-img').each(function (i) {
                var img_id = $(this).attr('data-cp-obji');
                var video_link = $(this).attr('data-cp-ulink');
                var yt_id = $(this).attr('data-cp-uid');
                var video_id = $(this).attr('data-cp-vid');
                if (yt_id && img_id) {
                    setTimeout(function() {
                        var is_owl = true;
                        var prev_img = $('.ty-owl-previewer__image--flex-fix-wrapper #det_img_' + img_id);
                        if (!prev_img || (prev_img && prev_img.length < 1)) {
                            prev_img = $('.ty-swiper-previewer__slide.swiper-slide #det_img_' + img_id);
                            is_owl = false;
                        }
                        if (prev_img && prev_img.length > 0) {
                            var ut_html = '<iframe width="100%" height="100%" src="' + video_link + 'embed/' + yt_id + '?enablejsapi=1" frameborder="0" data-yt-id="ytplayer_' + video_id + '" id="' + yt_id + '" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                        
                            prev_img.replaceWith(ut_html);
                            players[i] = new YT.Player(yt_id);
                            
                            if (is_owl) {
                                var carousel = $('.ty-owl-previewer__container.owl-carousel');
                                if (carousel && carousel.length > 0) {
                                    var owl = carousel.data('owlCarousel').options;
                                    if (owl) {
                                        owl.afterMove = function (item) {
                                            fn_pr_stop_vidosiki(players);
                                        }
                                    }
                                }
                            } else {
                                var pr_swiper = $('.ty-swiper-previewer .swiper-container');
                                mySwiper = pr_swiper[0].swiper;
                                if (mySwiper) {
                                    mySwiper.on('slideChange', function () {
                                        fn_pr_stop_vidosiki(players);
                                    });
                                }
                            }
                        }
                    }, 500);
                }
            });
        });
        $(document).on('click', '.cp-pr__post_images .cp-post-bl-img', function() {
            var img_id = $(this).attr('data-cp-obji');
            var video_link = $(this).attr('data-cp-ulink');
            var yt_id = $(this).attr('data-cp-uid');
            var video_id = $(this).attr('data-cp-vid');
            
            $('.cp-pr__post_images .cp-post-bl-img').removeClass('active owl-item');
            $(this).toggleClass('owl-item active');
            
            if (yt_id && img_id) {
                setTimeout(function() {
                    var is_owl = true;
                    var prev_img = $('.ty-owl-previewer__image--flex-fix-wrapper #det_img_' + img_id);
                    if (!prev_img || (prev_img && prev_img.length < 1)) {
                        prev_img = $('.ty-swiper-previewer__slide.swiper-slide #det_img_' + img_id);
                        is_owl = false;
                    }
                    if (prev_img && prev_img.length > 0) {
                        var ut_html = '<iframe width="100%" height="100%" src="' + video_link + 'embed/' + yt_id + '?enablejsapi=1" frameborder="0" data-yt-id="ytplayer_' + video_id + '" id="' + yt_id + '" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                       
                        prev_img.replaceWith(ut_html);
                        if (is_owl) {
                            $('.ty-owl-previewer__image--flex-fix-wrapper iframe').each(function (i) {
                                players[i] = new YT.Player(yt_id);
                            });
                            var carousel = $('.ty-owl-previewer__container.owl-carousel');
                            if (carousel && carousel.length > 0) {
                                var owl = carousel.data('owlCarousel').options;
                                if (owl) {
                                    owl.afterMove = function (item) {
                                        fn_pr_stop_vidosiki(players);
                                    }
                                }
                            }
                        } else {
                            $('.ty-swiper-previewer__slide.swiper-slide iframe').each(function (i) {
                                players[i] = new YT.Player(yt_id);
                            });
                            var pr_swiper = $('.ty-swiper-previewer .swiper-container');
                            mySwiper = pr_swiper[0].swiper;
                            if (mySwiper) {
                                mySwiper.on('slideChange', function () {
                                    fn_pr_stop_vidosiki(players);
                                });
                            }
                        }
                    }
                }, 500);
            }
        });
        $(document).click(function(e) {
            if (!($(e.target).parents('.cp-pr__attr-data').length > 0 || $(e.target).hasClass('cp-pr__attr-data')) && $(e.target).parents('.nd-rating-stars-block').length < 1) {
                $('.cp-pr__attr-data').addClass('hidden');
            }
            if (!($(e.target).parents('.cp-pr__top-stars-stat').length > 0 || $(e.target).hasClass('cp-pr__top-stars-stat')) && $(e.target).parents('.cp-pr__top-row-stars').length < 1) {
                $('.cp-pr__top-stars-stat').addClass('hidden');
            }
        });
    }(Tygh, Tygh.$));
    $.ceEvent('on', 'ce.commoninit', function(context) {
        $('.cp-show-more-link').click(function() {
            var post_id = $(this).attr('data-post-id');
            $('#first_block_'+post_id).hide();
            $('#second_block_'+post_id).show();
            $('#cp_most_post_message_' + post_id).removeClass('cp-msg-need-height');
        });
        $('.cp-show-less-link').click(function() {
            var post_id = $(this).attr('data-post-id');
            $('#first_block_'+post_id).show();
            $('#second_block_'+post_id).hide();
            $('#cp_most_post_message_' + post_id).addClass('cp-msg-need-height');
        });
    });

function fn_pr_stop_vidosiki(players) {
    if (players && players.length > 0) {
        $(players).each(function(i){
            if (typeof this.pauseVideo != 'undefined') {
                this.pauseVideo();
            }
        });
    }
}
function fn_pr_sort_by_images (elm, res_ids) {
    var href = $(elm).val();
    var is_checked = $(elm).prop('checked');
    if (href) {
        var cur_url = $(elm).attr('data-cp-cur-url');
        if (!is_checked) {
            href = cur_url;
        }
        $.ceAjax('request', fn_url(href), {
            result_ids: res_ids + ',cp_pr_reviews_sorting_block',
            hidden: false,
            full_render: true,
            save_history: true,
        });
    }
}
function fn_pr_change_sorting (href, res_ids) {
    if (href) {
        $.ceAjax('request', fn_url(href), {
            result_ids: res_ids,
            hidden: false,
            full_render: true,
        });
    }
}
function fn_pr_click_likes (href, res_ids, elm, post_id) {
    if (href && post_id) {
        $.ceAjax('request', fn_url(href), {
            result_ids: res_ids,
            hidden: false,
            save_history: false,
            full_render: true,
            callback: function callback(response) {
                var pos_elm = $('.cp_pr_like_in_post_' + post_id);
                var neg_elm = $('.cp_pr_dis_in_post_' + post_id);
                if (response.cp_is_like_added) {
                    if (response.cp_is_like_values.cp_neg_post && neg_elm && neg_elm.length > 0) {
                        neg_elm.html(response.cp_is_like_values.cp_neg_post);
                    }
                    if (response.cp_is_like_values.cp_pos_post && pos_elm && pos_elm.length > 0) {
                        pos_elm.html(response.cp_is_like_values.cp_pos_post);
                    }
                }
                if (response.cp_pr_show_reg_link) {
                    var reg_links = $('.cp-rev-sign-link');
                    if (reg_links && reg_links.length > 0) {
                        reg_links.show();
                    }
                }
            }
        });
    }
}
function fn_pr_click_stars (href, res_ids, show_all, history) {
    if (href) {
        if (show_all) {
            $('.cp-pr__by-starts_all').show();
        } else {
            $('.cp-pr__by-starts_all').hide();
        }
        $.ceAjax('request', fn_url(href), {
            result_ids: res_ids,
            hidden: false,
            save_history: history ? true : false,
            full_render: true,
        });
    }
}
function fn_pr_click_show_stats(id)
{
    if (id) {
        var elm = $('.cp-pr__attr-data[id="' + id + '"]');
        if (elm && elm.length > 0) {
            if (elm.hasClass('hidden')) {
                $('.cp-pr__attr-data').addClass('hidden');
                elm.removeClass('hidden');
            } else {
                $('.cp-pr__attr-data').addClass('hidden');
            }
        }
    }
    return true;
}
function fn_pr_click_show_top_stats(id)
{
    if (id) {
        var elm = $('.cp-pr__top-stars-stat[id="' + id + '"]');
        if (elm && elm.length > 0) {
            if (elm.hasClass('hidden')) {
                elm.removeClass('hidden');
            } else {
                elm.addClass('hidden');
            }
        }
    }
    return true;
}
</script>