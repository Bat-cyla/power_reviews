<div title="{__("cp_add_attr")}" id="cp_add_attr">
    <form action="{""|fn_url}" method="post" name="add_attr_form" class="cp-pr_add-attr-form form-horizontal ">
        <input type="hidden" class="cm-no-hide-input" name="cp_attr_id" value="0" />
        <input type="hidden" class="cm-no-hide-input" name="attr_data[object_type]" value="G" />

        <div id="add_attribute">
            <div class="control-group">
                <label for="elm_cp_attr_name" class="control-label cm-required">{__("name")}:</label>
                <div class="controls">
                    <input type="text" name="attr_data[cp_attr_name]" id="elm_cp_attr_name" size="25" value="" class="input-short" />
                </div>
            </div>
            <div class="control-group">
                <label for="elm_cp_attr_position" class="control-label">{__("position")}:</label>
                <div class="controls">
                    <input type="text" name="attr_data[position]" id="elm_cp_attr_position" size="25" value="0" class="input-small" />
                </div>
            </div>
            <div class="control-group">
                <label for="elm_required_position" class="control-label">{__("cp_pr_is_mandatory")}:</label>
                <div class="controls">
                    <input type="hidden" name="attr_data[required]" value="N" />
                    <input type="checkbox" name="attr_data[required]" id="elm_required_position" value="Y" checked="checked" />
                </div>
            </div>
            
            {if "ULTIMATE"|fn_allowed_for}
                {include file="views/companies/components/company_field.tpl"
                    name="attr_data[company_id]"
                    id="elm_attr_data_0"
                }
            {/if}
            <div class="control-group">
                <label for="elm_cp_attr_type" class="control-label">{__("cp_attr_type")}:</label>
                <div class="controls cm-required">
                    <select name="attr_data[object_type]" id="elm_cp_attr_type">
                        <option value="G">{__("cp_global_prod_attr")}</option>
                        {if !$company_id || !"MULTIVENDOR"|fn_allowed_for}
                            <option value="E">{__("cp_testimonails_glob_attr")}</option>
                            {if !$company_id && "MULTIVENDOR"|fn_allowed_for}
                                <option value="M">{__("cp_global_vend_attr")}</option>
                            {/if}
                        {/if}
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label for="elm_cp_attr_view_tpe" class="control-label">{__("cp_pr_view_type")}:</label>
                <div class="controls">
                    <select name="attr_data[view_type]" id="elm_cp_attr_view_tpe">
                        <option value="E">{__("cp_pr_extremum_txt")}</option>
                        <option value="D">{__("cp_pr_default_txt")}</option>
                    </select>
                </div>
            </div>
            <div class="" id="cp_pr_extremum_fields">
                <div class="control-group">
                    <label for="elm_cp_attr_limits_low" class="control-label">{__("cp_pr_limits_min_txt")}:</label>
                    <div class="controls">
                        <input name="attr_data[view_type_txt][L]" id="elm_cp_attr_limits_low" type="text" value="" class="input-short" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="elm_cp_attr_limits_low" class="control-label">{__("cp_pr_below_mid")}:</label>
                    <div class="controls">
                        <input name="attr_data[view_type_txt][LM]" id="elm_cp_attr_limits_low" type="text" value="" class="input-short" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="elm_cp_attr_limits_mid" class="control-label">{__("cp_pr_limits_mid_txt")}:</label>
                    <div class="controls">
                        <input name="attr_data[view_type_txt][M]" type="text" value="" id="elm_cp_attr_limits_mid" class="input-short" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="elm_cp_attr_limits_mid" class="control-label">{__("cp_pr_above_mid")}:</label>
                    <div class="controls">
                        <input name="attr_data[view_type_txt][MT]" type="text" value="" id="elm_cp_attr_limits_mid" class="input-short" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="elm_cp_attr_limits_top" class="control-label">{__("cp_pr_limits_max_txt")}:</label>
                    <div class="controls">
                        <input name="attr_data[view_type_txt][T]" type="text" value="" id="elm_cp_attr_limits_top" class="input-short" />
                    </div>
                </div>
            </div>
            {include file="common/select_status.tpl" input_name="attr_data[status]" id="elm_attr_data_status" obj=$attr_data hidden=false}
            <div class="buttons-container">
                <div class="controls">
                    {include file="buttons/button.tpl" but_text=__("cp_add_attr") but_role="submit" but_name="dispatch[cp_pow_rev.attr_update]" but_target_form="add_attr_form"}
                </div>
            </div>
        </div>
    </form>
    <script language="javascript">
        (function(_,$){
            $(document).on("change", "#elm_cp_attr_view_tpe", function(){
                var sel_type = $(this).val();
                if (sel_type && sel_type == 'E') {
                    $('#cp_pr_extremum_fields').show();
                    $('#cp_pr_extremum_fields label').addClass('cm-required');
                } else {
                    $('#cp_pr_extremum_fields').hide();
                    $('#cp_pr_extremum_fields label').removeClass('cm-required');
                }
            });
        })(Tygh,Tygh.$);
    </script>
<!--cp_add_attr--></div>