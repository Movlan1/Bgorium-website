<?PHP
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
 File: mass_static_action.php
-----------------------------------------------------
 Use: mass action static pages
=====================================================
*/
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( ! $user_group[$member_id['user_group']]['admin_static'] ) {
	msg( "error", $lang['mass_error'], $lang['mass_ddenied'], $_SESSION['static_referrer'] );
}

if( ! isset($_SESSION['static_referrer']) ) {
	
	$_SESSION['static_referrer'] = "?mod=static&amp;action=list";

}

$selected_news = isset($_REQUEST['selected_news']) ? $_REQUEST['selected_news'] : array();

if( ! $selected_news ) {
	msg( "error", $lang['mass_error'], $lang['mass_denied'], $_SESSION['static_referrer'] );
}

if( !isset($_REQUEST['user_hash']) OR !$_REQUEST['user_hash'] OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	
	die( "Hacking attempt! User not found" );

}

$action = htmlspecialchars( strip_tags( stripslashes( $_POST['action'] ) ) );

$k_mass = false;
$field = false;

if( $action == "mass_date" ) {
	$field = "date";
	$value = time();
	$k_mass = true;
	$title = $lang['mass_static_edit_date_tl'];
	$lang['mass_confirm'] = $lang['mass_static_edit_date_fr1'];
	$lang['mass_confirm_1'] = $lang['mass_static_confirm_2'];
} elseif( $action == "mass_clear_count" ) {
	$field = "views";
	$value = 0;
	$k_mass = true;
	$title = $lang['mass_clear_count_2'];
	$lang['mass_confirm'] = $lang['mass_clear_count_1'];
	$lang['mass_confirm_1'] = $lang['mass_static_confirm_2'];
}

if( $_POST['doaction'] == "mass_update" AND $field ) {
	foreach ( $selected_news as $id ) {
		$id = intval( $id );
		$db->query( "UPDATE " . PREFIX . "_static SET {$field}='{$value}' WHERE id='{$id}'" );
	}
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '39', '')" );
	msg( "success", $lang['db_ok'], $lang['db_ok_1'], $_SESSION['static_referrer'] );
}

if( $k_mass ) {
	
	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $title );
	
	echo <<<HTML
<form method="post">
<div class="panel panel-default">
  <div class="panel-heading">
    {$title}
  </div>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center">{$lang['mass_confirm']}
HTML;
	
	echo " (<b>" . count( $selected_news ) . "</b>) $lang[mass_confirm_1]<br><br>
<input class=\"btn bg-teal btn-sm btn-raised position-left\" type=submit value=\"{$lang['mass_yes']}\" style=\"min-width:100px;\"><input type=button class=\"btn bg-danger btn-sm btn-raised position-left\" value=\"{$lang['mass_no']}\" style=\"min-width:100px;\" onclick=\"javascript:document.location='?mod=static&action=list'\">
<input type=hidden name=action value=\"{$action}\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=doaction value=\"mass_update\">
<input type=hidden name=mod value=\"mass_static_actions\">";
	foreach ( $selected_news as $newsid ) {
		$newsid = intval($newsid);
		echo "<input type=hidden name=selected_news[] value=\"$newsid\">\n";
	}
	
	echo <<<HTML
</td>
		    </tr>
		</table>
  </div>
</div>
</form>
HTML;
	
	echofooter();
	exit();

}

if( $action == "mass_delete" ) {
	
	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $lang['mass_static_delete'] );
	
	echo <<<HTML
<form method="post">
<div class="panel panel-default">
  <div class="panel-heading">
    {$lang['mass_static_delete']}
  </div>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="100" class="text-center">
{$lang['mass_confirm']}
HTML;
	
	echo "(<b>" . count( $selected_news ) . "</b>) $lang[mass_static_confirm_3]<br><br>
<input class=\"btn bg-teal btn-sm btn-raised position-left\" type=submit value=\"{$lang['mass_yes']}\" style=\"min-width:100px;\"><input type=button class=\"btn bg-danger btn-sm btn-raised position-left\" value=\"{$lang['mass_no']}\" style=\"min-width:100px;\" onclick=\"javascript:document.location='?mod=static&action=list'\">
<input type=hidden name=action value=\"do_mass_delete\">
<input type=hidden name=user_hash value=\"{$dle_login_hash}\">
<input type=hidden name=mod value=\"mass_static_actions\">";
	foreach ( $selected_news as $newsid ) {
		$newsid = intval($newsid);
		echo "<input type=hidden name=selected_news[] value=\"$newsid\">\n";
	}
	
	echo <<<HTML
</td>
		    </tr>
		</table>
  </div>
</div>
</form>
HTML;
	
	echofooter();
	exit();

} elseif( $action == "do_mass_delete" ) {
	
	$deleted_articles = 0;
	DLEFiles::init();
	
	foreach ( $selected_news as $id ) {
		
		$id = intval( $id );

		$deleted_articles ++;
		
		$db->query( "DELETE FROM " . PREFIX . "_static WHERE id='$id'" );

		$db->query( "SELECT * FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );

		while ( $row = $db->get_row() ) {
			
			if( $row['onserver'] ) {
					
				if( trim($row['onserver']) == ".htaccess") die("Hacking attempt!");
				
				if( $row['is_public'] ) $uploaded_path = 'public_files/'; else $uploaded_path = 'files/';
	
				DLEFiles::Delete( $uploaded_path.$row['onserver'], $row['driver'] );

			} else {
				
				$dataimage = get_uploaded_image_info( $row['name'] );

				DLEFiles::Delete( "posts/" . $dataimage->path, $row['driver'] );

				if ($dataimage->hidpi) {
					DLEFiles::Delete("posts/{$dataimage->folder}/{$dataimage->hidpi}", $row['driver']);
				}

				if( $dataimage->thumb ) {
					
					DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $row['driver'] );

					if ($dataimage->hidpi) {
						DLEFiles::Delete("posts/{$dataimage->folder}/thumbs/{$dataimage->hidpi}", $row['driver'] );
					}

				}
				
				if( $dataimage->medium ) {
					
					DLEFiles::Delete( "posts/{$dataimage->folder}/medium/{$dataimage->name}", $row['driver'] );
					
					if ($dataimage->hidpi) {
						DLEFiles::Delete("posts/{$dataimage->folder}/medium/{$dataimage->hidpi}", $row['driver']);
					}
				}
			}
		}
	
		$db->query( "DELETE FROM " . PREFIX . "_static_files WHERE static_id = '$id'" );

	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '40', '')" );
	
	if( count( $selected_news ) == $deleted_articles ) {
		msg( "success", $lang['mass_static_delete'], $lang['mass_delok'], $_SESSION['static_referrer'] );
	} else {
		msg( "error", $lang['mass_notok'], "$deleted_articles $lang[mass_i] " . count( $selected_news ) . " $lang[mass_notok_1]", $_SESSION['static_referrer'] );
	}
	
} else {
	
	msg( "info", $lang['mass_noact'], $lang['mass_noact_1'], $_SESSION['static_referrer'] );

}
?>