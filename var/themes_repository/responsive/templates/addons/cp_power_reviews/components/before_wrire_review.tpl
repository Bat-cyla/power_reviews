<div class="nd-discus-bottom">
    <div class="cp-pr__pre-btn-t">
        {if $object_type == "P"}
            {__("cp_pr_use_this_product")}
        {elseif $object_type == "M"}
            {__("cp_pr_use_this_vendor")}
        {elseif $object_type == "E"}
            {__("cp_pr_use_this_test")}
        {elseif $object_type == "B"}
            {__("cp_pr_use_this_blog")}
        {else}
            {__("cp_pr_use_this_other")}
        {/if}
    </div>
    <div class="cp-pr__pre-btn-b">
        {if $object_type == "P"}
            {__("cp_pr_before_review_btn")}
        {elseif $object_type == "M"}
            {__("cp_pr_before_review_btn_vend")}
        {elseif $object_type == "E"}
            {__("cp_pr_before_review_btn_test")}
        {elseif $object_type == "B"}
            {__("cp_pr_before_review_btn_blog")}
        {else}
            {__("cp_pr_before_review_btn_other")}
        {/if}
    </div>
    {include
    file="addons/discussion/views/discussion/components/new_post_button.tpl"
    name=__("write_review")
    obj_id=$object_id
    object_type=$discussion.object_type
    locate_to_review_tab=true}
</div>