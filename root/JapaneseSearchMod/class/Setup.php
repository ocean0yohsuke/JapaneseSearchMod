<?php
class phpBB3_JapaneseSearchModSetup {
	const DEFAULTCONFIG_PHPBB3NATIVE_WAKACHIGAKI_LEVEL = 1;
	const DEFAULTCONFIG_MECAB_WAKACHIGAKI_LEVEL = 2;
	const DEFAULTCONFIG_MECAB_RENZOKU_HINSHI = 1;
	const DEFAULTCONFIG_MECAB_ONLY_MEISHI = 0;
	const DEFAULTCONFIG_FULLTEXTNATIVEJA_INDEX_ENGINE = 'phpBB3Native';
	const DEFAULTCONFIG_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE = phpBB3_JapaneseSearchModFulltextNativeJa::SEARCHMATCHTYPE_PARTIAL;
	const DEFAULTCONFIG_FULLTEXTNATIVEJA_CANONICAL_TRANSFORMATION = 0;
	const DEFAULTCONFIG_FULLTEXTNATIVEJA_LOAD_UPD = 1;
	const DEFAULTCONFIG_FULLTEXTNATIVEJA_MIN_CHARS = 2;
	const DEFAULTCONFIG_FULLTEXTNATIVEJA_MAX_CHARS = 50;
	const DEFAULTCONFIG_FULLTEXTNATIVEJA_COMMON_THRES = 0;
	private $messages = array ();
	function __construct() {
	}
	function setup() {
		$this->validate_config ();
		$this->garbage_collection ();
	}
	function unsetup() {
		$this->delete_config ();
		$this->garbage_collection ();
	}
	function getMessages() {
		return $this->messages;
	}
	private function addMessage($message) {
		$this->messages [] = $message;
	}
	private function validate_config() {
		global $user, $db, $config;
		
		$default_config = array (
				'JapaneseSearchMod_version' => phpBB3_JapaneseSearchModMain::VERSION,
				'JapaneseSearchMod_phpBB3Native_wakachigaki_level' => self::DEFAULTCONFIG_PHPBB3NATIVE_WAKACHIGAKI_LEVEL,
				'JapaneseSearchMod_MeCab_wakachigaki_level' => self::DEFAULTCONFIG_MECAB_WAKACHIGAKI_LEVEL,
				'JapaneseSearchMod_MeCab_renzoku_hinshi' => self::DEFAULTCONFIG_MECAB_RENZOKU_HINSHI,
				'JapaneseSearchMod_MeCab_only_meishi' => self::DEFAULTCONFIG_MECAB_ONLY_MEISHI,
				'JapaneseSearchMod_MeCab_Ext_encoding' => '',
				'JapaneseSearchMod_MeCab_Ext_dicdir' => '',
				'JapaneseSearchMod_MeCab_CLI_encoding' => '',
				'JapaneseSearchMod_MeCab_CLI_exepath' => '',
				'fulltext_native_ja_index_engine' => self::DEFAULTCONFIG_FULLTEXTNATIVEJA_INDEX_ENGINE,
				'fulltext_native_ja_search_match_type' => self::DEFAULTCONFIG_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE,
				'fulltext_native_ja_canonical_transformation' => self::DEFAULTCONFIG_FULLTEXTNATIVEJA_CANONICAL_TRANSFORMATION,
				'fulltext_native_ja_load_upd' => self::DEFAULTCONFIG_FULLTEXTNATIVEJA_LOAD_UPD,
				'fulltext_native_ja_min_chars' => self::DEFAULTCONFIG_FULLTEXTNATIVEJA_MIN_CHARS,
				'fulltext_native_ja_max_chars' => self::DEFAULTCONFIG_FULLTEXTNATIVEJA_MAX_CHARS,
				'fulltext_native_ja_common_thres' => self::DEFAULTCONFIG_FULLTEXTNATIVEJA_COMMON_THRES 
		);
		
		// garbage collection
		foreach ( $config as $config_name => $config_var ) {
			if (isset ( $default_config [$config_name] )) {
				continue;
			}
			
			if (preg_match ( '/^(JapaneseSearchMod_|fulltext_native_ja_)/i', $config_name )) {
				$sql = 'DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = '" . $db->sql_escape ( $config_name ) . "'";
				$db->sql_query ( $sql );
				$this->addMessage ( sprintf ( $user->lang ['JSM_SETUPPANEL_CONFIGKEY_DELETED'], $config_name ) );
			}
		}
		
		//
		// set config
		//
		if (isset ( $config ['JapaneseSearchMod_version'] )) {
			$message = sprintf ( $user->lang ['JSM_SETUPPANEL_CONFIGKEY_UPDATED'], 'JapaneseSearchMod_version', phpBB3_JapaneseSearchModMain::VERSION );
		} else {
			$message = sprintf ( $user->lang ['JSM_SETUPPANEL_CONFIGKEY_ADDED'], 'JapaneseSearchMod_version' );
		}
		set_config ( 'JapaneseSearchMod_version', $default_config ['JapaneseSearchMod_version'] );
		$this->addMessage ( $message );
		foreach ( $default_config as $config_name => $config_var ) {
			if (isset ( $config [$config_name] )) {
				continue;
			}
			set_config ( $config_name, $config_var );
			$this->addMessage ( sprintf ( $user->lang ['JSM_SETUPPANEL_CONFIGKEY_ADDED'], $config_name ) );
		}
		
		$this->validate_indexer ();
	}
	private function garbage_collection() {
		global $phpbb_root_path;
		global $cache;
		
		$cache->destroy ( 'config' );
		
		// for JapaneseSearchMod 2.2.x
		$cache->remove_file ( $phpbb_root_path . 'cache/JapaneseSearchMod_adm_panel.php', true );
		
		phpBB3_JapaneseSearchModMain::cache_purge ();
	}
	private function validate_indexer() {
		global $config;
		
		// もしコンフィグで指定されているインデクサが当バージョンで使えない場合、強制的にデフォルトインデクサをコンフィグに指定する
		{
			$indexer_list = phpBB3_JapaneseSearchModFulltextNativeJa::get_indexer_list ();
			if (isset ( $config ['fulltext_native_ja_index_engine'] ) && ! in_array ( $config ['fulltext_native_ja_index_engine'], $indexer_list )) {
				$old_index_engine = $config ['fulltext_native_ja_index_engine'];
				set_config ( 'fulltext_native_ja_index_engine', self::DEFAULTCONFIG_FULLTEXTNATIVEJA_INDEX_ENGINE );
				$this->addMessage ( sprintf ( $user->lang ['JSM_SETUPPANEL_INDEXER_ALTERED'], $old_index_engine, self::DEFAULTCONFIG_FULLTEXTNATIVEJA_INDEX_ENGINE ) );
			}
		}
	}
	private function delete_config() {
		global $user, $db, $config, $cache;
		
		foreach ( $config as $config_name => $config_var ) {
			if (preg_match ( '/^JapaneseSearchMod_|fulltext_native_ja_/', $config_name )) {
				$sql = 'DELETE FROM ' . CONFIG_TABLE . " WHERE config_name = '" . $db->sql_escape ( $config_name ) . "'";
				$db->sql_query ( $sql );
				set_config ( $config_name, null );
				$this->addMessage ( sprintf ( $user->lang ['JSM_SETUPPANEL_CONFIGKEY_DELETED'], $config_name ) );
			}
		}
	}
}
