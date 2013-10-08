<?php
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @ignore
*/
include_once($phpbb_root_path . 'includes/search/fulltext_native.' . $phpEx);

/**
* fulltext_native_ja
*/
class fulltext_native_ja extends fulltext_native
{
	private $JapaneseSearchMod;
	private $Util;
	private $searchMatchType_is_partial;

	/**
	 * Initialises the fulltext_native search backend with min/max word length and makes sure the UTF-8 normalizer is loaded.
	 *
	 * @param	boolean|string	&$error	is passed by reference and should either be set to false on success or an error message on failure.
	 * @param	boolean
	 * @access	public
	 */
	function fulltext_native_ja(&$error)
	{
		if (defined('IN_INSTALL') && IN_INSTALL == true) {
			// Leave here
			return;
		}
		
		global $phpbb_root_path, $phpEx;
		global $config, $user;
		
		include_once($phpbb_root_path . "JapaneseSearchMod/autoload.php");
		phpBB3_JapaneseSearchModMain::load_lang();
		
		if (defined('ADMIN_START')) {
			$pages['set_config']	= 'adm/index.'.$phpEx . "?i=search&mode=settings";
			//$pages['construct_index']	= 'adm/index.'.$phpEx . "?i=search&mode=index";

			$request_var['i'] = request_var('i', '');
			$request_var['mode'] = request_var('mode', '');
			$current_page= 'adm/' . basename($_SERVER['SCRIPT_NAME']) . "?i={$request_var['i']}&mode={$request_var['mode']}";

			if (in_array($current_page, $pages)) {
				parent::fulltext_native($error);
				// Leave here
				return;
			}
		}

		$this->JapaneseSearchMod = new phpBB3_JapaneseSearchModMain();

		$this->searchMatchType_is_partial = ($config['fulltext_native_ja_search_match_type'] == phpBB3_JapaneseSearchModFulltextNativeJa::SEARCHMATCHTYPE_PARTIAL)? true : false;
		@include_once($phpbb_root_path . 'JapaneseSearchMod/include/Util_JapaneseChar.php');
		$this->Util = new Util_JapaneseChar();

		$this->alter_config();
		parent::fulltext_native($error);
	}

	private function alter_config()
	{
		global $config;

		$config['fulltext_native_load_upd'] 	= $config['fulltext_native_ja_load_upd'];
		$config['fulltext_native_min_chars'] 	= $config['fulltext_native_ja_min_chars'];
		$config['fulltext_native_max_chars'] 	= $config['fulltext_native_ja_max_chars'];
		$config['fulltext_native_common_thres'] = $config['fulltext_native_ja_common_thres'];
	}

	/**
	* Wakachigaki
	*
	* Any number of "allowed chars" can be passed as a UTF-8 string in NFC.
	*
	* @param	string	$text			Text to split, in UTF-8 (not normalized or sanitized)
	* @param	string	$allowed_chars	String of special chars to allow
	* @param	string	$encoding		Text encoding
	* @return	string
	*
	* @todo normalizer::cleanup being able to be used?
	*/
	function cleanup($text, $allowed_chars = null, $encoding = 'utf-8')
	{
		global $phpbb_root_path, $phpEx;

		// Convert the text to UTF-8
		$encoding = strtolower($encoding);
		if ($encoding != 'utf-8') {
			$text = utf8_recode($text, $encoding);
		}

		/**
		* Replace HTML entities and NCRs
		*/
		$text = htmlspecialchars_decode(utf8_decode_ncr($text), ENT_QUOTES);

		/**
		* Load the UTF-8 normalizer
		*
		* If we use it more widely, an instance of that class should be held in a
		* a global variable instead
		*/
		utf_normalizer::nfc($text);

		// JapaneseSearchMod
		{
			global $config;
			
			if ($config['fulltext_native_ja_canonical_transformation']) {
				$text = $this->Util->convertAlphanumericToHankaku($text);
				$text = $this->Util->convertKatakanaToZenkaku($text);
				$text = strtolower($text);
			}

			if (isset($allowed_chars) && !empty($allowed_chars) && is_string($allowed_chars)) {
				$new_words = array();
				$text = preg_replace('#\s+#u', ' ', $text);
				$words = explode(' ', $text);
				foreach($words as $word)
				{
					if (strpbrk($word, $allowed_chars) === false)
					{
						$word = $this->JapaneseSearchMod->wakachigaki($word);
					}
					$new_words[] = $word;
				}
				$text = implode(' ', $new_words);
			} else {
				$text = $this->JapaneseSearchMod->wakachigaki($text);
			}
		}

		return $text;
	}

