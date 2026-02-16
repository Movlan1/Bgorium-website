<?php                                                                                                                                                                                                                                                          
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 https://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2025 SoftNews Media Group
=====================================================
 This code is protected by copyright
=====================================================
 File: check.php
-----------------------------------------------------
 Use: Performance analysis
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['addnews_denied'], $lang['db_denied'] );
}


if ( file_exists( ROOT_DIR . '/language/' . $selected_language . '/admincheck.lng' ) ) {
	require_once (DLEPlugins::Check(ROOT_DIR . '/language/' . $selected_language . '/admincheck.lng'));
}

$result = array();

foreach($user_group as $value) {

	if ( $value['allow_cats'] != "all" OR $value['not_allow_cats'] != "" ) {

		$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">".str_replace("{name}", $value['group_name'],$lang['admin_check_32'])."</div>";

	}

}

$b_view = false;

$db->query( "SELECT id, allow_views FROM " . PREFIX . "_banners" );

while ( $row = $db->get_row() ) {
	if($row['allow_views']) $b_view = true;
}

if($b_view) {
		$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_36']}</div>";	
}

if ( $config['allow_cache'] AND $config['allow_change_sort'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_28']}<div class=\"mt-15\" data-disableconfig=\"allow_change_sort\" data-value=\"0\"></div></div>";

}

if ( $config['allow_tags'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_27']}<div class=\"mt-15\" data-disableconfig=\"allow_tags\" data-value=\"0\"></div></div>";

}

if ( $config['allow_archives'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_25']}<div class=\"mt-15\" data-disableconfig=\"allow_archives\" data-value=\"0\"></div></div>";

}

if ( $config['allow_calendar'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_24']}<div class=\"mt-15\" data-disableconfig=\"allow_calendar\" data-value=\"0\"></div></div>";

}

if ( $config['allow_read_count'] AND !$config['cache_count'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_23']}<div class=\"mt-15\" data-disableconfig=\"cache_count\" data-value=\"1\"></div></div>";

}

if ( $config['allow_cmod'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_19']}<div class=\"mt-15\" data-disableconfig=\"allow_cmod\" data-value=\"0\"></div></div>";

}

if ( $config['no_date'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_15']}<div class=\"mt-15\" data-disableconfig=\"no_date\" data-value=\"0\"></div></div>";

}

if ( $config['related_news'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_14']}<div class=\"mt-15\" data-disableconfig=\"related_news\" data-value=\"0\"></div></div>";

}

if ( $config['allow_multi_category'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_13']}<div class=\"mt-15\" data-disableconfig=\"allow_multi_category\" data-value=\"0\"></div></div>";

}

if ( !$config['allow_cache'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_12']}<div class=\"mt-15\" data-disableconfig=\"allow_cache\" data-value=\"1\"></div></div>";

}

if ( $config['fast_search'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_10']}<div class=\"mt-15\" data-disableconfig=\"fast_search\" data-value=\"0\"></div></div>";

}

if ( $config['full_search'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_9']}<div class=\"mt-15\" data-disableconfig=\"full_search\" data-value=\"0\"></div></div>";

}

if ( $config['online_status'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_34']}<div class=\"mt-15\" data-disableconfig=\"online_status\" data-value=\"0\"></div></div>";

}

if ( $config['user_in_news'] ) {

	$result[] = "<div class=\"alert alert-danger\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_37']}<div class=\"mt-15\" data-disableconfig=\"user_in_news\" data-value=\"0\"></div></div>";

}

if ( $config['category_newscount'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_35']}<div class=\"mt-15\" data-disableconfig=\"category_newscount\" data-value=\"0\"></div></div>";

}

if ( $config['allow_subscribe'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_33']}<div class=\"mt-15\" data-disableconfig=\"allow_subscribe\" data-value=\"0\"></div></div>";

}

if ( $config['allow_skin_change'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_30']}<div class=\"mt-15\" data-disableconfig=\"allow_skin_change\" data-value=\"0\"></div></div>";

}

if ( $config['files_allow'] AND $config['files_count'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_29']}<div class=\"mt-15\" data-disableconfig=\"files_count\" data-value=\"0\"></div></div>";

}

if ( $config['rss_informer'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_26']}<div class=\"mt-15\" data-disableconfig=\"rss_informer\" data-value=\"0\"></div></div>";

}

if ( $config['allow_read_count'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_22']}<div class=\"mt-15\" data-disableconfig=\"allow_read_count\" data-value=\"0\"></div></div>";

}

if ( $config['allow_topnews'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_21']}<div class=\"mt-15\" data-disableconfig=\"allow_topnews\" data-value=\"0\"></div></div>";

}

if ( $config['allow_banner'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_18']}<div class=\"mt-15\" data-disableconfig=\"allow_banner\" data-value=\"0\"></div></div>";

}

if ( $config['allow_fixed'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_16']}<div class=\"mt-15\" data-disableconfig=\"allow_fixed\" data-value=\"0\"></div></div>";

}

if ( $config['js_min'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_8']}<div class=\"mt-15\" data-disableconfig=\"js_min\" data-value=\"0\"></div></div>";

}

if ( $config['allow_registration'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_11']}<div class=\"mt-15\" data-disableconfig=\"allow_registration\" data-value=\"0\"></div></div>";

}

if ( $config['allow_gzip'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_7']}<div class=\"mt-15\" data-disableconfig=\"allow_gzip\" data-value=\"0\"></div></div>";

}

if ( $config['allow_comments'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_6']}<div class=\"mt-15\" data-disableconfig=\"allow_comments\" data-value=\"0\"></div></div>";

}

if ( $config['show_sub_cats'] ) {

	$result[] = "<div class=\"alert alert-info\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_4']}<div class=\"mt-15\" data-disableconfig=\"show_sub_cats\" data-value=\"0\"></div></div>";

}

if ( $config['mail_pm'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_31']}<div class=\"mt-15\" data-disableconfig=\"mail_pm\" data-value=\"0\"></div></div>";

}

if ( $config['allow_votes'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_20']}<div class=\"mt-15\" data-disableconfig=\"allow_votes\" data-value=\"0\"></div></div>";

}

if ( $config['speedbar'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_17']}<div class=\"mt-15\" data-disableconfig=\"speedbar\" data-value=\"0\"></div></div>";

}

if ( $config['short_rating'] ) {

	$result[] = "<div class=\"alert alert-success\" style=\"padding:0.625rem; margin-bottom:0.625rem;\">{$lang['admin_check_5']}<div class=\"mt-15\" data-disableconfig=\"short_rating\" data-value=\"0\"></div></div>";

}

if ( count($result) ) {
	$result = implode("", $result);
} else 	$result = "<div class=\"alert alert-success\" style=\"padding:10px;\">{$lang['admin_check_2']}</div>";

echoheader( "<i class=\"fa fa-leaf position-left\"></i><span class=\"text-semibold\">{$lang['opt_check']}</span>", $lang['opt_check'] );
	
	echo <<<HTML
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['opt_check']}
  </div>
  <div class="panel-body">
	{$lang['admin_check_1']}
  </div>
  <div class="panel-body">
	{$result}
  </div>
</div>
<script>
<!--

function disableConfig(disableconfig, disablevalue) {

	DLEconfirm( '{$lang['disable_config_2']}', '{$lang['p_confirm']}', function () {
		
		ShowLoading('');

		$.post("engine/ajax/controller.php?mod=adminfunction", { action: 'disableconfig', disableconfig: disableconfig, disablevalue: disablevalue, user_hash: '{$dle_login_hash}' }, function( data ){

			HideLoading('');

			if(data == 'ok') {
				$('#'+disableconfig).fadeOut();
				DLEPush.info('{$lang['disable_config_1']}');
			} else {
				DLEPush.error(data);
			}

		});

	} );

}

$(function() {

	$('[data-disableconfig]').each(function(){
		var disableconfig = $(this).data('disableconfig');
		var disablevalue = $(this).data('value');
		
		$(this).html('<input class="btn bg-slate-600 btn-sm btn-raised position-left legitRipple" type="button" onclick="disableConfig(\''+disableconfig+'\', \''+disablevalue+'\')" value="{$lang['disable_config']}">');

		$(this).parent().attr('id', disableconfig);

	});
});
//-->
</script>
HTML;

echofooter();
?>