<script src="//www.youtube.com/iframe_api"></script>
<script language="javascript">
    (function(_,$){
        _.tr({
            "double_character_error": "{__("cp_double_character_error")|escape:"javascript"}"
        });
        $.extend(_, {
            addons_cp_sitemap: '{$addons.cp_sitemap.status}'
        });
        var players = [];
        $(document).on("click", ".cp-pr__video-thumb a", function(){
            var yt_id = $(this).attr('data-cp-uid');
            if (yt_id) {
                setTimeout(function() {
                    players.push(new YT.Player(yt_id));
                }, 600);
            }
        });
        $.ceEvent('on', 'ce.dialogclose', function(elm, e, u) {
            fn_pr_stop_vidosiki(players);
        });
        
        $(document).on("change", ".cp-select-type-attr", function(){
            var key = $(this).parent().closest('td').attr('id');
            if (key) {
                var new_key = key.split('p_teg_for_id_');
                if (new_key) {
                    if (new_key[1]) {
                        var cur_key = new_key[1];
                    } else {
                        var cur_key = '';
                    }
                }
            }
            var atr_type = $(this).val();
            if (atr_type && atr_type == 'G') {
                $('#attr_select_glob_'+cur_key).show();
                $('#selec_attr_glob_'+cur_key).attr('disabled', false);
                $('#attr_name_field_'+cur_key).hide();
                $('#cp_selec_view_type_main_' + cur_key).hide();
            } else if (atr_type && atr_type == 'P') {
                $('#attr_select_glob_'+cur_key).hide();
                $('#selec_attr_glob_'+cur_key).attr('disabled', true);
                $('#attr_name_field_'+cur_key).show();
                $('#cp_selec_view_type_main_' + cur_key).show();
            }
        });
        $(document).on("click", 'a[id^="cp_add_rew_attr"]', function(){
            var key = $('.cp-select-type-attr:last').attr('id');
            if (key) {
                var new_key = key.split('cp_selec_type_');
                if (new_key) {
                    if (new_key[1]) {
                        var cur_key = new_key[1];
                    } else {
                        var cur_key = '';
                    }
                }
            }
            if (cur_key) {
                var selected_type = $('#cp_selec_type_'+cur_key).val();
                if (selected_type && selected_type == 'G') {
                    $('#attr_select_glob_'+cur_key).show();
                    $('#selec_attr_glob_'+cur_key).attr('disabled', false);
                    $('#attr_name_field_'+cur_key).hide();
                } else if (selected_type && selected_type == 'P') {
                    $('#attr_select_glob_'+cur_key).hide();
                    $('#selec_attr_glob_'+cur_key).attr('disabled', true);
                    $('#attr_name_field_'+cur_key).show();
                }
            }
        });
        $(document).on("click", ".cp-change-img-stat", function(){
            var stat = $(this).attr('data-ca-status');
            var pair_id = $(this).attr('data-pair_id');
            var type = $(this).attr('data-cp-type');
            if (stat.length > 0 && stat == 'A') {
                $('#main_img_rev_span_d_' +pair_id + '_' + type).hide();
                $('#main_img_rev_span_a_' +pair_id + '_' + type).show();
                $('#main_img_rev_span_d_' +pair_id + '_' + type + '_btn').hide();
                $('#main_img_rev_span_a_' +pair_id + '_' + type + '_btn').show();
                $('#cp_reniew_status_img_' + pair_id + '_' + type + ' .cp-tools-img-rev .cm-statuses').removeClass('cp-pr__lab-main_d');
                $('#cp_reniew_status_img_' + pair_id + '_' + type + ' .cp-tools-img-rev .cm-statuses').addClass('cp-pr__lab-main_a');
            } else {
                $('#main_img_rev_span_a_' +pair_id + '_' + type).hide();
                $('#main_img_rev_span_d_' +pair_id + '_' + type).show();
                $('#main_img_rev_span_a_' +pair_id + '_' + type + '_btn').hide();
                $('#main_img_rev_span_d_' +pair_id + '_' + type + '_btn').show();
                $('#cp_reniew_status_img_' + pair_id + '_' + type + ' .cp-tools-img-rev .cm-statuses').removeClass('cp-pr__lab-main_a');
                $('#cp_reniew_status_img_' + pair_id + '_' + type + ' .cp-tools-img-rev .cm-statuses').addClass('cp-pr__lab-main_d');
            }
        });
        $(document).on("change", ".cp-select-view-type-attr", function(){
            var key = $(this).attr('id');
            if (key) {
                var new_key = key.split('cp_selec_view_type_');
                if (new_key) {
                    if (new_key[1]) {
                        var cur_key = new_key[1];
                    } else {
                        var cur_key = '';
                    }
                }
            }
            if (cur_key) {
                var cur_val = $(this).val();
                if (cur_val == 'E') {
                    $('#cp_selec_view_type_extrem_' + cur_key).show();
                } else {
                    $('#cp_selec_view_type_extrem_' + cur_key).hide();
                }
            }
        });
        $('[id*="addon_option_cp_power_reviews_rev_post_stars_bars_color_"]').keyup(function(e){
            if(!this.value) {
                return;
            } 
            this.value = this.value.replace(/[^#a-f0-9]/g, '');
            var piecesArray = this.value.split("#");
            if(piecesArray.length > 2) {
                fn_alert(_.tr('double_character_error'));
            }
        });
        
        if (Tygh.addons_cp_sitemap && Tygh.addons_cp_sitemap == "D" || Tygh.addons_cp_sitemap == null) {
            $('[id*="collapsable_addon_option_cp_power_reviews_sitemap_settings_"]').addClass('hidden');
            $('[data-target*="#collapsable_addon_option_cp_power_reviews_sitemap_settings_"]').addClass('hidden');

        }

    })(Tygh,Tygh.$);
    function fn_pr_stop_vidosiki(players) {
        if (players && players.length > 0) {
            $(players).each(function(i){
                if (typeof this.pauseVideo != 'undefined') {
                    this.pauseVideo();
                }
            });
        }
    }
</script>

