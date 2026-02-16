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
 File: admin.php
-----------------------------------------------------
 Use: adminpanel
=====================================================
*/

error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__FILE__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

require_once(ENGINE_DIR . '/classes/plugins.class.php');
require_once(DLEPlugins::Check(ENGINE_DIR . '/inc/include/init.php'));
