{*
image_key - required
image_name - required
image_object_type - required

image_object_id - optional
image_type - optional
*}

{if !"SMARTY_ATTACH_IMAGES_LOADED"|defined}
{assign var="tmp" value="SMARTY_ATTACH_IMAGES_LOADED"|define:true}
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.delete_image', function(r, p) {
        if (r.deleted == true) {
			var def_imag = "{$cp_image_icon}";
            $('#' + p.result_ids).closest('a').replaceWith('<div class="cp-no-image"><img src="'+def_imag+'" width="150px" border="0" alt="" /></div>');
            $('a[data-ca-target-id=' + p.result_ids + ']').hide();
        }
    });

    $.ceEvent('on', 'ce.delete_image_pair', function(r, p) {
        if (r.deleted == true) {
            $('#' + p.result_ids).remove();
        }        
    });    
}(Tygh, Tygh.$));    
</script>
{/if}

{if !$original_image}
    {assign var="thumbnail_width" value="85"}
{/if}
{assign var="_plug" value="."|explode:""}
{assign var="key" value=$image_key|default:"0"}
{assign var="object_id" value=$image_object_id|default:"0"}
{assign var="name" value=$image_name|default:""}
{assign var="object_type" value=$image_object_type|default:""}
{assign var="type" value=$image_type|default:"M"}
{assign var="pair" value=$image_pair|default:$_plug}
{assign var="suffix" value=$image_suffix|default:""}

<input type="hidden" name="{$name}_image_data{$suffix}[{$key}][pair_id]" value="{$pair.pair_id}" class="cm-image-field" />
<input type="hidden" name="{$name}_image_data{$suffix}[{$key}][type]" value="{$type|default:"M"}" class="cm-image-field" />
<input type="hidden" name="{$name}_image_data{$suffix}[{$key}][object_id]" value="{$object_id}" class="cm-image-field" />

<div id="box_attach_images_{$name}_{$key}" class="cp-attach-images">
   
    {if !$hide_titles}
        {if !hide_desc}
        <div class="desc">
            <span>{$detailed_title|default:__("popup_larger_image")}
            {if $detailed_text}{$detailed_text}{/if}
            :</span>
        </div>
        {/if}
    {/if}

    <div class="cp-upload-box clearfix {if $no_thumbnail && !$pair.icon}hidden{/if}" id="load_thumbnail_{$name}{$suffix}{$key}">
    {if $delete_pair && $pair.pair_id}
        <div class="float-right">
            <a data-ca-target-id="box_attach_images_{$name}_{$key}" href="{"cp_power_reviews.delete_image_pair?pair_id=`$pair.pair_id`&object_type=`$object_type`"|fn_url}" class="cm-confirm cm-post cm-ajax cm-tooltip pull-right" data-ca-event="ce.delete_image_pair" title="{__("delete_image_pair")}"><i class="ty-icon-cancel-circle"></i></a>
        </div>
    {/if}
        {if !$hide_titles}
            <h5>
                <span>{$icon_title|default:__("thumbnail")}</span>
                {if $icon_text}<span class="small-note">{$icon_text}</span>{/if}
            </h5>
        {/if}
        
        <div class="cp-pull-left cp-image-wrap">
        
            {if $pair.image_id}
				{if !("MULTIVENDOR"|fn_allowed_for && $runtime.company_id && $object_type == "category")}
					<a data-ca-target-id="image_{$pair.image_id}" href="{"cp_power_reviews.delete_image?pair_id=`$pair.pair_id`&image_id=`$pair.image_id`&object_type=`$object_type`"|fn_url}" class="image-delete cm-confirm cm-post cm-ajax delete cm-delete-image-link cm-tooltip" data-ca-event="ce.delete_image" title="{__("remove")}">
						<i class="ty-icon-cancel-circle"></i>
					</a>
				{/if}
            {/if}
            
            {if !$hide_images}
                <div class="cp-image">
                    {if $pair.image_id}
                        {include file="common/image.tpl" image=$pair.icon image_id=$pair.image_id image_width=150}
                    {else}
                        <div class="cp-no-image">
							{if $cp_image_icon}
								<img src="{$cp_image_icon}" width="150px" border="0" alt="" />
							{/if}
						</div>
                    {/if}
                </div>
            {/if}
            {if !$hide_alt}
                <div class="image-alt clear">
                    <div class="cp-input-prepend">
                    <span class="cp-add-on cm-tooltip cm-hide-with-inputs" title="{__("alt_text")}"><i class="ty-icon-chat"></i></span>
                    {*<label class="option_variant_alt_text">{__("alt_text")}:</label><br />*}
                    <input type="text" id="alt_icon_{$name}_{$key}" name="{$name}_image_data{$suffix}[{$key}][image_alt]" value="{$pair.icon.alt}" />
                    </div>
                </div>
            {/if}
        </div>
        <div class="cp-image-upload cm-hide-with-inputs">
            {include file="common/fileuploader.tpl" var_name="`$name`_image_icon`$suffix`[`$key`]" is_image=true }
            
        </div>
    </div>
    
    {if !$no_detailed}
    
		{if $detailed_title}
			<h5><span>{$detailed_title}</span></h5>
		{/if}
		<div class="cp-upload-box clearfix">
			<div class="cp-pull-left cp-image-wrap">
				{if !$hide_images}
					{if $pair.detailed_id}
						{if !("MULTIVENDOR"|fn_allowed_for && $runtime.company_id && $object_type == "category")}
							<a data-ca-target-id="image_{$pair.detailed_id}" href="{"cp_power_reviews.delete_image?pair_id=`$pair.pair_id`&image_id=`$pair.detailed_id`&object_type=detailed"|fn_url}" class="image-delete cm-confirm cm-post cm-tooltip cm-ajax delete cm-delete-image-link" data-ca-event="ce.delete_image" title="{__("remove")}"><i class="ty-icon-cancel-circle"></i></a>
						{/if}
					{/if}
					<div class="cp-image">
						{if $pair.detailed_id}
							{include file="common/image.tpl" image=$pair.detailed image_id=$pair.detailed_id image_width=150}
						{else}
							<div class="cp-no-image">
								{if $cp_image_icon}
									<img src="{$cp_image_icon}" width="150px" border="0" alt="" />
								{/if}
							</div>
						{/if}
					</div>
				{/if}
				
				{if !$hide_alt}
					<div class="image-alt">
						<div class="cp-input-prepend">
							{*<label for="alt_det_{$name}_{$key}">{__("alt_text")}:</label>*}
							<span class="cp-add-on cm-tooltip cm-hide-with-inputs" title="{__("alt_text")}"><i class="ty-icon-chat"></i></span>
							<input type="text" id="alt_det_{$name}_{$key}" name="{$name}_image_data{$suffix}[{$key}][detailed_alt]" value="{$pair.detailed.alt}" />
						</div>
					</div>
				{/if}
			</div>

			<div class="cp-image-upload cm-hide-with-inputs">
				{include file="common/fileuploader.tpl" var_name="`$name`_image_detailed`$suffix`[`$key`]" }
			</div>
		</div>
    {/if}
</div>
