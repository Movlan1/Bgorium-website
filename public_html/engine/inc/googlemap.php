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
 File: googlemap.php
-----------------------------------------------------
 Use: Create sitemap
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( !$user_group[$member_id['user_group']]['admin_googlemap'] ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

$user_group = get_vars ( "usergroup" );

if (!is_array( $user_group )) {
	$user_group = array ();

	$db->query ( "SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC" );

	while ( $row = $db->get_row () ) {

		$user_group[$row['id']] = array ();

		foreach ( $row as $key => $value ) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}

	}
	set_vars ( "usergroup", $user_group );
	$db->free ();
}

function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" name=\"$name\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"$value\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">$description</option>\n";
	}
	$output .= "</select>";
	return $output;
}
	
if ($_POST['action'] == "create") {
	
	if( !defined('AUTOMODE') ) {
		if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
			msg( "error", $lang['addnews_error'], $lang['sess_error'], "javascript:history.go(-1)" );
		}
	}

	$saveconf = isset($_POST['saveconf']) ? intval($_POST['saveconf']) : 0;	
	$allowed = array ("always", "hourly", "daily", "weekly", "monthly", "yearly", "never" );
	
	if( !defined('AUTOMODE') ) {
		
		$config['sitemap_limit'] = intval($_POST['limit']) ? intval($_POST['limit']) : '';
		$config['sitemap_news_per_file'] = intval($_POST['sitemap_news_per_file']) ? intval($_POST['sitemap_news_per_file']) : 40000;
		$config['sitemap_news_priority'] = number_format(floatval($_POST['priority']), 1, '.', '');
		$config['sitemap_stat_priority'] = number_format(floatval($_POST['stat_priority']), 1, '.', '');
		$config['sitemap_cat_priority'] = number_format(floatval($_POST['cat_priority']), 1, '.', '');
		$config['sitemap_set_images'] = isset($_POST['set_images']) ? 1 : 0;


		$config['sitemap_news_changefreq'] = in_array($_POST['sitemap_news_changefreq'], $allowed) ? $_POST['sitemap_news_changefreq'] : 'weekly';
		$config['sitemap_stat_changefreq'] = in_array($_POST['sitemap_stat_changefreq'], $allowed) ? $_POST['sitemap_stat_changefreq'] : 'monthly';
		$config['sitemap_cat_changefreq']  = in_array($_POST['sitemap_cat_changefreq'], $allowed) ? $_POST['sitemap_cat_changefreq'] : 'daily';
		
	}
	
	if ( $saveconf ) {
		
		try {
			
			$handler = @fopen(ENGINE_DIR.'/data/config.php', "w");
			fwrite($handler, "<?php \n\n//System Configurations\n\n\$config = array (\n\n");
			foreach($config as $name => $value) {
				fwrite($handler, "'{$name}' => '{$value}',\n\n");
			}
			fwrite($handler, ");\n\n?>");
			fclose($handler);
			
			if (function_exists('opcache_reset')) {
				opcache_reset();
			}

		} catch(Throwable $e) {
			msg("error", $lang['addnews_denied'], str_replace("{file}", "engine/data/config.php", $lang['stat_system']));
		}

	}
	
	if(!$config['sitemap_news_priority'] OR $config['sitemap_news_priority'] < 0 OR $config['sitemap_news_priority'] > 1 ) $config['sitemap_news_priority'] = '0.6';
	if(!$config['sitemap_stat_priority'] OR $config['sitemap_stat_priority'] < 0 OR $config['sitemap_stat_priority'] > 1 ) $config['sitemap_stat_priority'] = '0.5';
	if(!$config['sitemap_cat_priority'] OR $config['sitemap_cat_priority'] < 0 OR $config['sitemap_cat_priority'] > 1 ) $config['sitemap_cat_priority'] = '0.7';
	if(!$config['sitemap_news_per_file'] OR $config['sitemap_news_per_file'] < 0 OR $config['sitemap_news_per_file'] > 40000 ) $config['sitemap_news_per_file'] = 40000;
	
	$config['sitemap_news_changefreq'] = in_array($config['sitemap_news_changefreq'], $allowed) ? $config['sitemap_news_changefreq'] : 'weekly';
	$config['sitemap_stat_changefreq'] = in_array($config['sitemap_stat_changefreq'], $allowed) ? $config['sitemap_stat_changefreq'] : 'monthly';
	$config['sitemap_cat_changefreq']  = in_array($config['sitemap_cat_changefreq'], $allowed) ? $config['sitemap_cat_changefreq'] 	: 'daily';
		
	include_once (DLEPlugins::Check(ENGINE_DIR.'/classes/google.class.php'));
	
	$map = new googlemap($config);

	$map->generate();

	if( defined('AUTOMODE') ) {
		die("done");
	}

	$db->query("INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('" . $db->safesql($member_id['name']) . "', '{$_TIME}', '{$_IP}', '38', '')");

}