	/**
	* Split a text into words of a given length
	*
	* The text is converted to UTF-8, cleaned up, and split. Then, words that
	* conform to the defined length range are returned in an array.
	*
	* NOTE: duplicates are NOT removed from the return array
	*
	* @param	string	$text	Text to split, encoded in UTF-8
	* @return	array			Array of UTF-8 words
	*
	* @access	private
	*/
	function split_message($text)
	{
		global $phpbb_root_path, $phpEx, $user;

		$match = $words = array();

		/**
		* Taken from the original code
		*/
		// Do not index code
		$match[] = '#\[code(?:=.*?)?(\:?[0-9a-z]{5,})\].*?\[\/code(\:?[0-9a-z]{5,})\]#is';
		// BBcode
		$match[] = '#\[\/?[a-z0-9\*\+\-]+(?:=.*?)?(?::[a-z])?(\:?[0-9a-z]{5,})\]#';

		$min = $this->word_length['min'];
		$max = $this->word_length['max'];

		$isset_min = $min - 1;

		/**
		* Clean up the string, remove HTML tags, remove BBCodes
		*/
		$word = strtok($this->cleanup(preg_replace($match, ' ', strip_tags($text)), -1), ' ');

		while (strlen($word)) {
			if (strlen($word) > 255 || strlen($word) <= $isset_min) {
				/**
				* Words longer than 255 bytes are ignored. This will have to be
				* changed whenever we change the length of search_wordlist.word_text
				*
				* Words shorter than $isset_min bytes are ignored, too
				*/
				$word = strtok(' ');
				continue;
			}

			$len = utf8_strlen($word);

			/**
			* Test whether the word is too short to be indexed.
			*
			* Note that this limit does NOT apply to CJK and Hangul
			*/
			if (($len < $min)
			// JapaneseSearchMod
			// $word は "ご飯", "お題", "お盆", "お勧め", "たい焼き" 等のように必ずしも漢字で始まる word とは限らない
			// そのため $word に漢字が１つでも含まれている場合は以下の処理を行わない
			// 言い換えれば、漢字を１つでも含むキーワードは必ずインデクス化するということ
			&& !$this->Util->fullMatch('Kanji', $word))
			{
				/**
				 * Note: this could be optimized. If the codepoint is lower than Hangul's range
				 * we know that it will also be lower than CJK ranges
				 */
				// $word の最初の文字が ハングル・漢字・中国語文字 ではない場合、そのワードを無視する
				if ((strncmp($word, UTF8_HANGUL_FIRST, 3) < 0 || strncmp($word, UTF8_HANGUL_LAST, 3) > 0)
				&& (strncmp($word, UTF8_CJK_FIRST, 3) < 0 || strncmp($word, UTF8_CJK_LAST, 3) > 0)
				&& (strncmp($word, UTF8_CJK_B_FIRST, 4) < 0 || strncmp($word, UTF8_CJK_B_LAST, 4) > 0))
				{
					$word = strtok(' ');
					continue;
				}
			}

			$words[] = $word;
			$word = strtok(' ');
		}

		return $words;
	}

