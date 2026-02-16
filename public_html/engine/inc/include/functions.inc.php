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
 File: functions.inc.php
=====================================================
*/

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

if ( isset($config['auth_domain']) AND $config['auth_domain'] ) {

	$domain_cookie = explode (".", clean_url( $_SERVER['HTTP_HOST'] ));
	$domain_cookie_count = count($domain_cookie);
	$domain_allow_count = -2;
	
	if ( $domain_cookie_count > 2 ) {
	
		if ( in_array($domain_cookie[$domain_cookie_count-2], array('com', 'net', 'org') )) $domain_allow_count = -3;
		if ( $domain_cookie[$domain_cookie_count-1] == 'ua' ) $domain_allow_count = -3;
		
		$domain_cookie = array_slice($domain_cookie, $domain_allow_count);
	}
	
	$domain_cookie = "." . implode (".", $domain_cookie);
	
	if( ip2long($_SERVER['HTTP_HOST']) == -1 OR ip2long($_SERVER['HTTP_HOST']) === false) define( 'DOMAIN', $domain_cookie );
	else define( 'DOMAIN', '' );

} else define( 'DOMAIN', '' );

function dle_session( $sid = false ) {
	global $config;
	
	$params = session_get_cookie_params();

	if ( DOMAIN ) $params['domain'] = DOMAIN;
	
	if (isset($config['only_ssl']) AND $config['only_ssl']) $params['secure'] = true;

	session_set_cookie_params($params['lifetime'], "/", $params['domain'], $params['secure'], true);

	if ( $sid ) session_id( $sid );

	session_start();

}

function set_cookie($name, $value, $expires) {
	global $config;
	
	if( $expires ) {
		
		$expires = time() + ($expires * 86400);
	
	} else {
		
		$expires = FALSE;
	
	}
	
	if (isset($config['only_ssl']) AND $config['only_ssl']) setcookie( $name, $value, $expires, "/", DOMAIN, TRUE, TRUE );
	else setcookie( $name, $value, $expires, "/", DOMAIN, FALSE, TRUE );

}

function check_login($username, $md5_password, $post = true, $check_log = false) {
	global $member_id, $db, $user_group, $lang, $_IP, $_TIME, $config;

	if( $username == "" OR $md5_password == "" ) return false;
	
	$result = false;
	
	if( $post ) {
		
		$username = $db->safesql( $username );
		if( strlen($md5_password) > 72 ) $md5_password = substr($md5_password, 0, 72);

		if ($config['auth_metod']) {

			if ( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\/|\\\|\&\~\*\+]/", $username) ) return false;	
			$where_name = "email='{$username}'";
	
		} else {

			if ( preg_match( "/[\||\'|\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $username) ) return false;
			$where_name = "name='{$username}'";
	
		}

		$member_id = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE {$where_name}" );
		
		if( isset($member_id['user_id']) AND $member_id['user_id'] AND $member_id['password'] AND $member_id['banned'] != 'yes' AND $user_group[$member_id['user_group']]['allow_admin'] ) {
			
			if( is_md5hash( $member_id['password'] ) ) {
				
				if($member_id['password'] == md5( md5($md5_password) ) ) {
					$result = true;
				}
				
			} else {
				
				if(password_verify($md5_password, $member_id['password'] ) ) {
					$result = true;
				}
				
			}
			
		}
		
		if( !$result ) {

			$member_id = array ();
	
			$username = $db->safesql(trim( htmlspecialchars( stripslashes($username), ENT_QUOTES, 'UTF-8')));
	
			if( version_compare($config['version_id'], "9.3", '>') ) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$username."', '{$_TIME}', '{$_IP}', '89', '')" );

		}

	} else {
		
		$username = intval( $username );
		
		$member_id = $db->super_query( "SELECT * FROM " . USERPREFIX . "_users WHERE user_id='{$username}'" );
		
		if( isset($member_id['user_id']) AND $member_id['user_id'] AND $member_id['password'] AND md5($member_id['password']) == $md5_password AND $user_group[$member_id['user_group']]['allow_admin'] AND $member_id['banned'] != 'yes' ) {

			$result = true;

		} else {
			
			$member_id['name'] = isset($member_id['name']) ? $member_id['name'] : '';
			$username = $db->safesql(trim( htmlspecialchars( stripslashes($member_id['name']), ENT_QUOTES, 'UTF-8')));

			$member_id = array ();
	
			if( version_compare($config['version_id'], "9.3", '>') ) $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$username."', '{$_TIME}', '{$_IP}', '90', '')" );

		}
	
	}

	if( $result ) {
		
		if( !allowed_ip( $member_id['allowed_ip'] ) OR !allowed_ip( $config['admin_allowed_ip'] ) ) {
			
			$member_id = array ();
			$result = false;

			if (isset($_COOKIE) and is_array($_COOKIE) and count($_COOKIE)) {
				foreach ($_COOKIE as $key => $value) {
					set_cookie($key, '', 0);
				}
			}

			session_unset();
			session_destroy();
			
			msg( "info", $lang['index_msge'], $lang['ip_block'] );
		
		}
	}

	if ( !$result ) { 

		if ($config['login_log']) $db->query( "INSERT INTO " . PREFIX . "_login_log (ip, count, date) VALUES('{$_IP}', '1', '".time()."') ON DUPLICATE KEY UPDATE count=count+1, date='".time()."'" );

	} else {

		if ( $check_log AND !isset($_SESSION['check_log']) ) {

			if( $post ) { $a_id = 82; $extr =""; } else { $a_id = 86; if (isset($_SERVER['HTTP_REFERER']) AND $_SERVER['HTTP_REFERER']) $extr = $db->safesql(htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES)); else $extr = "Direct DLE Adminpanel"; }

			if( version_compare($config['version_id'], "9.3", '>') )  $db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '{$a_id}', '{$extr}')" );
			
			$_SESSION['check_log'] = 1;
		}

	}

	return $result;
}


function deletenewsbyid( $id ) {
	global $config, $db;
	
	$id = intval($id);
	DLEFiles::init();
	
	$row = $db->super_query( "SELECT user_id FROM " . PREFIX . "_post_extras WHERE news_id = '{$id}'" );
	
	$db->query( "UPDATE " . USERPREFIX . "_users SET news_num=news_num-1 WHERE user_id='{$row['user_id']}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_post WHERE id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_extras WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_extras_cats WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_poll WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_poll_log WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_log WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_post_pass WHERE news_id='{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_tags WHERE news_id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_xfsearch WHERE news_id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_logs WHERE news_id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_subscribe WHERE news_id='{$id}'");

	deletecommentsbynewsid( $id );

	$row = $db->super_query( "SELECT images  FROM " . PREFIX . "_images WHERE news_id = '{$id}'" );

	if( isset($row['images']) AND $row['images']) {
		
		$listimages = explode( "|||", $row['images'] );
	
		foreach ( $listimages as $dataimage ) {
			
			$dataimage = get_uploaded_image_info($dataimage);
		
			$query = $db->safesql( $dataimage->path );
			$row = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE short_story LIKE '%{$query}%' OR full_story LIKE '%{$query}%' OR xfields LIKE '%{$query}%'");

			if( isset($row['count']) AND $row['count'] ) {
				continue;
			}
			
			if( $dataimage->remote ) $disk = DLEFiles::FindDriver($dataimage->url);
			else $disk = 0;

			DLEFiles::Delete( "posts/" . $dataimage->path, $disk );

			if ($dataimage->hidpi) {
				DLEFiles::Delete("posts/{$dataimage->folder}/{$dataimage->hidpi}", $disk);
			}

			if( $dataimage->thumb ) {
				
				DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $disk );

				if ($dataimage->hidpi) {
					DLEFiles::Delete("posts/{$dataimage->folder}/thumbs/{$dataimage->hidpi}", $disk);
				}
				
			}
			
			if( $dataimage->medium ) {
				
				DLEFiles::Delete( "posts/{$dataimage->folder}/medium/{$dataimage->name}", $disk );
				
				if ($dataimage->hidpi) {
					DLEFiles::Delete("posts/{$dataimage->folder}/medium/{$dataimage->hidpi}", $disk);
				}
			}
						
		}
	
		$db->query( "DELETE FROM " . PREFIX . "_images WHERE news_id = '{$id}'" );
	
	}

	$db->query( "SELECT * FROM " . PREFIX . "_files WHERE news_id = '{$id}'" );

	while ( $row = $db->get_row() ) {
		
		if( trim($row['onserver']) == ".htaccess") die("Hacking attempt!");
		
		if( $row['is_public'] ) $uploaded_path = 'public_files/'; else $uploaded_path = 'files/';

		DLEFiles::Delete( $uploaded_path.$row['onserver'], $row['driver'] );

	}

	$db->query( "DELETE FROM " . PREFIX . "_files WHERE news_id = '{$id}'" );

	$sql_result = $db->query( "SELECT user_id, favorites FROM " . USERPREFIX . "_users WHERE favorites LIKE '%{$id}%'" );
	
	while ( $row = $db->get_row($sql_result) ) {
		
		$temp_fav = explode( ",", $row['favorites'] );
		$new_fav = array();
		
		foreach ( $temp_fav as $value ) {
			$value = intval($value);
			if($value != $id ) $new_fav[] = $value;
		}
		
		if(count($new_fav)) $new_fav = $db->safesql(implode(",", $new_fav));
		else $new_fav = "";
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET favorites='{$new_fav}' WHERE user_id='{$row['user_id']}'" );

	}
}

