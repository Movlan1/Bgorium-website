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
File: links.php
=====================================================
*/

if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

mb_internal_encoding("UTF-8");

class Trie
{
	private $root;

	public function __construct() {
		$this->root = new TrieNode();
	}

	public function add($word, $data, $caseInsensitive = false) {
		$node = $this->root;
		$lowerWord = $caseInsensitive ? mb_strtolower($word) : $word;
		$originalWord = $word;

		foreach (preg_split('//u', $lowerWord, -1, PREG_SPLIT_NO_EMPTY) as $index => $char) {
			if (!isset($node->children[$char])) {
				$node->children[$char] = new TrieNode();
			}
			$node = $node->children[$char];
		}

		$node->data = [
			'original' => $originalWord,
			'link' => $data['link'],
			'target_blank' => $data['target_blank'],
			'title' => $data['title'],
			'origpattern' => $data['origpattern'],
			'max_count' => $data['max_count'] ?? -1
		];
	}

	public function search($text, $caseInsensitive = false) {
		$result = array();
		$length = mb_strlen($text);

		for ($i = 0; $i < $length; $i++) {
			$node = $this->root;
			$originalWord = '';

			for ($j = $i; $j < $length; $j++) {
				$char = mb_substr($text, $j, 1);
				$lowerChar = $caseInsensitive ? mb_strtolower($char) : $char;

				if (!isset($node->children[$lowerChar])) break;

				$node = $node->children[$lowerChar];
				$originalWord .= $char;

				if ($node->data !== null) {
					$prevChar = $i > 0 ? mb_substr($text, $i - 1, 1) : '';
					$nextChar = mb_substr($text, $j + 1, 1);

					if (!$this->isWordChar($prevChar) && !$this->isWordChar($nextChar)) {
						$result[] = array(
							'position' => $i,
							'word' => $originalWord,
							'data' => $node->data);
					}
				}
			}
		}

		return $this->filterOverlappingMatches($result);
	}
	
	private function filterOverlappingMatches($result) {
		usort($result, function ($a, $b) {
			return $a['position'] - $b['position'];
		});

		$filtered = array();
		$lastEnd = -1;

		foreach ($result as $match) {
			$start = $match['position'];
			$end = $start + mb_strlen($match['word']);

			if ($start > $lastEnd) {
				$filtered[] = $match;
				$lastEnd = $end;
			}
		}

		return $filtered;
	}

	private function isWordChar($char) {
		return preg_match('/\p{L}/u', $char);
	}	
}

class TrieNode {
	public $children = array();
	public $data = null;
}