	/**
	* Returns a list of options for the ACP to display
	*/
	function acp()
	{
		global $phpbb_root_path, $phpEx;
		global $user, $config;

		if ($config['search_type'] != 'fulltext_native_ja') {
			$onclick_js = "popup(this.href, 900, 700, '_phpBB3_JapaneseSearchMod');return false;";
			$href = append_sid("{$phpbb_root_path}JapaneseSearchMod/adm/setup.$phpEx");
			$tpl = sprintf($user->lang['JSM_FULLTEXTNATIVEJA_NOT_SELECTED_YET'], phpBB3_JapaneseSearchModMain::VERSION, "<a onclick=\"{$onclick_js}\" href=\"{$href}\">", '</a>');
		} elseif (!isset($config['JapaneseSearchMod_version']) || ($config['JapaneseSearchMod_version'] != phpBB3_JapaneseSearchModMain::VERSION)) {
			$onclick_js = "popup(this.href, 900, 700, '_phpBB3_JapaneseSearchMod');return false;";
			$href = append_sid("{$phpbb_root_path}JapaneseSearchMod/adm/setup.$phpEx", 'tabmenu=setup');
			$tpl = sprintf($user->lang['JSM_FULLTEXTNATIVEJA_NOT_SETUP_YET'], phpBB3_JapaneseSearchModMain::VERSION, "<a onclick=\"{$onclick_js}\" href=\"{$href}\">", '</a>');
		}
		if (isset($tpl)) {
			return array(
				'tpl'		=> $tpl,
				'config'	=> array(),
			);
		}

		/**
		* if we need any options, copied from fulltext_native for now, will have to be adjusted or removed
		*/
		$engine_options = '';
		$engine_list = phpBB3_JapaneseSearchModFulltextNativeJa::get_indexer_list();
		foreach ($engine_list as $engine) {
			$name = str_replace('_', ' ', $engine);
			$selected = ($config['fulltext_native_ja_index_engine'] == $engine) ? ' selected="selected"' : '';
			$engine_options .= '<option value="' . $engine . '"' . $selected . '>' . $name . '</option>';
		}

		// $lang_INDEX_ENGINE_EXPLAIN
		$onclick_js = "popup(this.href, 900, 700, '_phpBB3_JapaneseSearchMod');return false;";
		$href = append_sid("{$phpbb_root_path}JapaneseSearchMod/adm/indexer.$phpEx");
		$lang_INDEX_ENGINE_EXPLAIN = sprintf($user->lang['JSM_FULLTEXTNATIVEJA_INDEX_ENGINE_EXPLAIN'], "<a onclick=\"{$onclick_js}\" href=\"{$href}\">", '</a>');

		$tpl = '
		<dl>
			<dt><label for="fulltext_native_ja_index_engine">' . $user->lang['JSM_FULLTEXTNATIVEJA_INDEX_ENGINE'] . ':</label><br /><span>' . $lang_INDEX_ENGINE_EXPLAIN . '</span></dt>
			<dd><select id="" name="config[fulltext_native_ja_index_engine]">' . $engine_options . '</select></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_ja_search_match_type">' . $user->lang['JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE'] . ':</label><br /><span>' . $user->lang['JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE_EXPLAIN'] . '</span></dt>
			<dd><label><input type="radio" id="fulltext_native_ja_search_match_type" name="config[fulltext_native_ja_search_match_type]" value="1"' . (($config['fulltext_native_ja_search_match_type']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $user->lang['JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE_FULL'] . '</label><label><input type="radio" name="config[fulltext_native_ja_search_match_type]" value="0"' . ((!$config['fulltext_native_ja_search_match_type']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $user->lang['JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE_PARTIAL'] . '</label></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_ja_canonical_transformation">' . $user->lang['JSM_FULLTEXTNATIVEJA_CANONICAL_TRANSFORMATION'] . ':</label><br /><span>' . $user->lang['JSM_FULLTEXTNATIVEJA_CANONICAL_TRANSFORMATION_EXPLAIN'] . '</span></dt>
			<dd><label><input type="radio" id="fulltext_native_ja_canonical_transformation" name="config[fulltext_native_ja_canonical_transformation]" value="1"' . (($config['fulltext_native_ja_canonical_transformation']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $user->lang['YES'] . '</label><label><input type="radio" name="config[fulltext_native_ja_canonical_transformation]" value="0"' . ((!$config['fulltext_native_ja_canonical_transformation']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $user->lang['NO'] . '</label></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_ja_load_upd">' . $user->lang['YES_SEARCH_UPDATE'] . ':</label><br /><span>' . $user->lang['YES_SEARCH_UPDATE_EXPLAIN'] . '</span></dt>
			<dd><label><input type="radio" id="fulltext_native_ja_load_upd" name="config[fulltext_native_ja_load_upd]" value="1"' . (($config['fulltext_native_ja_load_upd']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $user->lang['YES'] . '</label><label><input type="radio" name="config[fulltext_native_ja_load_upd]" value="0"' . ((!$config['fulltext_native_ja_load_upd']) ? ' checked="checked"' : '') . ' class="radio" /> ' . $user->lang['NO'] . '</label></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_ja_min_chars">' . $user->lang['MIN_SEARCH_CHARS'] . ':</label><br /><span>' . $user->lang['JSM_FULLTEXTNATIVEJA_MIN_SEARCH_CHARS_EXPLAIN'] . '</span></dt>
			<dd><input id="fulltext_native_ja_min_chars" type="text" size="3" maxlength="3" name="config[fulltext_native_ja_min_chars]" value="' . (int) $config['fulltext_native_ja_min_chars'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_ja_max_chars">' . $user->lang['MAX_SEARCH_CHARS'] . ':</label><br /><span>' . $user->lang['JSM_FULLTEXTNATIVEJA_MAX_SEARCH_CHARS_EXPLAIN'] . '</span></dt>
			<dd><input id="fulltext_native_ja_max_chars" type="text" size="3" maxlength="3" name="config[fulltext_native_ja_max_chars]" value="' . (int) $config['fulltext_native_ja_max_chars'] . '" /></dd>
		</dl>
		<dl>
			<dt><label for="fulltext_native_ja_common_thres">' . $user->lang['COMMON_WORD_THRESHOLD'] . ':</label><br /><span>' . $user->lang['COMMON_WORD_THRESHOLD_EXPLAIN'] . '</span></dt>
			<dd><input id="fulltext_native_ja_common_thres" type="text" size="3" maxlength="3" name="config[fulltext_native_ja_common_thres]" value="' . (double) $config['fulltext_native_ja_common_thres'] . '" /> %</dd>
		</dl>
		';

		// These are fields required in the config table
		return array(
			'tpl'		=> $tpl,
			'config'	=> array(
				'fulltext_native_ja_index_engine'				=> 'string',
				'fulltext_native_ja_search_match_type' 			=> 'bool', 
				'fulltext_native_ja_canonical_transformation'	=> 'bool', 
				'fulltext_native_ja_load_upd' 					=> 'bool', 
				'fulltext_native_ja_min_chars' 					=> 'integer:0:255', 
				'fulltext_native_ja_max_chars' 					=> 'integer:0:255', 
				'fulltext_native_ja_common_thres' 				=> 'double:0:100')
		);
	}