function deleteuserbyid( $id ) {
	global $config, $db;
	
	$id = intval($id);

	$row = $db->super_query("SELECT user_id, name, foto FROM " . USERPREFIX . "_users WHERE user_id='{$id}'");

	if (isset($row['user_id']) AND $row['user_id']) {

		if($row['foto'] AND count(explode("@", $row['foto'])) != 2) {

			$url = @parse_url($row['foto']);
			$row['foto'] = basename($url['path']);

			$driver = DLEFiles::getDefaultStorage();
			$config['avatar_remote'] = intval($config['avatar_remote']);
			if ($config['avatar_remote'] > -1)  $driver = $config['avatar_remote'];

			DLEFiles::init($driver);
			DLEFiles::Delete("fotos/" . totranslit($row['foto']));

		}

		$db->query("DELETE FROM " . USERPREFIX . "_social_login WHERE uid='{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_banned WHERE users_id='{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_ignore_list WHERE user='{$row['user_id']}' OR user_from='{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_notice WHERE user_id = '{$row['user_id']}'");
		$db->query("DELETE FROM " . PREFIX . "_subscribe WHERE user_id='{$row['user_id']}'");
		$db->query("DELETE FROM " . PREFIX . "_logs WHERE `member` = '{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_comment_rating_log WHERE `member` = '{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_vote_result WHERE name = '{$row['name']}'");
		$db->query("DELETE FROM " . PREFIX . "_poll_log WHERE `member` = '{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_users WHERE user_id='{$row['user_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_users_delete WHERE user_id='{$row['user_id']}'");
		deletepmuserbyid($row['user_id']);

	}

}

function deletepmuserbyid($id) {
	global $db;
	
	$sql_result = $db->query("SELECT c.id AS conversation_id, c.sender_id, c.recipient_id FROM " . USERPREFIX . "_conversations c WHERE c.sender_id = '{$id}' OR c.recipient_id = '{$id}'");

	while ($row = $db->get_row($sql_result)) {
		
		if ($id == $row['sender_id']) {
			$sync_user_id = $row['recipient_id'];
		} else {
			$sync_user_id = $row['sender_id'];
		}

		if($sync_user_id == $id ) $sync_user_id = 0;

		$db->query("DELETE FROM " . USERPREFIX . "_conversations WHERE id='{$row['conversation_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_conversation_reads WHERE conversation_id='{$row['conversation_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_conversation_users WHERE conversation_id='{$row['conversation_id']}'");
		$db->query("DELETE FROM " . USERPREFIX . "_conversations_messages WHERE conversation_id='{$row['conversation_id']}'");

		if( $sync_user_id ) {
			$count = $db->super_query("SELECT COUNT(DISTINCT cu.conversation_id) AS total, COUNT(DISTINCT CASE WHEN cr.last_read_at IS NULL OR c.updated_at > cr.last_read_at THEN cu.conversation_id ELSE NULL END) AS unread FROM " . USERPREFIX . "_conversation_users cu JOIN " . USERPREFIX . "_conversations c ON cu.conversation_id = c.id LEFT JOIN " . USERPREFIX . "_conversation_reads cr ON cu.conversation_id = cr.conversation_id AND cu.user_id = cr.user_id WHERE cu.user_id = '{$sync_user_id}'");
			$db->query("UPDATE " . USERPREFIX . "_users SET pm_all='{$count['total']}', pm_unread='{$count['unread']}' WHERE user_id='{$sync_user_id}'");
		}
	}
}

function deletecomments( $id ) {
	global $config, $db;
	
	$id = intval($id);
	DLEFiles::init();
	
	$row = $db->super_query( "SELECT id, post_id, user_id, is_register, approve FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE id = '{$id}'" );
	$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$id}'" );	

	if( $row['is_register'] ) {
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-1 WHERE user_id ='{$row['user_id']}'" );
	}
	
	if($row['approve']) $db->query( "UPDATE " . PREFIX . "_post SET comm_num=comm_num-1 WHERE id='{$row['post_id']}'" );

	$db->query( "SELECT id, name, driver FROM " . PREFIX . "_comments_files WHERE c_id = '{$id}'" );
	
	while ( $row = $db->get_row() ) {
		
		$dataimage = get_uploaded_image_info( $row['name'] );
		
		DLEFiles::Delete( "posts/" . $dataimage->path, $row['driver'] );
		
		if( $dataimage->thumb ) {
			
			DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $row['driver'] );
			
		}
			
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_comments_files WHERE c_id = '{$id}'" );
	
	if ( $config['tree_comments'] ) {

		$sql_result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE parent = '{$id}'" );
	
		while ( $row = $db->get_row( $sql_result ) ) {
			deletecomments( $row['id'] );
		}

	}

}

function deletecommentsbynewsid( $id ) {
	global $config, $db;
	
	$id = intval($id);
	DLEFiles::init();
	
	$result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE post_id='{$id}'" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "DELETE FROM " . PREFIX . "_comment_rating_log WHERE c_id = '{$row['id']}'" );

		$sub_result = $db->query( "SELECT id, name, driver FROM " . PREFIX . "_comments_files WHERE c_id = '{$row['id']}'" );
		
		while ( $file = $db->get_row( $sub_result ) ) {
			
			$dataimage = get_uploaded_image_info( $file['name'] );
			
			DLEFiles::Delete( "posts/" . $dataimage->path, $file['driver'] );
			
			if( $dataimage->thumb ) {
				
				DLEFiles::Delete( "posts/{$dataimage->folder}/thumbs/{$dataimage->name}", $file['driver'] );
				
			}

		}
		
		$db->query( "DELETE FROM " . PREFIX . "_comments_files WHERE c_id = '{$row['id']}'" );
	
	}
	
	$result = $db->query( "SELECT COUNT(*) as count, user_id FROM " . PREFIX . "_comments WHERE post_id='{$id}' AND is_register='1' GROUP BY user_id" );
	
	while ( $row = $db->get_array( $result ) ) {
		
		$db->query( "UPDATE " . USERPREFIX . "_users SET comm_num=comm_num-{$row['count']} WHERE user_id='{$row['user_id']}'" );
	
	}
	
	$db->query( "DELETE FROM " . PREFIX . "_comments WHERE post_id='{$id}'" );


}

function deletecommentsbyuserid( $id, $ip = false ) {
	global $db;
	
	$id = intval($id);
	
	if($ip) {
		$ip = $db->safesql($ip);
		$result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE ip='{$ip}' AND is_register='0'" );
	} else {
		$result = $db->query( "SELECT id FROM " . PREFIX . "_comments WHERE user_id='{$id}' AND is_register='1'" );
	}
	
	while ( $row = $db->get_array( $result ) ) {
		deletecomments($row['id']);
	}

}

function formatsize($file_size) {
	
	if( !$file_size OR $file_size < 1) return '0 b';
	
    $prefix = array("b", "Kb", "Mb", "Gb", "Tb");
    $exp = floor(log($file_size, 1024)) | 0;

    $file_size = round($file_size / (pow(1024, $exp)), 2).' '.$prefix[$exp];
	$file_size = str_replace(",", ".", $file_size);

    return $file_size;

}