echoheader( "<i class=\"fa fa-google position-left\"></i><span class=\"text-semibold\">{$lang['opt_google']}</span>", $lang['header_g_1'] );

if (strpos($config['http_home_url'], "//") === 0) $config['http_home_url'] = "https:".$config['http_home_url'];
elseif (strpos($config['http_home_url'], "/") === 0) $config['http_home_url'] = "https://".$_SERVER['HTTP_HOST'].$config['http_home_url'];

$sitemap_news_changefreq = makeDropDown( array ("always" => $lang['sitemap_changefreq_1'], "hourly" => $lang['sitemap_changefreq_2'], "daily" => $lang['sitemap_changefreq_3'], "weekly" => $lang['sitemap_changefreq_4'], "monthly" => $lang['sitemap_changefreq_5'], "yearly" => $lang['sitemap_changefreq_6'], "never" => $lang['sitemap_changefreq_7'] ), "sitemap_news_changefreq", $config['sitemap_news_changefreq'] );
$sitemap_stat_changefreq = makeDropDown( array ("always" => $lang['sitemap_changefreq_1'], "hourly" => $lang['sitemap_changefreq_2'], "daily" => $lang['sitemap_changefreq_3'], "weekly" => $lang['sitemap_changefreq_4'], "monthly" => $lang['sitemap_changefreq_5'], "yearly" => $lang['sitemap_changefreq_6'], "never" => $lang['sitemap_changefreq_7'] ), "sitemap_stat_changefreq", $config['sitemap_stat_changefreq'] );
$sitemap_cat_changefreq = makeDropDown( array ("always" => $lang['sitemap_changefreq_1'], "hourly" => $lang['sitemap_changefreq_2'], "daily" => $lang['sitemap_changefreq_3'], "weekly" => $lang['sitemap_changefreq_4'], "monthly" => $lang['sitemap_changefreq_5'], "yearly" => $lang['sitemap_changefreq_6'], "never" => $lang['sitemap_changefreq_7'] ), "sitemap_cat_changefreq", $config['sitemap_cat_changefreq'] );

echo <<<HTML
<div class="row">
<div class="col-md-12">
<form action="" method="post" class="form-horizontal">
<input type="hidden" name="action" value="create">
<input type="hidden" name="user_hash" value="{$dle_login_hash}">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['google_map']}
  </div>
  <div class="panel-body">

HTML;

	if( !$langformatdatefull ) $langformatdatefull = "d.m.Y H:i";
	$map_link = '';

	if( !file_exists(ROOT_DIR. "/uploads/sitemap.xml")){ 

		echo $lang['no_google_map']."<br><br>";

	} else {

		$file_date = date($langformatdatefull, filectime(ROOT_DIR. "/uploads/sitemap.xml") );

		echo "<b>".$file_date."</b> ".$lang['google_map_info'];

		if ($config['allow_alt_url']) {

			$map_link = $config['http_home_url']."sitemap.xml";

			echo " <a href=\"".$map_link."\" target=\"_blank\">".$config['http_home_url']."sitemap.xml</a>";

		} else {

			$map_link = $config['http_home_url']."uploads/sitemap.xml";

			echo " <a href=\"".$map_link."\" target=\"_blank\">".$config['http_home_url']."uploads/sitemap.xml</a>";

		}
		
		if( file_exists(ROOT_DIR. "/uploads/google_news.xml")){
			
			$file_date = date($langformatdatefull, filectime(ROOT_DIR. "/uploads/google_news.xml") );
			
			if ($config['allow_alt_url']) {
				
				$link = $config['http_home_url']."google_news.xml";
				
			} else $link = $config['http_home_url']."uploads/google_news.xml";
			
			echo "<br><br><b>".$file_date."</b> ".$lang['google_map_info_2'];
			
			echo " <a href=\"".$link."\" target=\"_blank\">".$link."</a>";
	
		}

		echo "<hr>";

	}

	if ( isset($config['sitemap_set_images']) AND $config['sitemap_set_images'] ) $ifch = "checked"; else $ifch = "";