	/**
	* This function fills $this->search_query with the cleaned user search query.
	*
	* If $terms is 'any' then the words will be extracted from the search query
	* and combined with | inside brackets. They will afterwards be treated like
	* an standard search query.
	*
	* Then it analyses the query and fills the internal arrays $must_not_contain_ids,
	* $must_contain_ids and $must_exclude_one_ids which are later used by keyword_search().
	*
	* @param	string	$keywords	contains the search query string as entered by the user
	* @param	string	$terms		is either 'all' (use search query as entered, default words to 'must be contained in post')
	* 	or 'any' (find all posts containing at least one of the given words)
	* @return	boolean				false if no valid keywords were found and otherwise true
	*
	* @access	public
	*/
	function split_keywords($keywords, $terms)
	{
		global $db, $user, $config;

		// wakachigaki
		$keywords = trim($this->cleanup($keywords, '+-|()*'));

		$match = array(
			'#\s+#u',
			'#\|\|+#',
			'#\-+#',
			'#\(\|#',
			'#\|\)#',
			'#\*+#'
		);
		$replace = array(
			' ',
			'|',
			'-',
			'(',
			')',
			'*',
		);
		$keywords = preg_replace($match, $replace, $keywords);

		$valid_keywords = array();
		foreach (explode(' ', $keywords) as $keyword) {
			if (preg_match('#^\(.+\)$#u', $keyword)) {
				$ary = array();
				$keyword = substr($keyword, 1, -1);
				foreach(explode('|', $keyword) as $keyword_part) {
					if (empty($keyword_part)) {
						continue;
					}
					if (strpbrk($keyword_part, '()|') !== false) {
						trigger_error($user->lang('JSM_WARNING_CONTAIN_SPECIALCHARS', $keyword_part));
					}
					if ($keyword_part[0] === '-') {
						trigger_error($user->lang('JSM_WARNING_CONTAIN_MINUSCHAR_WITH_ORSEARCH', $keyword_part));
					}
					$ary[] = $keyword_part;
				}
				if (sizeof($ary)) {
					$valid_keywords[] = '(' . implode('|', array_unique($ary)) . ')';
				}
			} else {
				if (empty($keyword))	{
					continue;
				}
				if (strpbrk($keyword, '()|') !== false) {
					trigger_error($user->lang('JSM_WARNING_CONTAIN_SPECIALCHARS', $keyword));
				}
				if ($keyword[0] === '-') {
					if ($keyword === '-') {
						continue;
					}
					if ($terms == 'any') {
						trigger_error($user->lang('JSM_WARNING_CONTAIN_MINUSCHAR_WITH_ORSEARCH', $keyword));
					}
				}
				$valid_keywords[] = $keyword;
			}
		}
		$keywords = implode(' ', array_unique($valid_keywords));
		$keywords_ary = explode(' ', $keywords);

		// We limit the number of allowed keywords to minimize load on the database
		if ($config['max_num_search_keywords'] && sizeof($keywords_ary) > $config['max_num_search_keywords']) {
			trigger_error($user->lang('MAX_NUM_SEARCH_KEYWORDS_REFINE', $config['max_num_search_keywords'], sizeof($keywords_ary)));
		}

		// $keywords input format: each word separated by a space, words in a bracket are not separated

		// the user wants to search for any word, convert the search query
		if ($terms == 'any') {
			$words = array();

			preg_match_all('#([^\\s|()]+)(?:$|[\\s|()])#u', $keywords, $words);
			if (sizeof($words[1])) {
				$keywords = '(' . implode('|', $words[1]) . ')';
				$keywords_ary = explode(' ', $keywords);
			}
		}

		// set the search_query which is shown to the user
		$this->search_query = $keywords;

		$exact_words = array();
		foreach ($keywords_ary as $keyword) {
			if (preg_match('#^\(.+\)$#u', $keyword)) {
				$keyword = substr($keyword, 1, -1);
				foreach(explode('|', $keyword) as $keyword_part) {
					if (empty($keyword_part)) {
						continue;
					}
					$exact_words[] = $keyword_part;
				}
			} else {
				if (empty($keyword)) {
					continue;
				}
				if ($keyword[0] === '-') {
					$keyword = substr($keyword, 1);
				}
				$exact_words[] = $keyword;
			}
		}

		$index_words = $common_ids = array();
		if (sizeof($exact_words)) {
			$this->pickout_index_words($exact_words, $index_words, $common_ids);
		}
		unset($exact_words);
			
		// now analyse the search query

		$this->must_contain_ids = array();
		$this->must_not_contain_ids = array();
		//$this->must_exclude_one_ids = array(); // -(foo|bar) のようなキーワードは定義があいまいなため、検索しない

		$mode = '';
		$minus_prefixed = true;

		foreach ($keywords_ary as $keyword) {
			if (empty($keyword))	{
				continue;
			}

			// words which should not be included
			if ($keyword[0] == '-') {
				$keyword = substr($keyword, 1);
				$mode = 'must_not_contain';
				$minus_prefixed = true;
			}
			// words which have to be included
			else {
				// a group of words of which at least one word should be in every resulting post
				if (preg_match('#^\(.+\)$#u', $keyword)) {
					$keyword = array_unique(explode('|', substr($keyword, 1, -1)));
				}
				$minus_prefixed = false;
				$mode = 'must_contain';
			}

			if (empty($keyword)) {
				continue;
			}

			// JapaneseSearchMod
			$this->set_must_honyarara_ids($index_words, $common_ids, $keyword, $mode, $minus_prefixed);
		}

		// JapaneseSearchMod
		//$this->dump_debug_data($index_words);

		// we can't search for negatives only
		if (!sizeof($this->must_contain_ids)) {
			return false;
		}

		if (!empty($this->search_query)) {
			return true;
		}

		return false;
	}