function CheckCanGzip() {
	
	if( headers_sent() || connection_aborted() || ! function_exists( 'ob_gzhandler' ) || ini_get( 'zlib.output_compression' ) ) return 0;
	
	if( isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip' ) !== false ) return "x-gzip";
	if( isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) return "gzip";
	
	return 0;
}

function GzipOut() {
	
	$ENCODING = CheckCanGzip();
	
	if( $ENCODING ) {
		$Contents = ob_get_contents();
		ob_end_clean();
		
		header( "Content-Encoding: $ENCODING" );
		
		$Contents = gzencode( $Contents, 1, FORCE_GZIP );
		echo $Contents;
		
		exit();
	} else {
		
		ob_end_flush(); 
		exit();
	}
}

function allowed_ip($ip_array) {
	
	$ip_array = trim( $ip_array );

	$_IP = get_ip();

	if( !$ip_array ) {
		return true;
	}
	
	if( strpos($_IP, ":") === false ) {
		$delimiter = ".";
	} else $delimiter = ":";
	
	$db_ip_split = explode( $delimiter, $_IP );
	$ip_lenght = count($db_ip_split);
	
	$ip_array = explode( "|", $ip_array );
	
	foreach ( $ip_array as $ip ) {
		
		$ip = trim( $ip );
		
		if( $ip == $_IP ) {
			
			return true;
		
		} elseif( count(explode ('/', $ip)) == 2 ) {
				
			if( maskmatch($_IP, $ip) ) return true;
				
		} else {
			
			$ip_check_matches = 0;
			$this_ip_split = explode( $delimiter, $ip );
			
			for($i_i = 0; $i_i < $ip_lenght; $i_i ++) {
				if( $this_ip_split[$i_i] == $db_ip_split[$i_i] OR $this_ip_split[$i_i] == '*' ) {
					$ip_check_matches += 1;
				}
			
			}
			
			if( $ip_check_matches == $ip_lenght ) return true;
		}
	
	}
	
	return false;
}


function maskmatch($IP, $CIDR) {
	
    list ($address, $netmask) = explode('/', $CIDR, 2);

	if( strpos($IP, ".") !== false AND strpos($CIDR, ".") !== false ) {
		
		return ( ip2long($IP) & ~((1 << (32 - $netmask)) - 1) ) == ip2long ($address);
	
	} elseif( strpos($IP, ":") !== false AND strpos($CIDR, ":") !== false ) {
		
        if (!((extension_loaded('sockets') && defined('AF_INET6')) || @inet_pton('::1'))) {
          return false;
        }
		
        $bytesAddr = unpack('n*', @inet_pton($address));
        $bytesTest = unpack('n*', @inet_pton($IP));

        if (!$bytesAddr || !$bytesTest) {
            return false;
        }

        for ($i = 1, $ceil = ceil($netmask / 16); $i <= $ceil; ++$i) {
            $left = $netmask - 16 * ($i - 1);
            $left = ($left <= 16) ? $left : 16;
            $mask = ~(0xffff >> $left) & 0xffff;
            if (($bytesAddr[$i] & $mask) != ($bytesTest[$i] & $mask)) {
                return false;
            }
        }
		
		return true;
		
	}
	
	return false;

}

function msg($type, $title, $text, $back = false) {
	global $lang;
	
	$buttons = array();
	
	if(is_array( $back )) {
		$bc = 1;
		
		foreach ($back as $key => $value) {
			
			if($bc == 1) $color="teal";
			elseif($bc == 2) $color="slate-600";
			elseif($bc == 3) $color="brown-600";
			else $color="primary-600";
			
			if( $value == $lang['add_s_5'] ) $target = " target=\"_blank\"";
			else $target="";
			
			$buttons[] = "<a class=\"btn btn-sm bg-{$color} btn-raised position-left\" href=\"{$key}\"{$target}>{$value}</a>";
			
			$bc++;
			
			if($bc > 4) $bc = 1;
		}
	} elseif( $back ) {
		$buttons[] = "<a class=\"btn btn-sm bg-teal btn-raised position-left\" href=\"{$back}\">{$lang['func_msg']}</a>";
	}
	
	if(count($buttons) ) {
		$back = "<div class=\"panel-footer\"><div class=\"text-center\">".implode('', $buttons)."</div></div>";
	} else $back ="";
	
	
	if (!is_array($title) AND $title == "error") $title = $lang['addnews_error'];

	echoheader( "<i class=\"fa fa-comment-o position-left\"></i><span class=\"text-semibold\">{$lang['header_box_title']}</span>", $title );

	if($type == "error") {
		$type = "alert-danger";
	} elseif ( $type == "warning" ) {
		$type = "alert-warning";
	} elseif ( $type == "success" ) {
		$type = "alert-success";
	} else $type = "alert-info";
	
	if( is_array( $title ) ) {
		$title = end($title);
	}

	echo <<<HTML
<div class="alert {$type} alert-styled-left alert-arrow-left alert-component message_box">
  <h4>{$title}</h4>
  <div class="panel-body">
		<table width="100%">
		    <tr>
		        <td height="80" class="text-center">{$text}</td>
		    </tr>
		</table>
	</div>
	{$back}
</div>
HTML;
	
	echofooter();
	die();
}

function echoheader($header_title, $header_subtitle) {
	global $skin_header, $skin_footer, $skin_not_logged_header, $member_id, $user_group, $js_array, $css_array, $config, $lang, $is_loged_in, $mod, $action, $langdate, $db, $dle_login_hash;

	if( !is_array( $header_subtitle )) $header_subtitle = array ( '' => $header_subtitle);
	
	$breadcrumb = array( "<li><a href=\"?mod=main\"><i class=\"fa fa-home position-left\"></i>{$lang['skin_main']}</a></li>" );

	foreach ($header_subtitle as $key => $value) {
		
		if($key) {
			$breadcrumb[] = "<li><a href=\"{$key}\">{$value}</a></li>";
		} else {
			$breadcrumb[] = "<li class=\"active\">{$value}</li>";
		}
	}

	$breadcrumb = implode('', $breadcrumb);

	include_once (DLEPlugins::Check(ENGINE_DIR . '/skins/default.skin.php'));
	
	$js = build_js($js_array);
	$css = build_css($css_array);
	
	$skin_header = str_replace( "{js_files}", $js, $skin_header );
	$skin_header = str_replace( "{css_files}", $css, $skin_header );
	$skin_not_logged_header = str_replace( "{js_files}", $js, $skin_not_logged_header );
	$skin_not_logged_header = str_replace( "{css_files}", $css, $skin_not_logged_header );
	
	if( $is_loged_in ) echo $skin_header;
	else echo $skin_not_logged_header;
}

function echofooter() {
	global $is_loged_in, $skin_footer, $skin_not_logged_footer;

	if( $is_loged_in ) echo $skin_footer;
	else echo $skin_not_logged_footer;

}

function listdir($dir) {
	
	if( is_dir($dir) ) {

		$current_dir = @opendir( $dir );
		
		if($current_dir !== false ) {
			while ( $entryname = readdir( $current_dir ) ) {
				if( is_dir( $dir."/".$entryname ) AND ($entryname != "." AND $entryname != "..") ) {
					listdir( $dir."/".$entryname );
				} elseif( $entryname != "." AND $entryname != ".." ) {
					@unlink( $dir."/".$entryname );
				}
			}
			@closedir( $current_dir );
			@rmdir( $dir );
		}

	}

}

function totranslit($var, $lower = true, $punkt = true, $translit = true ) {
	global $langtranslit;
	
	if ( !is_string($var) ) return "";

	$bads = array( '!', '*', '\'', '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '#', '[', ']', '%', '\\', '"', '<', '>', '^', '{', '}', '|', '`', '.php' );

	$var = html_entity_decode($var, ENT_QUOTES | ENT_HTML5, 'UTF-8');

	$var = strip_tags( $var );
	$var = str_replace(chr(0), '', $var);
	
	if ( $lower ) {
		$var = dle_strtolower($var);	
	}
	
	$var = str_replace( array( "\r\n", "\r", "\n" ), ' ', $var );
	$var = preg_replace( "/\s+/u", "-", $var );

	if ( !$punkt ) {
		$bads[] = '.';
	}
	
	$var = str_ireplace( $bads, '', $var );

	if( $translit ) {
		
		if (is_array($langtranslit) AND count($langtranslit) ) {
			$var = strtr($var, $langtranslit);
		}
		
		if ( $punkt ) {
			
			$var = preg_replace( "/[^a-z0-9\_\-.]+/mi", '', $var );
			$var = preg_replace( '#[.]+#i', '.', $var );
			
		} else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", '', $var );
	
	}
	
	$var = str_ireplace( ".php", ".ppp", $var );
	$var = preg_replace( '/\-+/', '-', $var );
	
	if( dle_strlen( $var ) > 250 ) {
		
		$var = dle_substr( $var, 0, 250 );
		
		if( ($temp_max = dle_strrpos( $var, '-' )) ) $var = dle_substr( $var, 0, $temp_max );
	
	}
    
	$var = trim( $var, '-' );
    $var = trim( $var );
	
	return $var;
}

function timezone_list(){
	static $timezones = null;

	if ($timezones === null) {
		$timezones = array();
		$offsets = array();
		$now = new DateTime('now', new DateTimeZone('UTC'));

		foreach (DateTimeZone::listIdentifiers() as $timezone) {
			try {

				$now->setTimezone(new DateTimeZone($timezone));
				$offsets[] = $offset = $now->getOffset();
				$timezones[$timezone] = '(' . format_GMT_offset($offset) . ') ' . format_timezone_name($timezone);

			} catch (Throwable $e) {}
		}

		array_multisort($offsets, $timezones);
	}

	return $timezones;
}

function format_GMT_offset($offset) {
	$hours = intval($offset / 3600);
	$minutes = abs(intval($offset % 3600 / 60));
	return 'GMT' . ($offset !== false ? sprintf('%+03d:%02d', $hours, $minutes) : '');
}

function format_timezone_name($name) {
	$name = str_replace('/', ', ', $name);
	$name = str_replace('_', ' ', $name);
	$name = str_replace('St ', 'St. ', $name);
	return $name;
}

function langdate($format, $stamp, $servertime = false, $custom = false ) {
	global $langdate, $member_id, $customlangdate;

	if( is_array($custom) ) $locallangdate = $customlangdate; else $locallangdate = $langdate;

	if (!$stamp) { $stamp = time(); }
	
	$local = new DateTime('@'.$stamp);

	if (isset($member_id['timezone']) AND $member_id['timezone'] AND !$servertime) {
		$localzone = $member_id['timezone'];

	} else {

		$localzone = date_default_timezone_get();
	}

	if ( !in_array( $localzone, DateTimeZone::listIdentifiers() ) ) $localzone = 'Europe/Moscow';

	$local->setTimeZone(new DateTimeZone($localzone));

	return strtr( $local->format($format), $locallangdate );

}

function difflangdate($format, $stamp) {
	global $_TIME, $langdate, $member_id, $lang, $langcommentsweekdays;

	if (!is_array($langdate)) {
		$langdate = array();
	}

	if (!is_array($langcommentsweekdays)) {
		$langcommentsweekdays = array();
	}

	if (!$stamp) {
		$stamp = $_TIME;
	}

	$olddate = new DateTime('@' . $stamp);
	$nowdate = new DateTime('@' . $_TIME);
	$yesterdaydate = new DateTime('-1 day');

	if (isset($member_id['timezone']) and $member_id['timezone']) {
		$localzone = $member_id['timezone'];
	} else {

		$localzone = date_default_timezone_get();
	}

	if ( !in_array( $localzone, DateTimeZone::listIdentifiers() ) ) $localzone = 'Europe/Moscow';

	$olddate->setTimeZone(new DateTimeZone($localzone));
	$nowdate->setTimeZone(new DateTimeZone($localzone));
	$yesterdaydate->setTimeZone(new DateTimeZone($localzone));

	$diff = $olddate->diff($nowdate);

	$days    = intval($diff->format('%a'));
	$hours   = intval($diff->format('%h'));
	$minutes = intval($diff->format('%i'));

	if ($olddate->format('Ymd') == $yesterdaydate->format('Ymd')) {

		$lang_format = str_replace('{date}', $lang['time_gestern'], $lang['diffs_format']);
		$lang_format = str_replace('{time}', $olddate->format('H:i'), $lang_format);

		return $lang_format;
	} elseif ($days < 1) {

		if ($hours < 1) {

			if ($minutes < 1) {

				return $lang['now_diffs'];
			} else {

				return $minutes . ' ' . declination(array('', $minutes, $lang['minutes_diffs'])) . ' ' . $lang['time_diffs'];
			}
		} elseif ($hours <= 12) {

			return $hours . ' ' . declination(array('', $hours, $lang['hours_diffs'])) . ' ' . $lang['time_diffs'];
		} else {

			$lang_format = str_replace('{date}', $lang['time_heute'], $lang['diffs_format']);
			$lang_format = str_replace('{time}', $olddate->format('H:i'), $lang_format);

			return $lang_format;
		}
	} else {

		if ($days < 6) {

			$lang_format = str_replace('{date}', $olddate->format('l'), $lang['diffs_format']);
			$lang_format = str_replace('{time}', $olddate->format('H:i'), $lang_format);

			return strtr($lang_format, $langcommentsweekdays);
		} else return strtr($olddate->format($format), $langdate);
	}
}

function declination($matches = array())
{

	$matches[1] = strip_tags($matches[1]);
	$matches[1] = str_replace(' ', '', $matches[1]);

	$matches[1] = intval($matches[1]);
	$words = explode('|', trim($matches[2]));
	$parts_word = array();

	switch (count($words)) {
		case 1:
			$parts_word[0] = $words[0];
			$parts_word[1] = $words[0];
			$parts_word[2] = $words[0];
			break;
		case 2:
			$parts_word[0] = $words[0];
			$parts_word[1] = $words[0] . $words[1];
			$parts_word[2] = $words[0] . $words[1];
			break;
		case 3:
			$parts_word[0] = $words[0];
			$parts_word[1] = $words[0] . $words[1];
			$parts_word[2] = $words[0] . $words[2];
			break;
		case 4:
			$parts_word[0] = $words[0] . $words[1];
			$parts_word[1] = $words[0] . $words[2];
			$parts_word[2] = $words[0] . $words[3];
			break;
	}

	$word = $matches[1] % 10 == 1 && $matches[1] % 100 != 11 ? $parts_word[0] : ($matches[1] % 10 >= 2 && $matches[1] % 10 <= 4 && ($matches[1] % 100 < 10 || $matches[1] % 100 >= 20) ? $parts_word[1] : $parts_word[2]);

	return $word;
}

function CategoryNewsSelection($selectedId = null, $deprecated = null, $nocat = true) {
	global $cat_info, $member_id, $user_group, $mod;
	
	$html = '';
	$groupedCategories = array();
	$allow_skip = true;

	if ($mod == "addnews" OR $mod == "editnews") {
		if ($member_id['cat_allow_addnews']) {
			$allow_list = explode(',', $member_id['cat_allow_addnews']);
		} else $allow_list = explode(',', $user_group[$member_id['user_group']]['cat_allow_addnews']);
	} else {
		$allow_list = explode(',', $user_group[$member_id['user_group']]['allow_cats']);
	}
	
	$not_allow_list = explode(',', $user_group[$member_id['user_group']]['not_allow_cats']);

	if ($mod == "usergroup" OR $mod == "editusers") {
		$allow_skip = false;
	}

	if ($nocat) $html .= '<option value="0"></option>';

	if (count($cat_info)) {

		foreach ($cat_info as $category) {
			$groupedCategories[$category['parentid']][] = $category;
		}

		$stack = isset($groupedCategories[0]) ? array_reverse($groupedCategories[0]) : array();
		$levels = array();
		foreach ($stack as $rootCategory) {
			$levels[$rootCategory['id']] = 0;
		}

		while (!empty($stack)) {

			$skip = false;
			$current = array_pop($stack);
			$currentLevel = $levels[$current['id']];

			if ($allow_skip AND $allow_list[0] != "all" AND !in_array($current['id'], $allow_list) ) $skip = true;
			if ($allow_skip AND in_array($current['id'], $not_allow_list) ) $skip = true;	

			$prefix = str_repeat('&nbsp;', $currentLevel * 4);

			if (is_array($selectedId)) {
				$selected = in_array($current['id'], $selectedId) ? ' selected' : '';
			} else {
				$selected = ($current['id'] == $selectedId) ? ' selected' : '';
			}

			if ( !$skip ) {
				$html .= sprintf(
					'<option value="%d"%s>%s%s</option>',
					$current['id'],
					$selected,
					$prefix,
					$current['name']
				);
			}

			if (isset($groupedCategories[$current['id']])) {
				foreach (array_reverse($groupedCategories[$current['id']]) as $childCategory) {
					$stack[] = $childCategory;
					$levels[$childCategory['id']] = $currentLevel + 1;
				}
			}
		}
	}

	return $html;
}

function array_selection($array_list, $selid = 0, $parentid = 0, $sublevelmarker = '', $returnstring = '') {

	$root_category = array ();
	
	if( $parentid == 0 ) {
		$returnstring .= '<option value="0"></option>';
	} else {
		$sublevelmarker .= '&nbsp;&nbsp;&nbsp;';
	}
	
	if( count( $array_list ) ) {
		
		foreach ( $array_list as $list ) {
			if( $list['parentid'] == $parentid ) $root_category[] = $list['id'];
		}
		
		if( count( $root_category ) ) {
			foreach ( $root_category as $id ) {
					
				$returnstring .= "<option value=\"" . $id . '" ';
					
				if( is_array( $selid ) ) {
					foreach ( $selid as $element ) {
						
						$element = intval($element);
						
						if( $element == $id ) $returnstring .= 'selected';
						
					}
				} elseif( intval($selid) == $id ) $returnstring .= 'selected';
					
				$returnstring .= '>' . $sublevelmarker . $array_list[$id]['title'] . '</option>';
				
				$returnstring = array_selection($array_list, $selid, $id, $sublevelmarker, $returnstring );
			}
		}
	}
	
	return $returnstring;
}

function dle_cache($prefix, $cache_id = false, $member_prefix = false) {
	global $config, $is_logged, $member_id, $dlefastcache;
	
	if( !$config['allow_cache'] ) return false;

	$config['clear_cache'] = (intval($config['clear_cache']) > 1) ? intval($config['clear_cache']) : 0;

	if( $is_logged ) $end_file = $member_id['user_group'];
	else $end_file = "0";
	
	if( ! $cache_id ) {
		
		$key = $prefix;
	
	} else {
		
		$cache_id = md5( $cache_id );
		
		if( $member_prefix ) $key = $prefix . "_" . $cache_id . "_" . $end_file;
		else $key = $prefix . "_" . $cache_id;
	
	}
	
	if( $config['cache_type'] ) {
		if( $dlefastcache->connection > 0 ) {
			return $dlefastcache->get($key);
		}
	}

	$buffer = @file_get_contents( ENGINE_DIR . "/cache/" . $key . ".tmp" );

	if ( $buffer !== false AND $config['clear_cache'] ) {

		$file_date = @filemtime( ENGINE_DIR . "/cache/" . $key . ".tmp" );
		$file_date = time()-$file_date;

		if ( $file_date > ( $config['clear_cache'] * 60 ) ) {
			$buffer = false;
			@unlink( ENGINE_DIR . "/cache/" . $key . ".tmp" );
		}

		return $buffer;

	} else return $buffer;

}

function create_cache($prefix, $cache_text, $cache_id = false, $member_prefix = false) {
	global $config, $is_logged, $member_id, $dlefastcache;
	
	if( !$config['allow_cache'] ) return false;
	
	if( $is_logged ) $end_file = $member_id['user_group'];
	else $end_file = "0";
	
	if( ! $cache_id ) {
		
		$key = $prefix;
		
	} else {
		
		$cache_id = md5( $cache_id );
		
		if( $member_prefix ) $key = $prefix . "_" . $cache_id . "_" . $end_file;
		else $key = $prefix . "_" . $cache_id;
	
	}
	
	if($cache_text === false) $cache_text = '';

	if( $config['cache_type'] ) {
		if( $dlefastcache->connection > 0 ) {
			$dlefastcache->set( $key, $cache_text );
			return true;
		}
	}

	file_put_contents (ENGINE_DIR . "/cache/" . $key . ".tmp", $cache_text, LOCK_EX);
	@chmod( ENGINE_DIR . "/cache/" . $key . ".tmp", 0666 );
	
	return true;
	
}

function clear_cache($cache_areas = false) {
	global $dlefastcache, $config;

	if( $config['cache_type'] ) {
		if( $dlefastcache->connection > 0 ) {
			$dlefastcache->clear( $cache_areas );
			return true;
		}
	}

	if ( $cache_areas ) {
		if(!is_array($cache_areas)) {
			$cache_areas = array($cache_areas);
		}
	}
		
	$fdir = opendir( ENGINE_DIR . '/cache' );
		
	while ( $file = readdir( $fdir ) ) {
		if( $file != '.htaccess' AND !is_dir(ENGINE_DIR . '/cache/' . $file) ) {
			
			if( $cache_areas ) {
				
				foreach($cache_areas as $cache_area) if( stripos( $file, $cache_area ) === 0 ) @unlink( ENGINE_DIR . '/cache/' . $file );
			
			} else {
				
				@unlink( ENGINE_DIR . '/cache/' . $file );
			
			}
		}
	}
}

function clear_all_caches() {
	global $config;
	
	listdir( ENGINE_DIR . '/cache/system/CSS' );
	listdir( ENGINE_DIR . '/cache/system/HTML' );
	listdir( ENGINE_DIR . '/cache/system/URI' );
	listdir( ENGINE_DIR . '/cache/system/plugins' );
	
	$fdir = opendir( ENGINE_DIR . '/cache/system/' );
	while ( $file = readdir( $fdir ) ) {
		if( !is_dir(ENGINE_DIR . '/cache/system/' . $file ) AND $file != '.htaccess' AND $file != 'cron.json' ) {
			@unlink( ENGINE_DIR . '/cache/system/' . $file );
		
		}
	}
	
	if( $config['cache_type'] ) {
		$fdir = opendir( ENGINE_DIR . '/cache' );
		while ( $file = readdir( $fdir ) ) {
			if( $file != '.htaccess' AND !is_dir(ENGINE_DIR . '/cache/' . $file)  ) {
					@unlink( ENGINE_DIR . '/cache/' . $file );
			}
		}
	}
	
	clear_cache();
	
	if (function_exists('opcache_reset')) {
		opcache_reset();
	}
	
}

function clear_static_cache_id( $save = true ) {

	$salt = str_shuffle("abchefghjkmnpqrstuvwxyz0123456789");

	$new_cache_id = "";

	for ($i = 0; $i < 5; $i++) {
		$new_cache_id .= $salt[random_int(0, 32)];
	}

	if ($save AND is_writable(ENGINE_DIR . '/data/config.php')) {

		include(ENGINE_DIR . '/data/config.php');

		$config['cache_id'] = $new_cache_id;

		$handler = fopen(ENGINE_DIR . '/data/config.php', "w");

		if ($handler !== false ) {
			
			fwrite($handler, "<?php \n\n//System Configurations\n\n\$config = array (\n\n");

			foreach ($config as $name => $value) {
				fwrite($handler, "'{$name}' => '{$value}',\n\n");
			}

			fwrite($handler, ");\n\n?>");
			fclose($handler);
		}

	}

	return $new_cache_id;

}

function xfieldsdataload($id) {
	
	if( $id == "" ) return;
	
	$xfieldsdata = explode( "||", $id );
	foreach ( $xfieldsdata as $xfielddata ) {
		list ( $xfielddataname, $xfielddatavalue ) = explode( "|", $xfielddata );
		$xfielddataname = str_replace( "&#124;", "|", $xfielddataname );
		$xfielddataname = str_replace( "__NEWL__", "\r\n", $xfielddataname );
		$xfielddatavalue = str_replace( "&#124;", "|", $xfielddatavalue );
		$xfielddatavalue = str_replace( "__NEWL__", "\r\n", $xfielddatavalue );
		$data[$xfielddataname] = $xfielddatavalue;
	}
	
	return $data;
}

function xfieldsload() {
	global $lang, $config;
	
	$path = ENGINE_DIR . '/data/xfields.txt';
	$filecontents = file( $path );
	$fields = array();
	$tmp_arr = array();

	if( !is_array( $filecontents ) ) {
		
		return array();
	
	} elseif( count($filecontents) ) {
		
		foreach ( $filecontents as $name => $value ) {
			
			if( trim($value) ) {
				
				$tmp_arr = explode( "|", trim($value, "\t\n\r\0\x0B") );
				
				foreach ( $tmp_arr as $name2 => $value2 ) {
					$value2 = str_replace( "&#124;", "|", $value2 );
					$value2 = str_replace( "__NEWL__", "\r\n", $value2 );
					$value2 = html_entity_decode($value2, ENT_QUOTES, 'UTF-8');
					$fields[$name][$name2] = $value2;
				}
				
			}
		}

		return $fields;

	}
	
	return array();
}

function clear_content ( $content, $len = 300, $replace_single_quote = true ) {
	
	global $config;
	
	if(!$content OR !is_string($content) ) {
		return '';
	}
	
	$remove = array ("\x60", "\t", "\n", "\r", '\t', '\n', '\r', "{PAGEBREAK}", "&nbsp;", "<br />", "<br>", " ," );
	$len = intval($len);
	
	$content = stripslashes($content);
	
	$content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	$content = preg_replace( "#\[hide(.*?)\](.+?)\[/hide\]#is", "", $content );
	$content = preg_replace( "'\[attachment=(.*?)\]'si", "", $content );
	$content = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "", $content );
	$content = preg_replace( "#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", "", $content );
	$content = preg_replace( "#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", "", $content );
	$content = preg_replace( "'{banner_(.*?)}'si", "", $content );
	$content = preg_replace( "'\\[banner_(.*?)\\](.*?)\\[/banner_(.*?)\\]'si", "", $content );
	$content = preg_replace( "#<pre(.*?)>(.+?)</pre>#is", "", $content );
	$content = str_replace( "&#1072;", "a", $content );
	$content = str_replace( "&#111;", "o", $content );
	$content = str_replace( "><", "> <", $content );

	$content = str_replace( $remove, ' ', $content );
	$content = strip_tags($content);

	$content = preg_replace("#(^|\s|>)((http|https)://\w+[^\s\[\]\<]+)#i", '', $content);

	if ($replace_single_quote) {
		$content = str_replace("&amp;amp;", "&amp;", htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
	} else {
		$content = str_replace("&amp;amp;", "&amp;", htmlspecialchars($content, ENT_COMPAT, 'UTF-8'));
	}
	
	$content = str_replace(array("{", "}", "[", "]"), array("&#123;", "&#125;", "&#91;", "&#93;"), $content);

	$content = preg_replace('/\s+/u', ' ', $content);

	if( $len AND $len > 1 ) {

		if(dle_strlen( $content ) > $len ) {
	
			$content = dle_substr( $content, 0, $len );
				
			if( ($temp_dmax = dle_strrpos( $content, ' ' )) ) $content = dle_substr( $content, 0, $temp_dmax );
				
		}

	}
	
	return trim($content);
}

function create_metatags($story, $ajax = false) {
	global $config, $db;
	
	$keyword_count = 20;
	$newarr = array ();
	$headers = array ();

	$bad_keywords_symbol = array (",", ".", "/", "#", ":", "@", "~", "=", "-", "+", "*", "^", "%", "$", "?", "!");
	$remove = array ('\t', '\n', '\r' );
	
	$story = explode( "{PAGEBREAK}", $story );
	$story = $story[0];

	$story = str_replace( $remove, ' ', $story );

	$_REQUEST['meta_title'] = isset($_REQUEST['meta_title']) ? trim( str_replace($remove, ' ', $_REQUEST['meta_title']) ) : '';
	$_REQUEST['descr'] = isset($_REQUEST['descr']) ? trim( str_replace($remove, ' ', $_REQUEST['descr']) ) : '';
	$_REQUEST['keywords'] = isset($_REQUEST['keywords']) ? trim( str_replace($remove, ' ', $_REQUEST['keywords']) ) : '';

	if( $_REQUEST['meta_title'] ) {
	
		$headers['title'] = clear_content( $_REQUEST['meta_title'], 300, false );
		
		$headers['title'] = $db->safesql( $headers['title']  );
		

	} else $headers['title'] = "";
	
	if( $_REQUEST['descr'] ) {
		
		$headers['description'] = clear_content( $_REQUEST['descr'], 300, false );
		
		$headers['description'] = $db->safesql( $headers['description'] );
		
	} elseif($config['create_metatags'] OR $ajax) {

		$headers['description'] = clear_content(stripslashes($story), 0, false);
		
		if( dle_strlen( $headers['description'] ) > 300 ) {
			
			$headers['description'] = dle_substr( $headers['description'], 0, 300 );
			
			if( ($temp_dmax = dle_strrpos( $headers['description'], ' ' )) ) $headers['description'] = dle_substr( $headers['description'], 0, $temp_dmax );

		}
		
		$headers['description'] = $db->safesql( $headers['description'] );

	} else {

		$headers['description'] = '';

	}
	
	if( $_REQUEST['keywords'] ) {
		
		$arr = explode( ",", clear_content($_REQUEST['keywords'], 0, false) );
		$newarr = array();

		foreach ( $arr as $word ) {
			$newarr[] = trim(str_replace($bad_keywords_symbol, '', $word));
		}

		$_REQUEST['keywords'] = implode( ", ", $newarr );

		$headers['keywords'] = $db->safesql( $_REQUEST['keywords'] );

	} elseif( $config['create_metatags'] OR $ajax) {

		$story = clear_content(str_replace($bad_keywords_symbol, '', stripslashes($story)), 0, false);

		$arr = explode(" ", $story );
		
		foreach ( $arr as $word ) {
			$word = str_replace("&amp;", "&", $word);
			if( dle_strlen( $word ) > 4 ) $newarr[] = $word;
		}
		
		$arr = array_count_values( $newarr );
		arsort( $arr );
		
		$arr = array_keys( $arr );
		
		$offset = 0;
		
		$arr = array_slice( $arr, $offset, $keyword_count );
		
		$headers['keywords'] = $db->safesql( implode( ", ", $arr ) );
		
	} else {

		$headers['keywords'] = '';

	}

	return $headers;
}

function set_vars($file, $data) {
	
	$file = totranslit($file, true, false);
	
	if ( is_array($data) OR is_int($data) OR is_string($data) ) {
		
		file_put_contents (ENGINE_DIR . '/cache/system/' . $file . '.json', json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), LOCK_EX);
		@chmod( ENGINE_DIR . '/cache/system/' . $file . '.json', 0666 );
		
	}
}

function get_vars($file) {
	$file = totranslit($file, true, false);

	$data = @file_get_contents( ENGINE_DIR . '/cache/system/' . $file . '.json' );

	if ( $data !== false ) {

		$data = json_decode( $data, true );
		if ( is_array($data) OR is_int($data) OR is_string($data) ) return $data;

	} 

	return false;	
}

function get_folder_list( $folder = 'language' ) {
	global $lang;
	$allowed_folder = array( 'language', 'templates' );
	
	$list = array ();
	
	if( !in_array($folder, $allowed_folder) ) {
		return $list;
	}
	
	if( !$handle = opendir( ROOT_DIR . "/". $folder ) ) {
		$list[]['name'] = $lang['opt_errfo']." ".$folder;
		return $list;
	}
	
	while ( false !== ($file = readdir( $handle )) ) {
		
		if( is_dir( ROOT_DIR . "/".$folder."/".$file ) AND ($file != "." and $file != "..") ) {
			
			if( is_file( ROOT_DIR . "/".$folder."/".$file."/info.json" ) ) {
				
				$data = json_decode( trim(file_get_contents( ROOT_DIR . "/".$folder."/".$file."/info.json" ) ), true );
				
				if( isset($data['name']) AND $data['name'] ) {
					$list[$file] = $data;
					continue;
				}
			}
			
			$list[$file]['name'] = $file;
		}
		
	}

	closedir( $handle );
	ksort($list);

	return $list;

	
}

function get_groups($id = false) {
	global $user_group;
	
	$returnstring = "";
	
	foreach ( $user_group as $group ) {
		$returnstring .= '<option value="' . $group['id'] . '" ';
		
		if( is_array( $id ) ) {
			foreach ( $id as $element ) {
				if( $element == $group['id'] ) $returnstring .= 'SELECTED';
			}
		} elseif( $id and $id == $group['id'] ) $returnstring .= 'SELECTED';
		
		$returnstring .= ">" . $group['group_name'] . "</option>\n";
	}
	
	return $returnstring;

}
function permload($id) {
	
	if( $id == "" ) return;
	
	$data = array ();
	
	$groups = explode( "|", $id );
	foreach ( $groups as $group ) {
		list ( $groupid, $groupvalue ) = explode( ":", $group );
		$data[$groupid][1] = ($groupvalue == 1) ? "selected" : "";
		$data[$groupid][2] = ($groupvalue == 2) ? "selected" : "";
		$data[$groupid][3] = ($groupvalue == 3) ? "selected" : "";
	}
	return $data;
}

function check_xss() {

	if (isset($_GET['mod']) AND isset($_GET['action']) AND $_GET['mod'] == "editnews" AND $_GET['action'] == "list") return;
	if (isset($_GET['mod']) AND isset($_GET['action']) AND $_GET['mod'] == "static" AND $_GET['action'] == "list") return;
	if (isset($_GET['mod']) AND ($_GET['mod'] == "tagscloud" OR $_GET['mod'] == "links" OR $_GET['mod'] == "redirects"  OR $_GET['mod'] == "metatags") ) return;
	
	$url = html_entity_decode( urldecode( $_SERVER['QUERY_STRING'] ), ENT_QUOTES, 'ISO-8859-1' );

	$url = str_replace( "\\", "/", $url );

	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, './' ) !== false) || (strpos( $url, '../' ) !== false) || (strpos( $url, '\'' ) !== false) || (strpos( $url, '.php' ) !== false) ) {

			header( "HTTP/1.1 403 Forbidden" );
			die( "Hacking attempt!" );
		
		}
	
	}
	
	$url = html_entity_decode( urldecode( $_SERVER['REQUEST_URI'] ), ENT_QUOTES, 'ISO-8859-1' );
	$url = str_replace( "\\", "/", $url );
	
	if( $url ) {
		
		if( (strpos( $url, '<' ) !== false) || (strpos( $url, '>' ) !== false) || (strpos( $url, '"' ) !== false) || (strpos( $url, '\'' ) !== false) ) {
			header( "HTTP/1.1 403 Forbidden" );
			die( "Hacking attempt!" );
		
		}
	
	}

}