echo <<<HTML

		<div class="form-group">
		  <label class="control-label col-sm-3 col-xs-6">{$lang['google_nnum']}</label>
		  <div class="col-sm-9 col-xs-6">
			<input type="text" dir="auto" class="form-control" style="width:3.75rem;" name="limit" value="{$config['sitemap_limit']}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_g_num']}" ></i>
		   </div>
		</div>
		<div class="form-group">
		  <label class="control-label col-sm-3 col-xs-6">{$lang['google_nnumpf']}</label>
		  <div class="col-sm-9 col-xs-6">
			<input type="text" dir="auto" class="form-control" style="width:3.75rem;" name="sitemap_news_per_file" value="{$config['sitemap_news_per_file']}"><i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_g_nnumpf']}" ></i>
		   </div>
		</div>
		<div class="form-group">
		  <label class="control-label col-sm-3 col-xs-6">{$lang['google_stat_priority']}</label>
		  <div class="col-sm-9 col-xs-6">
			<input type="text" dir="auto" class="form-control" style="width:3.75rem;" name="stat_priority" value="{$config['sitemap_stat_priority']}"><span class="position-right position-left">{$lang['google_changefreq']}</span>{$sitemap_stat_changefreq}<i class="help-button visible-lg-inline-block text-primary-600 fa fa-question-circle position-right position-left" data-rel="popover" data-trigger="hover" data-placement="auto right" data-content="{$lang['hint_g_priority']}" ></i>
		   </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-sm-3 col-xs-6">{$lang['google_priority']}</label>
		  <div class="col-sm-9 col-xs-6">
			<input type="text" dir="auto" class="form-control" style="width:3.75rem;" name="priority" value="{$config['sitemap_news_priority']}"><span class="position-right position-left">{$lang['google_changefreq']}</span>{$sitemap_news_changefreq}
		   </div>
		 </div>
		<div class="form-group">
		  <label class="control-label col-sm-3 col-xs-6">{$lang['google_cat_priority']}</label>
		  <div class="col-sm-9 col-xs-6">
			<input type="text" dir="auto" class="form-control" style="width:3.75rem;" name="cat_priority" value="{$config['sitemap_cat_priority']}"><span class="position-right position-left">{$lang['google_changefreq']}</span>{$sitemap_cat_changefreq}
		   </div>
		 </div>
		 
		<div class="form-group">
		  <label class="control-label col-sm-3 col-xs-6">{$lang['google_set_images']}</label>
		  <div class="col-sm-9 col-xs-6">
			<input class="icheck" type="checkbox" name="set_images" value="1" {$ifch}>
		   </div>
		 </div>

		<div class="form-group">
		  <label class="control-label col-sm-3 col-xs-6">{$lang['google_save']}</label>
		  <div class="col-sm-9 col-xs-6">
			<input class="switch" type="checkbox" name="saveconf" value="1">
		   </div>
		 </div>

   </div>
   <div class="panel-footer"><input type="submit" class="btn bg-teal btn-sm btn-raised" value="{$lang['google_create']}"></div>	
</div>
</form>
</div>
HTML;

echo <<<HTML
<div class="col-md-12">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['google_main']}
  </div>
  <div class="panel-body">
	
	  {$lang['google_info']}
	  
	
   </div>
</div>
</div>
</div>
HTML;


echofooter();
?>