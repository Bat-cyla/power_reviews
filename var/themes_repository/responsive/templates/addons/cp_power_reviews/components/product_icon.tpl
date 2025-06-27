{if $post_img && $show_gallery}
    {assign var="cp_obj_id_prefix" value="`$post_id`"}
    <div class="ty-center-block">
        <div class="ty-thumbs-wrapper owl-carousel cm-image-gallery" data-ca-items-count="1" data-ca-items-responsive="true" id="icons_{$cp_obj_id_prefix}">
            {foreach from=$post_img item="image_pair" name="image_pair"}
                {if $image_pair@first}
                    <div class="cm-gallery-item cm-item-gallery">
                        {include file="common/image.tpl" images=$image_pair image_width=$props.post_image_width image_height=$props.post_image_height}
                    </div>
                {/if}
                {if !$image_pair@first}
                    <div class="cm-gallery-item cm-item-gallery">
                        {include file="common/image.tpl" no_ids=true images=$image_pair image_width=$props.post_image_width image_height=$props.post_image_height lazy_load=true}
                    </div>
                {/if}
            {/foreach}
        </div>
    </div>
{/if}