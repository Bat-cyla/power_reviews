{if $m_post}
    {if $discussion.object_type && $discussion.object_type == "P"}
    
        {$msg_show_title=$addons.cp_power_reviews.allow_message_title}
        {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv}
        
    {elseif $discussion.object_type && $discussion.object_type == "E"}
    
        {$msg_show_title=$addons.cp_power_reviews.allow_message_title_test}
        {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_test}
        
    {elseif $discussion.object_type && $discussion.object_type == "M"}
    
        {$msg_show_title=$addons.cp_power_reviews.allow_message_title_vend}
        {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_vend}
        
    {elseif $discussion.object_type && $discussion.object_type == "C"}
        
        {$msg_show_title=$addons.cp_power_reviews.allow_message_title_cat}
        {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_cat}
        
    {elseif $discussion.object_type && $discussion.object_type == "A"}
        
        {$msg_show_title=$addons.cp_power_reviews.allow_message_title_page}
        {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_page}
        
    {elseif $discussion.object_type && $discussion.object_type == "ALL"}
        
        {$msg_show_title=$addons.cp_power_reviews.allow_message_title_all}
        {$msg_show_adv=$addons.cp_power_reviews.allow_adv_disv_all}
        
    {else}
        {$msg_show_title="N"}
        {$msg_show_adv="N"}
    {/if}
    {if $m_type == "mh"}
        {$post_kind="pos"}
    {else}
        {$post_kind="neg"}
    {/if}
    <div class="nd-mp__wrapper cp-disc-mosts__main-main {if $discussion.type == "R"}nd-mp__wrapper-collapse-height{/if} {$m_type} clearfix">
        <div class="nd-mp cp-disc-mosts__main {$m_type}">
            <div class="nd-mp__content cp-disc-mosts__inside-wrap">
                <div class="nd-mp__header {if $msg_show_title != "Y" || !$m_post.cp_pr_title} cp-pr__mp-need-border{/if}">
                    <div class="cp-disc-mosts__top clearfix">
                        {if $m_text}
                            <div class="cp-disc-mosts__top-title">
                                {if $m_type == "mh" || $m_type == "mc"}
                                    <span>
                                        {if $m_type == "mh"}
                                            <i class="cp-rev-icons-up-down cp_pr-ico-smile"></i>
                                        {elseif $m_type == "mc"}
                                            <i class="cp-rev-icons-up-down cp_pr-ico-frown"></i>
                                        {/if}
                                    </span>
                                    <span>{$m_text}</span>
                                {/if}
                            </div>
                        {/if}
                        {if $m_type == "mh1" || $m_type == "mh2"}
                            <div class=" nd-mp__name-useful cp-disc-mosts__name">{$m_post.name}</div>
                        {/if}
                        {if $discussion.object_type && $discussion.object_type == "P"}
                            {$mh_link="cp_pow_rev.view?thread_id=`$discussion.thread_id`&r_limit=`$m_pos_limit`&cp_post_kind=`$post_kind`"|fn_url}
                        {else}
                            {$mh_link="cp_pow_rev.all_reviews?thread_id=`$discussion.thread_id`&r_limit=`$m_pos_limit`&cp_post_kind=`$post_kind`"|fn_url}
                        {/if}
                        {if $discussion.all_positive_posts && $m_type == "mh" && $discussion.all_positive_posts > 1}
                            <div class="cp-disc-mosts__more-link">
                                <a rel="nofollow" target="_blank" href="{$mh_link}">{__("cp_pr_see_all_positive", ["[amount]" => $discussion.all_positive_posts])}</a>
                            </div>
                        {/if}
                        {if $discussion.all_critical_posts && $m_type == "mc" && $discussion.all_critical_posts > 1}
                            <div class="cp-disc-mosts__more-link">
                                <a rel="nofollow" target="_blank" href="{$mh_link}">{__("cp_pr_see_all_critical", ["[amount]" => $discussion.all_critical_posts])}</a>
                            </div>
                        {/if}
                    </div>
                    <div class="cp-disc-mosts__middle1 clearfix">
                        {if $m_type == "mh" || $m_type == "mc"}
                            <div class="nd-mp__name-posit-or-negat cp-disc-mosts__name">{$m_post.name}</div>
                        {/if}
                        {if $m_msg_show_date && $m_msg_show_date == "Y" && !$m_post.cp_is_fake}
                            <div class="cp-disc-mosts__date">{$m_post.timestamp|date_format:"`$settings.Appearance.date_format`"}</div>
                        {/if}
                        {if $addons.cp_power_reviews.show_purchase_label == "Y" && (($discussion.object_type && $discussion.object_type == "P") || ($m_post.object_type && $m_post.object_type == "P"))}
                            {if $m_post.cp_pr_verified_purchase == "Y"}
                                <div class="cp-pr__verif_purch">{__("cp_pr_verified_purchase")}</div>
                            {else}
                                <div class="cp-pr__not-verif_purch">{__("cp_pr_not_verified_purchase")}</div>
                            {/if}
                        {/if}
                        {if $discussion.type == "R" || $discussion.type == "B"}
                            <div class="cp-review-attr-av-string">
                                <div>
                                    {if $m_post.cp_av_rate_stars}
                                        {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$m_post.cp_av_rate_stars}
                                    {else}
                                        {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$m_post.rating_value|fn_cp_power_reviews_discussion_rating}
                                    {/if}
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            {if $discussion.type != "R"}
                <div class="cp-msg-need-height cp-disc-mosts__bot clearfix" id="cp_most_post_message_{$m_post.post_id}_most_{$m_type}">
                    {if $discussion.type == "C" || $discussion.type == "B"}
                        {if $msg_show_title && $msg_show_title == "Y" && $m_post.cp_pr_title}
                            <div class="cp-pr__msg_title">
                                <div class="cp-pr__msg_title_txt">{$m_post.cp_pr_title|escape|nl2br nofilter}</div>
                            </div>
                        {/if}
                        <div class="cp-disc-mosts__msg">
                            {if $m_post.cp_pr_advantages && $msg_show_adv && $msg_show_adv == "Y"}
                                <div class="cp-pr__post_msg_label">{__("cp_pr_advantages")}</div>
                                <div class="cp-pr__msg_advan">
                                    {if $m_post.short_adv}
                                        {include file="addons/cp_power_reviews/components/more_less.tpl" fb_msg=$m_post.short_adv 
                                            post_id="`$m_post.post_id`_adv_mp" sb_msg=$m_post.cp_pr_advantages 
                                        }
                                    {else}
                                        <div class="">{$m_post.cp_pr_advantages|escape|nl2br nofilter}</div>
                                    {/if}
                                </div>
                            {/if}
                            {if $m_post.cp_pr_disadvantages && $msg_show_adv && $msg_show_adv == "Y"}
                                <div class="cp-pr__post_msg_label">{__("cp_pr_disadvantages")}</div>
                                <div class="cp-pr__msg_advan">
                                    {if $m_post.short_disadv}
                                        {include file="addons/cp_power_reviews/components/more_less.tpl" fb_msg=$m_post.short_disadv 
                                            post_id="`$m_post.post_id`_disadv_mp" sb_msg=$m_post.cp_pr_disadvantages 
                                        }
                                    {else}
                                        <div class="">{$m_post.cp_pr_disadvantages|escape|nl2br nofilter}</div>
                                    {/if}
                                </div>
                            {/if}
                            <div class="cp-pr__post_msg_label">{__("cp_pr_comment_txt")}</div>
                            <div class="cp-pr__msg_advan">
                                {if $m_post.short_msg}
                                    {include file="addons/cp_power_reviews/components/more_less.tpl" fb_msg=$m_post.short_msg 
                                        post_id="`$m_post.post_id`_msg_mp" sb_msg=$m_post.message 
                                    }
                                {else}
                                    <div class="">{$m_post.message|escape|nl2br nofilter}</div>
                                {/if}
                            </div>
                        </div>
                    {/if}
                </div>
            {/if}
            </div>
            {if $m_show_up_down && $m_show_up_down != "not_display" && !$m_post.cp_is_fake}
                <div class="clearfix cp-disc-mosts__likes{if $discussion.type == "R" || $discussion.type == "B"} {/if} cp-two-blocks{if !$m_post.cp_attr_ratings} cp-no-attributes{/if} nd-likes__box">
                    <div class="cp-disc-mosts__likes-text">{__("cp_pr_does_it_helps")}</div>
                    <div class="cp-disc-mosts__likes-icons">
                        {if !$auth.user_id}
                            <span class="cp-rev-sign-link {if !$discussion.cp_show_signin}hidden{/if}">
                                <a href="" {if $settings.Security.secure_storefront != "partial"} data-ca-target-id="cp_login_block_{$object_id}" class="cm-dialog-opener cm-dialog-auto-size"{else} class=""{/if} rel="nofollow">{__("sign_in")}</a>
                            </span>
                        {/if}
                        {if $m_show_up_down == "only_up" || $m_show_up_down == "both"}
                            <span><a rel="nofollow" class="cm-ajax" data-ca-target-id="cp_posts_list_{$object_id},cp_prod_most_posts_{$object_id},cp_prod_most_help_posts_{$object_id}" 
                                href="{"discussion.cp_like_post&object_id={$object_id}&m_show_help_bl={$m_show_help_bl}&m_pos_limit={$m_pos_limit}&cp_type_for_most={$cp_type_for_most}&m_show_up_down={$m_show_up_down}&m_msg_show_date={$m_msg_show_date}&det_page={$cp_m_det_page}&object_type={$discussion.object_type}&cp_like=Y&post_id=`$m_post.post_id`&cp_sort_by=`$discussion.cp_sort_by`&selected_section=discussion&thread_id=`$discussion.thread_id`"|fn_url}"><i class="cp-rev-icons-up-down cp_pr-ico-like"></i></a><span class="cp-users-likes">{$m_post.cp_pos_post}</span></span>
                        {/if}
                        {if $m_show_up_down == "only_down" || $m_show_up_down == "both"}
                            <span><a rel="nofollow" class="cm-ajax" data-ca-target-id="cp_posts_list_{$object_id},cp_prod_most_posts_{$object_id},cp_prod_most_help_posts_{$object_id}" 
                                href="{"discussion.cp_like_post&object_id={$object_id}&m_show_help_bl={$m_show_help_bl}&m_pos_limit={$m_pos_limit}&cp_type_for_most={$cp_type_for_most}&m_show_up_down={$m_show_up_down}&m_msg_show_date={$m_msg_show_date}&det_page={$cp_m_det_page}&object_type={$discussion.object_type}&cp_like=N&post_id=`$m_post.post_id`&cp_sort_by=`$discussion.cp_sort_by`&selected_section=discussion&thread_id=`$discussion.thread_id`"|fn_url}"><i class="cp-rev-icons-up-down cp_pr-ico-dislike"></i></a><span class="cp-users-likes">{$m_post.cp_neg_post}</span></span>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/if}