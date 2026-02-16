<?php

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../../' );
	die( "Hacking attempt!" );
}

$config['version_id'] = '16.0';
$config['cache_id'] = clear_static_cache_id(false);

$config['files_access'] = 'public';
$config['file_chunk_size'] = "1.5";
$config['allow_iframe'] = "1";
$config['iframe_domains'] = 'vkontakte.ru, ok.ru, vk.com, youtube.com, maps.google.ru, maps.google.com, player.vimeo.com, facebook.com, web.facebook.com, dailymotion.com, bing.com, w.soundcloud.com, video.yandex.ru, player.rutv.ru, rutube.ru, skydrive.live.com, docs.google.com, api.video.mail.ru, megogo.net, mapsengine.google.com, google.com, videoapi.my.mail.ru, coub.com, music.yandex.ru, rasp.yandex.ru, mixcloud.com, yandex.ru, my.mail.ru, icloud.com, codepen.io, embed.music.apple.com, drive.google.com, player.smotrim.ru';
$config['display_php_errors'] = "1";

unset($config['max_file_count']);
unset($config['rss_format']);
unset($config['thumb_dimming']);
unset($config['outlinetype']);

$handler = fopen(ENGINE_DIR.'/data/config.php', "w");
fwrite($handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n");
foreach($config as $name => $value) {
	fwrite($handler, "'{$name}' => \"{$value}\",\n\n");
}
fwrite($handler, ");\n\n?>");
fclose($handler);

?>