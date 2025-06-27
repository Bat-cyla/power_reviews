{if $discussion.cp_pr_slider}
    <h4 class="ty-subheader">{__("cp_pr_customer_images")}</h4>
    {$obj_prefix="pr_img_slider_`$discussion.thread_id`"}
    
    {$cp_outside_navigation="N"}
    {if $cp_outside_navigation == "Y"}
        <div class="owl-theme ty-owl-controls">
            <div class="owl-controls clickable owl-controls-outside"  id="owl_outside_nav_{$obj_prefix}">
                <div class="owl-buttons">
                    <div id="owl_prev_{$obj_prefix}" class="owl-prev"><i class="ty-icon-left-open-thin"></i></div>
                    <div id="owl_next_{$obj_prefix}" class="owl-next"><i class="ty-icon-right-open-thin"></i></div>
                </div>
            </div>
        </div>
    {/if}
    {$slider_img_size=$slider_size*3}
    <div id="{$obj_prefix}" class="cp-pr__slider-images {if !$cp_skip_prev_wrap}cm-preview-wrapper{/if} owl-carousel ty-scroller-list">
        {$cp_pr_order=0}
        {if $discussion.cp_pr_slider.videos}
            {foreach from=$discussion.cp_pr_slider.videos item="pr_videos"}
                <div class="ty-scroller-list__item" style="max-width: {$slider_size}px; max-height:{$slider_size}px;">
                    <span class="cp-post-bl-img cp-pr__post-video" data-cp-vid="{$pr_videos.video_id}" data-cp-ulink="{$smarty.const.CP_PR_YOUTUBE_PLAYER_URL}" data-cp-uid="{$pr_videos.youtube_id}" 
                        {if $pr_videos.preview}data-cp-obji="{$obj_prefix}_{$pr_videos.preview.detailed_id}"{else}data-cp-obji="{$obj_prefix}_cp_pr_def_icon_{$cp_pr_order}"{/if}
                        style="max-width: {$slider_size}px; max-height:{$slider_size}px;"
                    >
                        {if $pr_videos.preview}
                            {include file="addons/cp_power_reviews/components/post_image.tpl" images=$pr_videos.preview link_class="cm-image-previewer cp-pr-previewer" obj_id="`$obj_prefix`_`$pr_videos.preview.detailed_id`" image_width=$slider_img_size image_height=$slider_img_size image_id="preview[`$obj_prefix`]" show_detailed_link=true}
                        {elseif $pr_videos.preview_def}
                            <a id="det_img_link_{$obj_prefix}_cp_pr_def_icon_{$cp_pr_order}" {if $cp_pr_order}data-ca-image-order="{$cp_pr_order}"{/if} data-ca-image-id="preview[{$obj_prefix}]" class="cm-image-previewer cp-pr-previewer" data-ca-image-width="600" data-ca-image-height="600" href="{$pr_videos.preview_def}" title="">
                                <img class="ty-pict cm-image" id="det_img_{$obj_prefix}_cp_pr_def_icon_{$cp_pr_order}" src="{$pr_videos.preview_def}" width="{$slider_size}" height="{$slider_size}" />
                            </a>
                        {/if}
                    </span>
                    {$cp_pr_order=$cp_pr_order + 1}
                </div>
            {/foreach}
        {/if}
        {if $discussion.cp_pr_slider.images}
            {foreach from=$discussion.cp_pr_slider.images item="pr_img"}
                <div class="ty-scroller-list__item" style="max-width: {$slider_size}px; max-height:{$slider_size}px;">
                    {$obj_id="`$obj_prefix`_`$pr_img.pair_id`"}
                    {include file="addons/cp_power_reviews/components/post_image.tpl" images=$pr_img image_width=$slider_img_size image_height=$slider_img_size image_id="preview[`$obj_prefix`]" show_detailed_link=true lazy_load=false link_class="cm-image-previewer cp-pr-previewer"}
                    {$cp_pr_order=$cp_pr_order + 1}
                </div>
            {/foreach}
        {/if}
    </div>

    {$prev_selector="#owl_prev_`$obj_prefix`"}
    {$next_selector="#owl_next_`$obj_prefix`"}
    
    {script src="js/lib/owlcarousel/owl.carousel.min.js"}
    <script type="text/javascript">
    (function(_, $) {
        $.ceEvent('on', 'ce.commoninit', function(context) {
            var elm = context.find('#{$obj_prefix}');

            $('.ty-float-left:contains(.ty-scroller-list),.ty-float-right:contains(.ty-scroller-list)').css('width', '100%');

            var item = {$slider_qty|default:5},
                // default setting of carousel
                itemsDesktop = 4,
                itemsDesktopSmall = 3;
                itemsTablet = 2;
            if (item > 3) {
                itemsDesktop = item;
                itemsDesktopSmall = item - 1;
                itemsTablet = item - 2;
            } else if (item == 1) {
                itemsDesktop = itemsDesktopSmall = itemsTablet = 1;
            } else {
                itemsDesktop = item;
                itemsDesktopSmall = itemsTablet = item - 1;
            }

            var desktop = [1199, itemsDesktop],
                desktopSmall = [979, itemsDesktopSmall],
                tablet = [768, itemsTablet],
                mobile = [479, 1];
            function outsideNav () {
                {if $cp_outside_navigation == "Y"}
                    if(this.options.items >= this.itemsAmount){
                        $("#owl_outside_nav_{$obj_prefix}").hide();
                    } else {
                        $("#owl_outside_nav_{$obj_prefix}").show();
                    }
                {/if}
                var slide_img = $('.cp-pr__slider-images.owl-carousel .owl-item').first();
                if (slide_img && slide_img.length > 0) {
                    var item_width = slide_img.width();
                    var opt_width ="{$slider_size}";
                    if (opt_width < item_width) {
                        $('.cp-pr__slider-images.owl-carousel .ty-scroller-list__item').css('width', opt_width);
                        $('.cp-pr__slider-images.owl-carousel .ty-scroller-list__item').css('height', opt_width);
                    } else {
                        $('.cp-pr__slider-images.owl-carousel .ty-scroller-list__item').css('width', item_width);
                        $('.cp-pr__slider-images.owl-carousel .ty-scroller-list__item').css('height', item_width);
                    }
                }
            }

            if (elm.length) {
                elm.owlCarousel({
                    direction: '{$language_direction}',
                    items: item,
                    itemsDesktop: desktop,
                    itemsDesktopSmall: desktopSmall,
                    itemsTablet: tablet,
                    itemsMobile: mobile,
                    scrollPerPage: true,
                    autoPlay: false,
                    lazyLoad: true,
                    slideSpeed: 400,
                    stopOnHover: true,
                {if $cp_outside_navigation == "N"}
                    navigation: true,
                    navigationText: ['<i class="ty-icon-left-open-thin"></i>', '<i class="ty-icon-right-open-thin"></i>'],
                {/if}
                    pagination: false,
                    afterInit: outsideNav,
                    afterUpdate : outsideNav
                });
            {if $cp_outside_navigation == "Y"}
                $('{$prev_selector}').click(function(){
                    elm.trigger('owl.prev');
                });
                $('{$next_selector}').click(function(){
                    elm.trigger('owl.next');
                });
            {/if}
            }
        });
    }(Tygh, Tygh.$));
    </script>
    
{/if}