function replace_links($html, $links) {

	if(!is_array($links) OR !count($links)) return $html;

	$groups = array();
	foreach ($links as $item) {
		if (comparehosts(urldecode($item['replace']), urldecode($_SERVER['REQUEST_URI']))) continue;

		$key = $item['rcount'] . '___' . ($item['case'] ? '1' : '0');
		$groups[$key][] = $item;
	}

	$matchers = array();
    foreach ($groups as $key => $group) {
        $trie = new Trie();
        foreach ($group as $item) {
            $trie->add($item['word'], [
                'link' => $item['replace'],
                'target_blank' => $item['targetblank'],
                'title' => $item['title'],
				'origpattern' => $item['origpattern'],
                'max_count' => $item['rcount']
            ], $item['case']);
        }
        $keyParts = explode('___', $key);
        $case = (bool)$keyParts[1];
        $matchers[] = array(
            'trie' => $trie,
            'case' => $case,
            'max_count' => $group[0]['rcount']
		);
    }

	$dom = new DOMDocument();
	$dom->encoding = 'UTF-8';
	
	libxml_use_internal_errors(true);

	if ( stripos($html, "<html") !== false ) {
		$full_page = true;
		$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	} else {
		$full_page = false;
		$dom->loadHTML('<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	}
	
	libxml_clear_errors();

	$xpath = new DOMXPath($dom);
	$nodes = $xpath->query('//td/text()[not(ancestor::a)] | 
              //div/text()[not(ancestor::a)] | 
              //p/text()[not(ancestor::a)] | 
              //li/text()[not(ancestor::a)] | 
              //span/text()[not(ancestor::a)]
              [not(ancestor::script|ancestor::style|ancestor::meta|ancestor::noscript|ancestor::textarea|ancestor::title)]');
	
	$has_replacement = false;
	$replacementCounts = array();

	foreach ($nodes as $node) {

		$text = $node->nodeValue;
		$parent = $node->parentNode;
		$offset = 0;
		$fragments = array();

		foreach ($matchers as $matcher) {

			$matches = $matcher['trie']->search($text, $matcher['case']);

			foreach ($matches as $match) {

				$word = $match['data']['origpattern'];
				$maxCount = $match['data']['max_count'];
				
				if (!isset($replacementCounts[$word])) {
					$replacementCounts[$word] = 0;
				}

				if ($maxCount > -1 && $replacementCounts[$word] >= $maxCount) {
					continue;
				}

				if ($match['position'] >= $offset) {
					if ($match['position'] > $offset) {
						$fragments[] = mb_substr($text, $offset, $match['position'] - $offset);
					}
					$has_replacement = true;
					$a = $dom->createElement('a');
					$a->setAttribute('href', htmlspecialchars($match['data']['link']));

					if( $match['data']['target_blank'] ) {
						$a->setAttribute('target', '_blank');
					}
					
					if ($match['data']['title']) {
						$a->setAttribute('title', htmlspecialchars($match['data']['title']));
					}

					$a->textContent = $match['word'];
					$fragments[] = $a;

					$offset = $match['position'] + mb_strlen($match['word']);
					$replacementCounts[$word] ++;
				}
			}
		}

		if ($offset < mb_strlen($text)) {
			$fragments[] = mb_substr($text, $offset);
		}



		if (count($fragments) > 1 || !($fragments[0] instanceof DOMText)) {
			$fragment = $dom->createDocumentFragment();
			foreach ($fragments as $part) {
				if ($part instanceof DOMNode) {
					$fragment->appendChild($part);
				} else {
					$fragment->appendChild($dom->createTextNode($part));
				}
			}
			$parent->replaceChild($fragment, $node);
		}
	}

	if(!$has_replacement) return $html;
	
	$newHTML = $dom->saveHTML();

	if($full_page) {
		$newHTML = str_replace('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">', '', $newHTML);
	} else {
		$newHTML = str_replace('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">', '', $newHTML);
		$newHTML = str_replace('<html><head></head><body>', '', $newHTML);
		$newHTML = str_replace('</body></html>', '', $newHTML);
		$newHTML = str_replace('<!DOCTYPE html>', '', $newHTML);
		$newHTML = trim($newHTML);
	}
	
	$newHTML = str_ireplace('%7Btheme%7D', '{THEME}', $newHTML);

	return $newHTML;
}

function comparehosts($a, $b) {

	if (!$a or !$b) return false;

	if (strpos($a, "//") === 0) $a = "http:" . $a;
	$a = parse_url($a);
	$a['path'] = isset($a['path']) ? $a['path'] : '';

	if (strpos($b, "//") === 0) $b = "http:" . $b;
	$b = parse_url($b);
	$b['path'] = isset($b['path']) ? $b['path'] : '';

	if (isset($a['query']) and $a['query']) $a = $a['path'] . '?' . $a['query'];
	else $a = $a['path'];
	if (isset($b['query']) and $b['query']) $b = $b['path'] . '?' . $b['query'];
	else $b = $b['path'];

	$a = preg_replace('#[/]+#i', '/', $a);
	$b = preg_replace('#[/]+#i', '/', $b);

	if (!isset($a[0]) or $a[0] != '/') $a = '/' . $a;

	if (!$a or !$b) return false;

	if ($a == $b) return true;
	else return false;
}

function generate_words_variations($string) {
    $words = explode(' ', $string);
    $parsedWords = [];
    $maxOptions = 1; 

    foreach ($words as $word) {
        if (preg_match('/\(([^)]+)\)/', $word, $matches)) {

            preg_match('/([^\(]+)\(([^)]+)\)/', $word, $matches);
            $base = $matches[1];
            $options = explode('|', $matches[2]);

            $variants = array_map(function ($opt) use ($base) {
                return $base . $opt;
            }, $options);

            $parsedWords[] = $variants;
            $maxOptions = max($maxOptions, count($variants));
        } else {
            $parsedWords[] = [$word];
        }
    }

    $result = [];

    for ($i = 0; $i < $maxOptions; $i++) {
        $phraseParts = [];
        foreach ($parsedWords as $variants) {
            $index = ($i < count($variants)) ? $i : (count($variants) - 1);
            $phraseParts[] = $variants[$index];
        }
        $result[] = implode(' ', $phraseParts);
    }

    return $result;
}

$replace_links = get_vars( "links" );

if( !is_array( $replace_links ) ) {

	$replace_links = array();
	
	$db->query( "SELECT * FROM " . PREFIX . "_links WHERE enabled=1 ORDER BY id DESC" );
	
	while ( $row = $db->get_row() ) {
		
		$processed = array();

		$row['word'] = trim(str_replace('&quot;', '"', stripslashes($row['word'])));
		if ($row['rcount'] < 1) $rcount = -1;
		else $rcount = intval($row['rcount']);
		if (!$row['only_one']) $caseInsensitive = true;
		else $caseInsensitive = false;

		if (preg_match('/^(.*?)\((.*?)\)/', $row['word'], $matches)) {

			$variants = generate_words_variations($row['word']);

			foreach ($variants as $v) {
				$processed[] = array(
					'word' => $v,
					'origpattern' => $row['word'],
					'replace' => $row['link'],
					'rcount' => $rcount,
					'targetblank' => $row['targetblank'],
					'title' => $row['title'],
					'case' => $caseInsensitive
				);
			}

		} else {
			$processed[] = [
				'word' => $row['word'],
				'origpattern' => $row['word'],
				'replace' => $row['link'],
				'rcount' => $rcount,
				'targetblank' => $row['targetblank'],
				'title' => $row['title'],
				'case' => $caseInsensitive
			];
		}

		foreach ($processed as $item) {
			if ($row['replacearea'] == 2) {
				$replace_links['news'][] = $item;
				$replace_links['comments'][] = $item;
			} elseif ($row['replacearea'] == 3) {
				$replace_links['news'][] = $item;
			} elseif ($row['replacearea'] == 4) {
				$replace_links['comments'][] = $item;
			} elseif ($row['replacearea'] == 5) {
				$replace_links['static'][] = $item;
			} elseif ($row['replacearea'] == 6) {
				$replace_links['news'][] = $item;
				$replace_links['comments'][] = $item;
				$replace_links['static'][] = $item;
			} else {
				$replace_links['all'][] = $item;
			}
		}
	
	}

	unset($processed);

	set_vars( "links", $replace_links );
	$db->free();

}