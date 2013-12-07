<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer_overview_searchTest extends phpBB3_JapaneseSearchModAdmPanel_Base {
	CONST WORDS_PER_PAGE = 20;
	private $is_submitted;
	private $start;
	private $keyword;
	private $search_match_type;
	private $form_key = 'searchTest';
	function main($current_page) {
		$this->current_page = $current_page;
		
		global $phpbb_root_path, $phpEx;
		global $db, $user, $template, $config;
		
		$this->is_submitted = request_var ( 'submit', '' );
		$this->start = request_var ( 'start', 0 );
		$this->keyword = utf8_normalize_nfc ( request_var ( 'keyword', '', true ) );
		$this->search_match_type = request_var ( 'search_match_type', $config ['fulltext_native_ja_search_match_type'] );
		
		add_form_key ( $this->form_key );
		
		$template->assign_vars ( array (
				'TITLE' => $user->lang ['JSM_SEARCHTEST_TITLE'],
				'TITLE_EXPLAIN' => $user->lang ['JSM_SEARCHTEST_TITLE_EXPLAIN'],
				
				'U_ACTION' => append_sid ( $current_page ) . '#main',
				'KEYWORD' => $this->keyword,
				
				'SEARCHMATCHTYPE_FULL_VALUE' => phpBB3_JapaneseSearchModFulltextNativeJa::SEARCHMATCHTYPE_FULL,
				'SEARCHMATCHTYPE_PARTIAL_VALUE' => phpBB3_JapaneseSearchModFulltextNativeJa::SEARCHMATCHTYPE_PARTIAL,
				'S_SEARCHMATCHTYPE_FULL_CHECKED' => ($this->search_match_type == phpBB3_JapaneseSearchModFulltextNativeJa::SEARCHMATCHTYPE_FULL) ? true : false,
				'S_SEARCHMATCHTYPE_PARTIAL_CHECKED' => ($this->search_match_type == phpBB3_JapaneseSearchModFulltextNativeJa::SEARCHMATCHTYPE_PARTIAL) ? true : false 
		) );
		
		$num_words = 0;
		if (! $this->keyword) {
			$this->isnot_keyword ( $num_words );
		} else {
			$this->is_keyword ( $num_words );
		}
		
		$template->assign_vars ( array (
				'PAGE_NUMBER' => on_page ( $num_words, self::WORDS_PER_PAGE, $this->start ),
				'PAGINATION' => generate_pagination ( append_sid ( $this->current_page, "keyword=$this->keyword" ), $num_words, self::WORDS_PER_PAGE, $this->start ),
				'TOTAL_WORDS' => $num_words 
		) );
	}
	private function isnot_keyword(&$num_words) {
		global $phpbb_root_path, $phpEx;
		global $db, $user, $template;
		
		$sql = 'SELECT COUNT(word_id) as num_words
			FROM ' . SEARCH_WORDLIST_TABLE;
		$result = $db->sql_query ( $sql );
		$num_words = $db->sql_fetchfield ( 'num_words' );
		$db->sql_freeresult ( $result );
		
		$sql = 'SELECT *
			FROM ' . SEARCH_WORDLIST_TABLE . '
			ORDER BY word_id DESC';
		$result = $db->sql_query_limit ( $sql, self::WORDS_PER_PAGE, $this->start );
		
		$row_count = 0;
		if ($row = $db->sql_fetchrow ( $result )) {
			$template->assign_var ( 'S_SEARCH_WORDLIST_ROWS', true );
			do {
				$template->assign_block_vars ( 'wordrow', array (
						'WORD_ID' => $row ['word_id'],
						'WORD_TEXT' => $row ['word_text'] 
				) );
				
				$row_count ++;
			} while ( $row = $db->sql_fetchrow ( $result ) );
		}
		$db->sql_freeresult ( $result );
	}
	private function is_keyword(&$num_words) {
		global $phpbb_root_path, $phpEx;
		global $db, $user, $template, $config;
		
		if ($this->is_submitted && ! check_form_key ( $this->form_key )) {
			trigger_error ( $user->lang ['FORM_INVALID'] );
		}
		
		$config ['fulltext_native_ja_search_match_type'] = $this->search_match_type;
		
		$starttime = explode ( ' ', microtime () );
		$starttime = $starttime [1] + $starttime [0];
		
		$words = $this->search_index ( array (
				$this->keyword 
		), $config ['fulltext_native_ja_search_match_type'] == phpBB3_JapaneseSearchModFulltextNativeJa::SEARCHMATCHTYPE_PARTIAL, $this->start, self::WORDS_PER_PAGE, $num_words );
		
		$mtime = explode ( ' ', microtime () );
		$totaltime = $mtime [0] + $mtime [1] - $starttime;
		
		$template->assign_vars ( array (
				'SEARCH_TIME' => $totaltime 
		) );
		
		if (sizeof ( $words )) {
			$template->assign_var ( 'S_SEARCH_WORDLIST_ROWS', true );
			foreach ( $words as $word_text => $word_id ) {
				$template->assign_block_vars ( 'wordrow', array (
						'WORD_ID' => $word_id,
						'WORD_TEXT' => $word_text 
				) );
			}
		}
	}
	private function search_index($exact_words, $patial_match = false, $start, $per_page, &$num_words) {
		$num_words = 0;
		$words = array ();
		
		global $db;
		
		if (! $patial_match) {
			$sql = 'SELECT COUNT(word_id) as num_words
					FROM ' . SEARCH_WORDLIST_TABLE . '
					WHERE ' . $db->sql_in_set ( 'word_text', $exact_words );
			$result = $db->sql_query ( $sql );
			$num_words = $db->sql_fetchfield ( 'num_words' );
			$db->sql_freeresult ( $result );
			
			$sql = 'SELECT word_id, word_text, word_common
					FROM ' . SEARCH_WORDLIST_TABLE . '
					WHERE ' . $db->sql_in_set ( 'word_text', $exact_words ) . '
					ORDER BY word_count ASC';
			$result = $db->sql_query_limit ( $sql, $per_page, $start );
			
			// store an array of words and ids, remove common words
			while ( $row = $db->sql_fetchrow ( $result ) ) {
				if ($row ['word_common']) {
					continue;
				}
				$words [$row ['word_text']] = ( int ) $row ['word_id'];
			}
			$db->sql_freeresult ( $result );
		} else {
			$sql_where = ' WHERE ';
			$i = 0;
			do {
				$exact_words [$i] = str_replace ( '\\', '\\\\', $exact_words [$i] );
				$sql_where .= 'word_text ' . $db->sql_like_expression ( $db->any_char . $exact_words [$i] . $db->any_char );
				
				if (! isset ( $exact_words [$i + 1] )) {
					break;
				}
				
				$sql_where .= ' OR ';
				$i += 1;
			} while ( 1 );
			
			$sql = 'SELECT COUNT(word_id) as num_words
					FROM ' . SEARCH_WORDLIST_TABLE . $sql_where;
			$result = $db->sql_query ( $sql );
			$num_words = $db->sql_fetchfield ( 'num_words' );
			
			$sql = 'SELECT word_id, word_text, word_common
					FROM ' . SEARCH_WORDLIST_TABLE . $sql_where . '
					ORDER BY word_count ASC';
			$result = $db->sql_query_limit ( $sql, $per_page, $start );
			
			// store an array of words and ids, remove common words
			while ( $row = $db->sql_fetchrow ( $result ) ) {
				if ($row ['word_common']) {
					continue;
				}
				$words [$row ['word_text']] = ( int ) $row ['word_id'];
			}
			$db->sql_freeresult ( $result );
		}
		
		return $words;
	}
}