function clean_url($url) {
	
	if( $url == '' ) return;
	
	$url = str_replace( "http://", "", $url );
	$url = str_replace( "https://", "", $url );
	if( strtolower( substr( $url, 0, 4 ) ) == 'www.' ) $url = substr( $url, 4 );
	$url = explode( '/', $url );
	$url = reset( $url );
	$url = explode( ':', $url );
	$url = reset( $url );
	
	return $url;
}

function get_url($id) {
	global $cat_info;

	if( !$id ) return '';

	$id = explode (",", $id);
	$id = intval($id[0]);

	$paths = buildCategoryPaths($cat_info);

	if (isset($paths[$id]) AND $paths[$id]) {

		return $paths[$id];
	}

	return '';
}

function buildCategoryPaths($categories) {
	static $result = null;

	if ($result === null) {

		$result = array();
		$temp = array();

		foreach ($categories as $category) {
			$temp[$category['id']] = [
				'alt_name' => $category['alt_name'],
				'parentid' => $category['parentid']
			];
		}

		foreach ($categories as $category) {
			$id = $category['id'];
			$path = array();
			$current = $id;

			while ($current != 0) {
				array_unshift($path, $temp[$current]['alt_name']);
				$current = $temp[$current]['parentid'];
			}

			$result[$id] = implode('/', $path);
		}

	}

    return $result;
}

