{if ($addons.cp_power_reviews.allow_reply_rev == "Y" && $addons.cp_power_reviews.reply_moderation_vend == "NMTV") && $auth.user_type === "UserTypes::ADMIN"|enum}
    {include file="common/subheader.tpl" title=__("cp_power_reviews.vendor.moderation") target="#cp_power_reviews"}
    <div id="cp_power_reviews" class="collapsed in">
        <div class="control-group">
            <label class="control-label" for="cp_power_reviews_trust_vendor">{__("cp_power_reviews.vendor.trust_vendor")}:</label>
            <div class="controls">
                <input type="hidden" name="company_data[cp_trust_status]" value="N">
                <input type="checkbox" class="cm-checkbox" id="cp_power_reviews_trust_vendor" name="company_data[cp_trust_status]" value="Y" {if $company_data.cp_trust_status == "Y"}checked="checked"{/if}>
            </div>
        </div>
    </div>
{/if}
