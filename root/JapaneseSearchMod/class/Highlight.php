<?php

class phpBB3_JapaneseSearchModHighlight extends phpBB3_JapaneseSearchModFulltextNativeJa
{
	private $is_FulltextNativeJa;
	private $current_page;

	private $hilit = array();

	private $Util;

	function __construct()
	{
		global $phpbb_root_dir, $phpEx;
		global $config;

		$this->is_FulltextNativeJa = ($config['search_type'] == 'fulltext_native_ja')? true : false;

		if ($this->is_FulltextNativeJa)
		{
			phpBB3_JapaneseSearchModMain::assert_setup();

			@include_once($phpbb_root_dir . 'JapaneseSearchMod/include/Util_JapaneseChar.php');
			$this->Util = new Util_JapaneseChar();
		}

		$this->current_page = basename($_SERVER['SCRIPT_NAME'], ".{$phpEx}");
	}

	function highlightFilter($hilit)
	{
		global $config;

		if (!$this->is_FulltextNativeJa)
		{
			// ネイティブコード
			return $hilit = implode('|', explode(' ', preg_replace('#\s+#u', ' ', str_replace(array('+', '-', '|', '(', ')', '&quot;'), ' ', $hilit))));
		}

		// キーワードの直前に "-" がある場合、そのキーワードを一応取り除いておく
		$hilit = preg_replace('#(^|\s)-[^\\s]+($|\s)#u', ' ', $hilit);

		$hilit = str_replace(array('|', '(', ')'), ' ', $hilit);
		$hilit = preg_replace('#\s+#u', ' ', $hilit);
		$hilit_words = explode(' ', $hilit);

		// 最小字数未満のキーワードは取り除く。ただし漢字を１つでも含むキーワードは例外
		$valid_words = array();
		foreach ($hilit_words as $word)
		{
			$len = utf8_strlen(str_replace('*', '', $word));

			if ($len >= $config['fulltext_native_ja_min_chars'] || $this->Util->partialMatch('Kanji', $word))
			{
				$valid_words[] = $word;
			}
		}
		$hilit_words = $valid_words;

		return implode('|', $hilit_words);
	}

