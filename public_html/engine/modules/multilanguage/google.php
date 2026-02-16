<?php
/*
=====================================================
 Multi-Language 4.11
-----------------------------------------------------
 Автор: Japing
-----------------------------------------------------
 https://japing.pw/
-----------------------------------------------------
 Copyright (c) 2014-2025  Japing
=====================================================
 Данный код защищен авторскими правами
=====================================================
*/

if(version_compare(phpversion(), '8.2.0', '>=')) {
	require (ENGINE_DIR . '/modules/multilanguage/google.8.2.php');
} elseif(version_compare(phpversion(), '8.1.0', '>=')) {
	require (ENGINE_DIR . '/modules/multilanguage/google.8.1.php');
}
