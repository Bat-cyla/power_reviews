<div class="nd-recom" id="cp_object_recommend_{$object_id}">
    <p class="nd-recom__header cp-pr-recom__top-text">{__("cp_pr_recomend_purch")}</p>
    {if $discussion.cp_recom_proc}
        {$cp_recom_proc=$discussion.cp_recom_proc}
    {else}
        {$cp_recom_proc=0}
    {/if}
    {if $discussion.cp_recom_total}
        <p class="cp-pr-recom__proc-text">{$cp_recom_proc}%</p>
    {/if}
    <div class="nd-discuss-reviews">
        {if $discussion.cp_recom_total}
            {__("cp_pr_based_on", [$discussion.cp_recom_total])}
        {else}
            {__("cp_pr_nobody_recommended_yet")} {if $object_type == "P"}{__("cp_pr_this_product")}{elseif $object_type == "M"}{__("cp_pr_this_vendor")}{/if}
        {/if}
    </div>
    {if $discussion.cp_recom_total}
        <div class="cp-pr-recom__bar-main nd-likes__box">
            <i class="cp_pr-ico-like"></i>
            <div class="cp-pr-recom__bar-wrap">
                <div class="cp-progress-bar-rate" style="width:{$cp_recom_proc}%;"></div>
            </div>
            <div class="cp-pr-recom__based-numb">
                {if $discussion.cp_recom_positiv} {$discussion.cp_recom_positiv}{else} 0{/if}
            </div>
        </div>
    {/if}
<!--cp_object_recommend_{$object_id}--></div>