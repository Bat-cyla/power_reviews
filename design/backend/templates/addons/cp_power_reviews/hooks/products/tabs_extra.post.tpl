{if $cp_pr_recommend}
    <div class="hidden" id="content_cp_pr_recomends">
        <div class="table-responsive-wrapper">
            <table class="table table-middle table-responsive">
                <thead>
                    <tr>
                        <th>{__("date")}</th>
                        <th>{__("ip")}</th>
                        <th>{__("cp_pr_recommend_qu")}</th>
                        <th width="5%" class="mobile-hide center">{__("cp_pr_action_txt")}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$cp_pr_recommend item="pr_recom"}
                        <tr>
                            <td>{$pr_recom.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
                            <td>{$pr_recom.ip}</td>
                            <td>{if $pr_recom.type == "U"}{__("yes")}{else}{__("no")}{/if}</td>
                            <td width="5%" class="center" data-th="{__("cp_pr_action_txt")}">
                                {capture name="tools_items"}
                                {$current_redirect_url=$config.current_url|escape:url}
                                <li>{btn type="list" href="discussion.cp_delete_recom?thread_id=`$pr_recom.thread_id`&ip=`$pr_recom.ip_address`&redirect_url=`$current_redirect_url`&selected_section=cp_pr_recomends" class="cm-confirm" text={__("delete")} method="POST"}</li>
                                {/capture}
                                <div class="hidden-tools">
                                    {dropdown content=$smarty.capture.tools_items}
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    <!--content_cp_pr_recomends--></div>
{/if}