function convert_unicode($t, $to = '') {
// deprecated
	return $t;
}

function check_netz($ip1, $ip2) {
	
	if( strpos($ip1, ":") === false ) {
		$delimiter = ".";
	} else $delimiter = ":";
	
	$ip1 = explode( $delimiter, $ip1 );
	$ip2 = explode( $delimiter, $ip2 );
	
	if( $ip1[0] != $ip2[0] ) return false;
	if( $ip1[1] != $ip2[1] ) return false;
	
	if($delimiter == ":") {
		if( $ip1[2] != $ip2[2] ) return false;
		if( $ip1[3] != $ip2[3] ) return false;
	}
	
	return true;

}

function compare_filter($a, $b) {
	
	$a = explode( "|", $a );
	$b = explode( "|", $b );
	
	if( $a[1] == $b[1] ) return 0;
	
	return strcasecmp( $a[1], $b[1] );

}

function build_js($js) {
	global $config;

	$js_array = array();
	$i=0;
	$defer = "";
	
	if ($config['js_min']) {

		$js_array[] = "<script src=\"engine/classes/min/index.php?g=admin&amp;v={$config['cache_id']}\"></script>";

		if ( count($js) ) $js_array[] = "<script src=\"engine/classes/min/index.php?f=".implode(",", $js)."&amp;v={$config['cache_id']}\" defer></script>";

		return implode("\n", $js_array);

	} else {

		$default_array = array (
			'engine/skins/javascripts/application.js',
		);

		if ( count($js) ) $js = array_merge($default_array, $js); else $js = $default_array;

		foreach ($js as $value) {
			
			if($i > 0) $defer =" defer";
			
			$js_array[] = "<script src=\"{$value}?v={$config['cache_id']}\"{$defer}></script>";
			
			$i++;
		
		}

		return implode("\n", $js_array);
	}

}