	/**
	* Exclusively used at split_keywords()
	*
	* @param array $exact_words
	* @param array $index_words
	* @param array $common_ids
	*/
	private function pickout_index_words($exact_words, &$index_words, &$common_ids)
	{
		global $db;

		$valid_exact_words = array();
		foreach ($exact_words as $word) {
			$len = utf8_strlen(str_replace('*', '', $word));

			if ($len <= $this->word_length['max']
			&& ($len >= $this->word_length['min'] || $this->Util->partialMatch('Kanji', $word)))
			{
				$valid_exact_words[] = $word;
			}
		}
		if (!sizeof($valid_exact_words)) {
			return;
		}
		$exact_words = $valid_exact_words;

		$no_astelisk_words = $astelisk_words = array();
		foreach ($exact_words as $word) {
			if (strpbrk($word, '*') === false) {
				$no_astelisk_words[] = $word;
			} else	{
				$astelisk_words[] = $word;
			}
		}

		if (!$this->searchMatchType_is_partial) {
			$sql_where = ' WHERE ';
			if (sizeof($no_astelisk_words)) {
				$sql_where .= $db->sql_in_set('word_text', $no_astelisk_words);
			}
			if (sizeof($astelisk_words)) {
				if (sizeof($no_astelisk_words)) {
					$sql_where .= ' OR ';
				}
				for($i = 0, $size = sizeof($astelisk_words); $i < $size; ++$i) {
					$astelisk_words[$i] = str_replace('*', $db->any_char, $astelisk_words[$i]);
					$astelisk_words[$i] = str_replace('\\', '\\\\', $astelisk_words[$i]);
					$sql_where .= 'word_text ' . $db->sql_like_expression($astelisk_words[$i]);
					if (!isset($astelisk_words[$i+1])) {
						break;
					}
					$sql_where .= ' OR ';
				}
			}
		} else	{
			$sql_where = ' WHERE ';
			if (sizeof($no_astelisk_words))	{
				for($i = 0, $size = sizeof($no_astelisk_words); $i < $size; ++$i) {
					$no_astelisk_words[$i] = str_replace('\\', '\\\\', $no_astelisk_words[$i]);
					$sql_where .= 'word_text ' . $db->sql_like_expression($db->any_char . $no_astelisk_words[$i] . $db->any_char);
					if (!isset($no_astelisk_words[$i+1])) {
						break;
					}
					$sql_where .= ' OR ';
				}
			}
			if (sizeof($astelisk_words)) {
				if (sizeof($no_astelisk_words)) {
					$sql_where .= ' OR ';
				}
				for($i = 0, $size = sizeof($astelisk_words); $i < $size; ++$i) {
					$astelisk_words[$i] = rtrim($astelisk_words[$i], '*');
					$astelisk_words[$i] = ltrim($astelisk_words[$i], '*');
					$astelisk_words[$i] = str_replace('*', $db->any_char, $astelisk_words[$i]);
					$astelisk_words[$i] = str_replace('\\', '\\\\', $astelisk_words[$i]);
					$sql_where .= 'word_text ' . $db->sql_like_expression($db->any_char . $astelisk_words[$i] . $db->any_char);
					if (!isset($astelisk_words[$i+1])) {
						break;
					}
					$sql_where .= ' OR ';
				}
			}
		}

		$sql = 'SELECT word_id, word_text, word_common
				FROM ' . SEARCH_WORDLIST_TABLE . $sql_where . '
				ORDER BY word_count ASC';
		$result = $db->sql_query($sql);

		// store an array of words and ids, remove common words
		while ($row = $db->sql_fetchrow($result)) {
			if ($row['word_common'])	{
				$this->common_words[] = $row['word_text'];
				$common_ids[$row['word_text']] = (int) $row['word_id'];
				continue;
			}
			$index_words[$row['word_text']] = (int) $row['word_id'];
		}
		$db->sql_freeresult($result);
	}