	/**
	 * @param string $hilit 'KOKIA|ありがとう' 等。 パイプライン(|) でキーワードが区切られていることが前提。各キーワードは既に preg_quote() されている場合がある
	 * @param string $text
	 * @param string $css
	 * @return string
	 */
	function highlight($hilit, $text, $css='class="posthilit"')
	{
		if (!$this->is_FulltextNativeJa)
		{
			/*
			 * ネイティブコード
			 * (?!<.*) はおそらく不必要
			 */
			return $text = preg_replace('#(?!<.*)(?<!\w)(' . $hilit . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">$1</span>', $text);
		}

		global $config, $cache;

		$hilit = $this->preg_quote_decode($hilit, '#');
		$hilit = str_replace(array('\w+?', '\w*?'), '*', $hilit);
		$preg_pattern = $cache->get('_JSM_HighlightMatch' . strval($config['fulltext_native_ja_search_match_type']) . strval($config['fulltext_native_ja_canonical_transformation']) . '_' . md5($hilit));

		if ($preg_pattern == false)
		{
			global $phpbb_root_path, $phpEx;

			if ($this->current_page == 'search')
			{
				global $search;
			}
			else
			{
				include_once($phpbb_root_path . 'includes/search/fulltext_native_ja.php');
				$search = new fulltext_native_ja($error);
				$search->split_keywords('(' . $hilit . ')', 'all');
			}
			$index_words = $search->get_must_contain_words();

			$hilit_words = array();
			foreach (explode('|', $hilit) as $word)
			{
				if (strpos($word, '*') !== false)
				{
					$word = preg_quote($word, '#');
					$word = str_replace('\*', '.*', $word);
					foreach($index_words as $index_word)
					{
						if (preg_match('#^' .$word . '$#iu', $index_word))
						{
							$hilit_words[] = $index_word;
						}
					}
				}
				$hilit_words[] = $word;
			}
			$hilit_words = array_unique($hilit_words);
			$hilit_words = $this->sort_by_length($hilit_words);

			$match_words 	= array();
			if ($config['fulltext_native_ja_canonical_transformation'])
			{
				foreach($hilit_words as $word)
				{
					$match_words[] = $word;

					if ($isKatakana = $this->Util->partialMatch('Katakana', $word))
					{
						$match_words[] = $this->Util->convertKatakanaToHankaku($word);
					}
					if ($isAlphanumeric = $this->Util->partialMatch('Alphanumeric', $word))
					{
						$match_words[] = $this->Util->convertAlphanumericToZenkaku($word);
					}
					if ($isKatakana && $isAlphanumeric)
					{
						$word = $this->Util->convertKatakanaToHankaku($word);
						$word = $this->Util->convertAlphanumericToZenkaku($word);
						$match_words[] = $word;
					}
				}
				$match_words = array_unique($match_words);
			}
			else
			{
				$match_words = $hilit_words;
			}

			$match_patterns = array();
			foreach($match_words as $word)
			{
				$match_patterns[] = preg_quote($this->htmlentities($word), '#');
				$match_patterns[] = preg_quote($word, '#');
			}
			$match_patterns = array_unique($match_patterns);

			$preg_pattern = implode('|', $match_patterns);

			$cache->put('_JSM_HighlightMatch' . strval($config['fulltext_native_ja_search_match_type']) . strval($config['fulltext_native_ja_canonical_transformation']) . '_' . md5($hilit), $preg_pattern, $config['search_store_results']);
		}

		if ($preg_pattern != '')
		{
			$modifier = ($config['fulltext_native_ja_canonical_transformation'])? 'iu' : 'u';
			
			/*
			 * reference : http://www.php.net/manual/ja/regexp.reference.assertions.php
			 */
			$text = preg_replace('#(' . $preg_pattern  . ')(?![^<>]*(?:</s(?:cript|tyle))?>)#' . $modifier, '<span '.$css.'>$1</span>', $text);
		}

		return $text;
	}

	private function preg_quote_decode($str, $delimiter = null)
	{
		// 正規表現の特殊文字：　 . \ + * ? [ ^ ] $ ( ) { } = ! < > | : -
		$special_chars = '.\+*?[^]$(){}=!<>|:' . $delimiter;
		if(version_compare(PHP_VERSION, '5.3.0', '>='))
		{
			$special_chars .= '-';
		}

		$special_chars = preg_quote($special_chars, '#');

		$str = preg_replace('#\\\([' . $special_chars . '])#', '$1', $str);

		return $str;
	}

	private function htmlentities($string)
	{
		$string = htmlspecialchars($string);

		$replace = array(
			'<' => '&lt;', 
			'>' => '&gt;',
			'[' => '&#91;',
			']' => '&#93;',
			'.' => '&#46;',
			':' => '&#58;',
		);
		$string = str_replace(array_keys($replace), array_values($replace), $string);

		return $string;
	}

	private function htmlentities_decode($string)
	{
		$entities = array('&lt;', '&gt;', '&#91;', '&#93;', '&#46;', '&#58;', '&#058;');
		$characters = array('<', '>', '[', ']', '.', ':', ':');
		$string = str_replace($entities, $characters, $string);

		$string = htmlspecialchars_decode($string);

		return $string;
	}

	private function sort_by_length($hilit_words)
	{
		$sorted_words = array();
		$len_words = array();
		$max_len = 1;
		foreach ($hilit_words as $word)
		{
			$len = utf8_strlen($word);
			if ($len > $max_len)
			{
				$max_len = $len;
			}
			$len_words[$len][] = $word;
		}
		for($i = $max_len; $i > 0 ; --$i)
		{
			if (isset($len_words[$i]))
			{
				$sorted_words = array_merge($sorted_words, $len_words[$i]);
			}
		}
		$hilit_words = $sorted_words;

		return $hilit_words;
	}

}