function build_css($css) {
	global $config, $lang;

	if($lang['direction'] == 'rtl') $rtl_prefix ='_rtl'; else $rtl_prefix = '';

	$default_array = array (
		"engine/skins/fonts/inter/inter.css",
		"engine/skins/fonts/fontawesome/styles.min.css",
		"engine/skins/stylesheets/application{$rtl_prefix}.css"
	);
	
	$css_array = array();

	if ( count($css) ) $css = array_merge($default_array, $css); else $css = $default_array;

	if ($config['js_min']) {

		return "<link href=\"engine/classes/min/index.php?f=".implode(",", $css)."&amp;v={$config['cache_id']}\" rel=\"stylesheet\" type=\"text/css\">";

	} else {

		foreach ($css as $value) {
		
			$css_array[] = "<link href=\"{$value}?v={$config['cache_id']}\" rel=\"stylesheet\" type=\"text/css\">";
		
		}

		return implode("\n", $css_array);
	}

}

function dle_strlen($value, $charset = "utf-8" ) {

	if( function_exists( 'mb_strlen' ) ) {
		return mb_strlen( $value, 'UTF-8' );
	} elseif( function_exists( 'iconv_strlen' ) ) {
		return iconv_strlen($value, 'UTF-8');
	}

	return strlen($value);
}

function dle_substr($str, $start, $length, $charset = "utf-8" ) {

	if( function_exists( 'mb_substr' ) ) {
		return mb_substr( $str, $start, $length, 'UTF-8' );
	
	} elseif( function_exists( 'iconv_substr' ) ) {
		return iconv_substr($str, $start, $length, 'UTF-8');
	}

	return substr($str, $start, $length);

}