	/**
	* Exclusively used at split_keywords()
	*
	* @param array $words			A data picked up with keyword(s) from search_wordlist table
	* @param array $common_ids		A data picked up with keyword(s) from search_wordlist table
	* @param string $word 			e.g. 'KOKIA', 'ありがとう'
	* @param string $mode 			'must_contain' or 'must_not_contain' or 'must_exclude_one'
	* @param bool $minus_prefixed 	True if minus character '-' is used to exclude one keyword (or one braket), else false.
	*/
	private function set_must_honyarara_ids($index_words, $common_ids, $keyword, $mode, $minus_prefixed)
	{
		global $db, $user;

		// if this is an array of words then retrieve an id for each
		if (is_array($keyword)) {
			$non_common_words = array();
			$id_words = array();
			foreach ($keyword as $i => $keyword_part) {
				$len = utf8_strlen(str_replace('*', '', $keyword_part));
				if (($len < $this->word_length['min'] && !$this->Util->partialMatch('Kanji', $keyword_part))
				 || $len > $this->word_length['max'])
				{
					$this->common_words[] = $keyword_part;
				} else {
					if ($this->in_words_match($index_words, $keyword_part, $keyword_ids)) {
						foreach ($keyword_ids as $id) {
							$id_words[] = $id;
						}
						$id_words = array_unique($id_words);
					} else	{
						$non_common_words[] = $keyword_part;
					}
				}
			}
			if (sizeof($id_words)) {
				sort($id_words);
				if (sizeof($id_words) == 1)	{
					$this->{$mode . '_ids'}[] = $id_words[0];
				} else	{
					$this->{$mode . '_ids'}[] = $id_words;
				}
			}
			// throw an error if we shall not ignore unexistant words
			else if (!$minus_prefixed && sizeof($non_common_words))	{
				trigger_error(sprintf($user->lang['WORDS_IN_NO_POST'], implode(', ', $non_common_words)));
			}
			unset($non_common_words);
		}
		// else we only need one id
		else {
			$len = utf8_strlen(str_replace('*', '', $keyword));
			if (($len < $this->word_length['min'] && !$this->Util->partialMatch('Kanji', $keyword))
			 || $len > $this->word_length['max'])
			{
				$this->common_words[] = $keyword;
			}
			else if ($this->in_words_match($index_words, $keyword, $keyword_ids))	{
				switch ($mode) {
					case 'must_contain' :
						$this->{$mode . '_ids'}[] = $keyword_ids;
						break;
					case 'must_not_contain' :
						foreach ($keyword_ids as $id)
						{
							$this->{$mode . '_ids'}[] = $id;
						}
						break;
				}
			}
			// throw an error if we shall not ignore unexistant words
			// 検索キーワードの直前に - が付かない場合
			else if (!$minus_prefixed) {
				// コモンワードではない場合
				if (!isset($common_ids[$keyword])) {
					trigger_error(sprintf($user->lang['WORD_IN_NO_POST'], $keyword));
				}
			}
		}

		$this->set_must_contain_words($index_words);
	}

