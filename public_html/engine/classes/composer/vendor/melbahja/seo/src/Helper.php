<?php
namespace Melbahja\Seo;

/**
 * @package Melbahja\Seo
 * @since v2.0
 * @see https://git.io/phpseo
 * @license MIT
 * @copyright 2019-present Mohamed Elabhja
 */
class Helper
{

	public static $encoding = 'UTF-8';

	public static function escape(string $text): string
	{
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, static::$encoding);
		$text = htmlspecialchars($text, ENT_COMPAT | ENT_HTML5, static::$encoding);

		$text = str_replace(array("{", "}", "[", "]"), array("&#123;", "&#125;", "&#91;", "&#93;"), $text);

		return $text;
	}

	/**
	 * Escape url for sitemaps.
	 *
	 * @param  string $url
	 * @return string
	 */
	public static function escapeUrl(string $url): string
	{

		$url = str_replace(['&amp;', '&apos;', '&quot;'], ['&', "'", '"'], $url);

		$url = parse_url($url);
		$url['path'] = $url['path'] ?? '';
		$url['query'] = $url['query'] ?? '';

		if ($url['query'] !== '') {
			$url['query'] = "?{$url['query']}";
		}

		return str_replace(
			['&', "'", '"', '>', '<'],
			['&amp;', '&apos;', '&quot;', '&gt;', '&lt;'],
			$url['scheme'] . "://{$url['host']}{$url['path']}{$url['query']}"
		);
	}
}