function dle_strrpos($str, $needle, $charset = "utf-8" ) {

	if( function_exists( 'mb_strrpos' ) ) {
		return mb_strrpos( $str, $needle, 0, 'UTF-8' );
	
	} elseif( function_exists( 'iconv_strrpos' ) ) {
		return iconv_strrpos($str, $needle, 'UTF-8');
	}

	return strrpos($str, $needle);

}

function dle_strpos($str, $needle, $charset = "utf-8" ) {

	if( function_exists( 'mb_strpos' ) ) {
		return mb_strpos( $str, $needle, 0, 'UTF-8' );
	} elseif( function_exists( 'iconv_strrpos' ) ) {
		return iconv_strpos($str, $needle, 0, 'UTF-8');
	}

	return strpos($str, $needle);

}

function dle_strtolower($str, $charset = "utf-8" ) {

	if( function_exists( 'mb_strtolower' ) ) {
		return mb_strtolower( $str, 'UTF-8' );
	}

	return strtolower($str);

}

function check_allow_login($ip, $max ) {
	global $db, $config;

	$config['login_ban_timeout'] = intval($config['login_ban_timeout']);
	
	$max = intval($max);
	
	if( $max < 2 ) $max = 2;
	
	$block_date = time()-($config['login_ban_timeout'] * 60);

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_login_log WHERE ip='{$ip}'" );

	if ( isset($row['count']) AND $row['count'] AND $row['date'] < $block_date ) {
		$db->query( "DELETE FROM " . PREFIX . "_login_log WHERE ip = '{$ip}'" );
		return true;
	}

	if ( isset($row['count']) AND $row['count'] >= $max AND $row['date'] > $block_date ) return false;
	else return true;

}

function detect_encoding($string) {  
  static $list = array('utf-8', 'windows-1251');
   
  foreach ($list as $item) {

	if( function_exists( 'mb_convert_encoding' ) ) {

		$sample = mb_convert_encoding( $string, $item, $item );

	} elseif( function_exists( 'iconv' ) ) {
	
		$sample = iconv($item, $item, $string);
	
	}

	if (md5($sample) == md5($string)) return $item;
   }

   return null;
}

function get_ip() {
	global $config;

	if (isset($config['own_ip']) AND $config['own_ip'] AND isset($_SERVER[$config['own_ip']]) ) $ip = $_SERVER[$config['own_ip']]; else $ip = $_SERVER['REMOTE_ADDR'];

	$temp_ip = explode(",", $ip);

	if(count($temp_ip) > 1) $ip = trim($temp_ip[0]);

	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
		return filter_var( $ip , FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ) {
		return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	return 'not detected';
}

function http_get_contents( $file, $post_params = false ) {
		
	$data = false;

	if (stripos($file, "http://") !== 0 AND stripos($file, "https://") !== 0) {
		return false;
	}
		
	if( function_exists( 'curl_init' ) ) {
			
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $file );

		if( is_array($post_params) ) {

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));

		}
		
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_TIMEOUT, 5 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			
		$data = curl_exec( $ch );
		curl_close( $ch );

		if( $data !== false ) return $data;
		
	} 

	if( preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen')) ) {

		if( is_array($post_params) ) {

			$file .= '?'.http_build_query($post_params);
		}

		$data = @file_get_contents( $file );
			
		if( $data !== false ) return $data;

	}

	return false;	
}

function cleanpath($path) {
	$path = trim(str_replace(chr(0), '', (string)$path));
	$path = str_replace(array('/', '\\'), '/', $path);
	$path = str_replace(array('"', "'"), '', $path);
	
	if (preg_match('#\p{C}+#u', $path)) {
        return '';
    }
	
	$path = strip_tags($path);
	
	$parts = array_filter(explode('/', $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) {
		if ('.' == $part OR !$part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = $part;
		}
	}

	return implode('/', $absolutes);
}

function is_md5hash( $md5 = '' ) {
  return strlen($md5) == 32 && ctype_xdigit($md5);
}

function generate_pin(){
	
	$pin = "";
	
	for($i = 0; $i < 5; $i ++) {

		$pin .= random_int(0, 9);
	}
	
    return $pin;
}

function normalize_name($var, $punkt = true) {
	
	if ( !is_string($var) ) return;

	$var = str_replace(chr(0), '', $var);
	
	$var = trim( strip_tags( $var ) );
	$var = preg_replace( "/\s+/u", "-", $var );
	$var = str_replace( "/", "-", $var );
	
	if ( $punkt ) $var = preg_replace( "/[^a-z0-9\_\-.]+/mi", "", $var );
	else $var = preg_replace( "/[^a-z0-9\_\-]+/mi", "", $var );

	$var = preg_replace( '#[\-]+#i', '-', $var );
	$var = preg_replace( '#[.]+#i', '.', $var );
	
	return $var;
}

function clearfilepath( $file, $ext=array() ) {

	$file = trim(str_replace(chr(0), '', (string)$file));
	$file = str_replace(array('/', '\\'), '/', $file);
	
	$path_parts = pathinfo( $file );

	if( count($ext) ) {
		if ( !isset($path_parts['extension']) OR !in_array( $path_parts['extension'], $ext ) ) return '';
	}
	
	$filename = normalize_name($path_parts['basename'], true);
	
	if( !$filename) return '';
	
	$parts = array_filter(explode('/', $path_parts['dirname']), 'strlen');
	
	$absolutes = array();
	
	foreach ($parts as $part) {
		if ('.' == $part) continue;
		if ('..' == $part) {
			array_pop($absolutes);
		} else {
			$absolutes[] = normalize_name($part, false);
		}
	}

	$path = implode('/', $absolutes);
	
	if ( $path ) return implode('/', $absolutes).'/'.$filename;
	else return '';

}

function execute_query($id, $query) {
	global $config, $db;

	if(!$query) return;
	
	if( version_compare($db->mysql_version, '5.6.4', '<') ) {
		$storage_engine = "MyISAM";
	} else $storage_engine = "InnoDB";
	
	$query = str_ireplace(array("{prefix}", "{userprefix}", "{charset}", "{engine}"), array(PREFIX, USERPREFIX, COLLATE, $storage_engine), $query);

	$db->query_errors_list = array();
		
	$db->multi_query( trim($query), false );
	
	$id = intval($id);

	if( count($db->query_errors_list) ){

		foreach($db->query_errors_list as $error) {
			$db->query( "INSERT INTO " . PREFIX . "_plugins_logs (plugin_id, area, error, type) values ('{$id}', '".$db->safesql( htmlspecialchars( $error['query'], ENT_QUOTES, 'UTF-8' ), false)."', '".$db->safesql( htmlspecialchars( $error['error'], ENT_QUOTES, 'UTF-8' ) )."', 'mysql')" );
		}
		
	}
	
	$db->query_errors_list = array();
	
}

function check_referer( $current_path ) {

	if( !isset($_SERVER['HTTP_REFERER']) OR !$_SERVER['HTTP_REFERER']) return false;
	
	$ref = parse_url($_SERVER['HTTP_REFERER']);
	$ref['host'] = clean_url($ref['host']);
	$ref['path'] = basename($ref['path']);
	
	$current_path = html_entity_decode($current_path, ENT_QUOTES | ENT_XML1, 'UTF-8');
	$curr = parse_url($current_path);
	$curr['host'] = clean_url($_SERVER['HTTP_HOST']);
	$curr['path'] = basename($curr['path']);
	
	if( $ref['path'] AND $curr['path'] AND $ref['host'] AND $curr['host'] AND $ref['path'] == $curr['path'] AND $ref['host'] == $curr['host'] ) {
		if( strpos($ref['query'], $curr['query']) !== false) {
			return true;
		}
	}
	
	return false;
	
}

function isSSL() {
    if( (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
		|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
		|| (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) == 'on')
		|| (!empty($_SERVER['HTTP_X_FORWARDED_SCHEME']) && strtolower($_SERVER['HTTP_X_FORWARDED_SCHEME']) == 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
		|| (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == 'on')
        || (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
		|| (!empty($_SERVER['CF_VISITOR']) && $_SERVER['CF_VISITOR'] == '{"scheme":"https"}')
		|| (!empty($_SERVER['HTTP_CF_VISITOR']) && $_SERVER['HTTP_CF_VISITOR'] == '{"scheme":"https"}')
    ) return true; else return false;
}

function get_uploaded_image_info( $file, $root_folder = 'posts', $force_size = false ) {
	global $config;
	
	$info = array();
	$file = explode("|", $file);
	$path = $file[0];
	$path = str_replace('&#58;',':', $path);

	if( stripos($path, "https://" ) === 0 OR stripos($path, "http://" ) === 0 OR stripos($path, "//" ) === 0 ) {
		
		$info['remote'] = true;
		$info['local'] 	= false;
		$info['exists'] = true;
		$info['url'] 	= $path;
		
		$path = explode("/{$root_folder}/", $path);
		
		$info['path'] = $path[1];
		$info['root'] = $path[0] . "/{$root_folder}/";
		
	} else {
		
		$info['remote'] = false;
		$info['exists'] = true;
		$info['path'] 	= $path;
		$info['root']   = $config['http_home_url'] . "uploads/{$root_folder}/";
		$info['url'] 	= $info['root'] . $info['path'];
		
		if( !file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] ) ) {
			
			$info['url'] = 	$config['http_home_url'] . "engine/skins/images/noimage.jpg";
			$file[1] = 0;
			$file[2] = 0;
			$file[3] = "0x0";
			$file[4] = "0 b";
			$info['exists'] = false;
	
		}

	}

	if( count($file) == 1) {

		$info['local_check'] = true;
		$file[1] = 0;
		$file[2] = 0;

		$files_array = explode('/', $file[0]);

		if( count($files_array) == 2 ) {
			$folder_prefix = $files_array[0].'/';
			$file_name =  $files_array[1];
		} else {
			$folder_prefix = '';
			$file_name =  $files_array[0];
		}

		if( file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $folder_prefix . "thumbs/" . $file_name ) ) $file[1] = 1;
		if( file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $folder_prefix . "medium/" . $file_name ) ) $file[2] = 1;
		
		if( $force_size ) {
			
			if( file_exists( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] ) ) {
				
				$img_info =  @getimagesize( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] );
				$file[3] = "{$img_info[0]}x{$img_info[1]}";
				$file[4] = formatsize( filesize( ROOT_DIR . "/uploads/{$root_folder}/" . $info['path'] ) );
	
			} else {
				
				$file[3] = "0x0";
				$file[4] = "0 b";
				
			}
				
		}
		
		
	} else $info['local_check'] = false;

	$parts = pathinfo($info['path']);
	$info['folder'] = $parts['dirname'];
	$info['name'] = $parts['basename'];

	if (isset($file[5]) and $file[5]) {
		$info['hidpi'] = pathinfo($info['name'], PATHINFO_FILENAME) . '@x2.' . pathinfo($info['name'], PATHINFO_EXTENSION);
	} else {
		$info['hidpi'] = false;
	}

	if( isset($file[1]) AND $file[1]) {
		$info['thumb'] = $info['root'] . $info['folder'] . "/thumbs/" . $info['name'];

		if( $info['hidpi'] ) $info['hidpi_thumb'] = $info['root'] . $info['folder'] . "/thumbs/" . $info['hidpi'];
	} else {
		$info['thumb'] = false;
	}
	
	if( isset($file[2]) AND $file[2]) {
		$info['medium'] = $info['root'] . $info['folder'] . "/medium/" . $info['name'];

		if ($info['hidpi']) $info['hidpi_medium'] = $info['root'] . $info['folder'] . "/medium/" . $info['hidpi'];
	} else {
		$info['medium'] = false;
	}

	if( isset($file[3]) AND $file[3]) $info['dimension'] = $file[3]; else $info['dimension'] = false;
	if( isset($file[4]) AND $file[4]) $info['size'] = $file[4]; else $info['size'] = false;

	return (object)$info;
}