	/**
	* Exclusively used at set_must_honyarara_ids()
	*
	* @param array $words
	* @param string $word
	* @param array $word_ids
	*/
	private function in_words_match($words, $word, &$word_ids)
	{
		$word = preg_quote($word, '#');
		$word = str_replace('\*', '.*', $word);

		$word_ids = array();
		foreach ($words as $word_text => $word_id) {
			$pattern = ($this->searchMatchType_is_partial)? '#'.$word.'#u' :  '#^'.$word.'$#u';

			if (preg_match($pattern, $word_text)) {
				$word_ids[] = $word_id;
			}
		}

		return (sizeof($word_ids))? true : false;
	}

	private $must_contain_words = array();

	private function set_must_contain_words($words)
	{
		$this->must_contain_words = array_flip($words);
	}

	public function get_must_contain_words()
	{
		return $this->must_contain_words;
	}

	/**
	* Exclusively used at split_keywords()
	*
	* @param array $words
	*/
	private function dump_debug_data($index_words)
	{
		global $user;

		if (defined('DEBUG') && defined('DEBUG_EXTRA') && $user->data['user_type'] == USER_FOUNDER) {
			print('<b>Following is important properties and values assigned at fulltext_native_ja::split_keywords()</b> : ');
			print('<br />' . "\n" . '$index_words = ');var_dump($index_words);;print(";\n");
			print('<br />' . "\n" . '$this->must_contain_ids = ');var_dump($this->must_contain_ids);;print(";\n");
			print('<br />' . "\n" . '$this->must_not_contain_ids = ');var_dump($this->must_not_contain_ids);;print(";\n");
			print('<br />' . "\n" . '$this->must_exclude_one_ids = ');var_dump($this->must_exclude_one_ids);;print(";\n");
			print('<br />' . "\n" . '$this->common_words = ');var_dump($this->common_words);;print(";\n");
		}
	}

}
