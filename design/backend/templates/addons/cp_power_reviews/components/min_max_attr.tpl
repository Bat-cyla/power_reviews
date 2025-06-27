
<fieldset class="cp-pr__line-rating" id="{$rate_id}">
    <span class="cp-pr__attr-bar2">&nbsp;</span>
    {foreach from =""|fn_get_discussion_ratings item="title" key="val"}
        {$item_rate_id = "`$rate_id`_`$val`"}
        <input type="radio" id="{$item_rate_id}" name="{$rate_name}" class="cp-pr__check_{$val}" value="{$val}" {if $rate_value == $val}checked="checked"{/if} {if $disabled}disabled="disabled"{/if}/>
            <div class="{if $val != 1}cp-pr__line-rate__main{else}cp-pr__line-rate__main-first{/if} cp-pr__check_{$val}">
                <label for="{$item_rate_id}" >&nbsp;</label>
                {*
                <span class="cp-pr__attr-bar">&nbsp;</span>
                *}
                {if $val != 1}
                    <span class="cp-pr__line-txt{if in_array($val, array(2,3,4))} cp-pr__is-middle{/if}">
                        {if $val == 1}{$cp_attr.view_type_txt.L.name}
                        {elseif $val == 2}{$cp_attr.view_type_txt.LM.name}
                        {elseif $val == 3}{$cp_attr.view_type_txt.M.name}
                        {elseif $val == 4}{$cp_attr.view_type_txt.MT.name}
                        {elseif $val == 5}{$cp_attr.view_type_txt.T.name}{/if}
                    </span>
                {/if}
            </div>
            {if $val == 1}
                <span class="cp-pr__line-txt cp-pr__is-first">{$cp_attr.view_type_txt.L.name}</span>
            {/if}
    {/foreach}
</fieldset>

{*
<fieldset class="cp-pr__line-rating2" id="{$rate_id}">
    <span class="cp-pr__attr-bar">&nbsp;</span>
    {foreach from =""|fn_cp_pr_get_discussion_ratings_revers item="title" key="val"}
        {$item_rate_id = "`$rate_id`_`$val`"}
        <input type="radio" class="{if in_array($val, array(1,3,5))}cp-pr__big-radio cm-tooltip{/if}" {if $val == 1}title="{$cp_attr.view_type_txt.L.name}"{elseif $val == 3}title="{$cp_attr.view_type_txt.M.name}"{elseif $val == 5}title="{$cp_attr.view_type_txt.T.name}"{/if} id="{$item_rate_id}" name="{$rate_name}" value="{$val}" 
            {if $rate_value == $val}checked="checked"{/if} {if $disabled}disabled="disabled"{/if}
        />
    {/foreach}
</fieldset>
*}