function UniqIDReal($lenght = 10) {
	if (function_exists("random_bytes")) {
		$bytes = random_bytes(ceil($lenght / 2));
	} elseif (function_exists("openssl_random_pseudo_bytes")) {
		$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
	} else {
		throw new Exception("no cryptographically secure random function available");
	}
	return substr(bin2hex($bytes), 0, $lenght);
}

function check_ip($ips) {
	
	$_IP = get_ip();

	$blockip = false;
	
	if( is_array( $ips ) ) {
		
		if( strpos($_IP, ":") === false ) {
			$delimiter = ".";
		} else $delimiter = ":";
		
		$this_ip_split = explode( $delimiter, $_IP );
		$ip_lenght = count($this_ip_split);
		
		foreach ( $ips as $ip_line ) {

			$ip_arr = trim( $ip_line['ip'] );
			
			if( $ip_arr == $_IP ) {
				
				$blockip = $_IP;
				break;
			
			} elseif ( count(explode ('/', $ip_arr)) == 2 ) {
				
				if( maskmatch($_IP, $ip_arr) ) {
					$blockip = $ip_line['ip'];
					break;
				}
				
			} else {
				
				$ip_check_matches = 0;
				$db_ip_split = explode( $delimiter, $ip_arr );

				for($i_i = 0; $i_i < $ip_lenght; $i_i ++) {
					if( $this_ip_split[$i_i] == $db_ip_split[$i_i] or $db_ip_split[$i_i] == '*' ) {
						$ip_check_matches += 1;
					}
				
				}
			
				if( $ip_check_matches == $ip_lenght ) {
					$blockip = $ip_line['ip'];
					break;
				}
			}		
		}
	}
	
	return $blockip;
}

function get_sub_cats($id, $subcategory = '', $with_id = true) {
	global $cat_info;

	$subfound = array ();
	
	if( !$subcategory AND $with_id) $subcategory = $id;
	
	foreach ( $cat_info as $cats ) {
		if( $cats['parentid'] == $id ) {
			$subfound[] = $cats['id'];
		}
	}
	
	foreach ( $subfound as $parentid ) {
		
		if( $subcategory ) $subcategory .= "|";
		
		$subcategory .= $parentid;
		$subcategory = get_sub_cats( $parentid, $subcategory );
		
	}
	
	return $subcategory;

}

function get_related_ids($body, $id = false, $categories = false) {
	global $config, $db, $user_group, $cat_info;
	
	$id = intval($id) ? intval($id) : 0;

	if ($config['no_date'] and !$config['news_future']) $where_date = " AND date < '" . date("Y-m-d H:i:s", time()) . "'";
	else $where_date = "";

	$body = stripslashes(stripslashes($body));
	$body = html_entity_decode($body, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	$body = strip_tags($body);
	$body = trim(str_replace(array('+', '-', '*', '\\', '\'', '"','.', ',', '!', '%',  '(', ')', '[', ']', '{', '}', "\n", "\r", "\x60", "\t"), ' ', $body));
	$body = preg_replace('/\s+/u', ' ', $body);

	if (dle_strlen($body) > 2000) {

		$body = dle_substr($body, 0, 2000);

		if (($temp_dmax = dle_strrpos($body, ' '))) $body = dle_substr($body, 0, $temp_dmax);
	}

	$body = $db->safesql($body);

	$config['related_number'] = intval($config['related_number']);
	if ($config['related_number'] < 1) $config['related_number'] = 5;

	$allowed_cats = array();
	$not_allowed_cats = array();

	foreach ($user_group as $value) {
		
		if ($value['allow_cats'] != "all" AND !$value['allow_short']) $allowed_cats[] = $db->safesql($value['allow_cats']);
		if ($value['not_allow_cats'] != "" AND !$value['allow_short']) $not_allowed_cats[] = $db->safesql($value['not_allow_cats']);

	}

	$join_category = "";

	if (count($allowed_cats)) {

		$allowed_cats = implode(",", $allowed_cats);
		$allowed_cats = explode(",", $allowed_cats);
		$allowed_cats = array_unique($allowed_cats);
		sort($allowed_cats);

		if ($config['allow_multi_category']) {

			$join_category = "p INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . implode(',', $allowed_cats) . "')) c ON (p.id=c.news_id) ";
			$allowed_cats = "";
		} else {

			$allowed_cats = "category IN ('" . implode("','", $allowed_cats) . "') AND ";
		}
	} else $allowed_cats = "";

	if (count($not_allowed_cats)) {

		$not_allowed_cats = implode(",", $not_allowed_cats);
		$not_allowed_cats = explode(",", $not_allowed_cats);
		$not_allowed_cats = array_unique($not_allowed_cats);
		sort($not_allowed_cats);

		if ($config['allow_multi_category']) {

			$not_allowed_cats = "p.id NOT IN ( SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN (" . implode(',', $not_allowed_cats) . ") ) AND ";
			$join_category = "p ";
		} else {

			$not_allowed_cats = "category NOT IN ('" . implode("','", $not_allowed_cats) . "') AND ";
		}
	} else $not_allowed_cats = "";

	if ($config['related_only_cats'] AND $categories) {

		$allowed_cats = "";
		$not_allowed_cats = "";
		$allow_sub_cats = true;
		$all_cats = explode(",", $categories);
		$get_cats = array();

		foreach ($all_cats as $value) {

			if ($cat_info[$value]['show_sub']) {

				if ($cat_info[$value]['show_sub'] == 1) $get_cats[] = get_sub_cats($value);
				else {
					$get_cats[] = $value;
				}
			} else {

				if ($config['show_sub_cats']) $get_cats[] = get_sub_cats($value);
				else {
					$get_cats[] = $value;
				}
			}
		}

		$get_cats = implode("|", $get_cats);
		$get_cats = explode("|", $get_cats);

		if (count($get_cats) < 2) $allow_sub_cats = false;

		$get_cats = implode("|", $get_cats);

		if ($config['allow_multi_category']) {

			$get_cats = str_replace("|", "','", $get_cats);
			$join_category = "p INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('" . $get_cats . "')) c ON (p.id=c.news_id) ";
			$where_category = "";

		} else {

			if ($allow_sub_cats) {

				$get_cats = str_replace("|", "','", $get_cats);
				$where_category = "category IN ('" . $get_cats . "') AND ";
			} else {

				$where_category = "category = '{$get_cats}' AND ";
			}
		}

	} else $where_category = "";

	if ($id) {
		$id = " AND id != {$id}";
	} else $id = '';
	
	$related_ids = array();

	$db->query("SELECT id, MATCH (title, short_story, full_story, xfields) AGAINST ('{$body}') as score FROM " . PREFIX . "_post {$join_category}WHERE {$where_category}{$allowed_cats}{$not_allowed_cats}MATCH (title, short_story, full_story, xfields) AGAINST ('{$body}'){$id} AND approve=1" . $where_date . " ORDER BY score DESC LIMIT " . $config['related_number']);

	while ($related = $db->get_row()) {
		$related_ids[] = $related['id'];
	}

	if (count($related_ids)) {
		$related_ids = implode(",", $related_ids);
	} else $related_ids = '0';

	return $related_ids;
}