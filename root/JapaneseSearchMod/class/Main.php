<?php

class phpBB3_JapaneseSearchModMain
{
	const VERSION = '2.3.4';

	private $indexer_name = '';

	private $Indexer; // object

	function __construct($indexer_name = null)
	{
		self::assert_setup();

		$this->set_indexer($indexer_name);
	}

	/**
	 * @param string $indexer_name
	 */
	private function set_indexer($indexer_name=null)
	{
		global $config;

		try {
			$this->indexer_name = (!isset($indexer_name))? $config['fulltext_native_ja_index_engine'] : $indexer_name;

			$indexer_className = 'JapaneseIndexer_' . $this->indexer_name;

			$path = PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "JapaneseIndexer/{$this->indexer_name}.php";
			if (defined('DEBUG') && !file_exists($path))
			{
				throw new phpBB3_JapaneseSearchModException("Could not find file '{$path}'.");
			}
			include_once($path);
			if (defined('DEBUG') && !class_exists($indexer_className))
			{
				throw new phpBB3_JapaneseSearchModException("Could not find class '{$class}'.");
			}

			switch($this->indexer_name)
			{
				case 'MeCab' :
					$DataStructure = new JapaneseIndexer_MeCabData();
					$MeCab_config = self::MeCab_get_config();
					$DataStructure->set_Ext($MeCab_config['Ext']['encoding'], $MeCab_config['Ext']['dicdir']);
					$DataStructure->set_CLI($MeCab_config['CLI']['encoding'], $MeCab_config['CLI']['exepath']);
					$DataStructure->set_wakachigaki_level($config['JapaneseSearchMod_MeCab_wakachigaki_level']);
					$DataStructure->set_renzoku_hinshi($config['JapaneseSearchMod_MeCab_renzoku_hinshi']);
					$DataStructure->set_only_meishi($config['JapaneseSearchMod_MeCab_only_meishi']);
					$this->Indexer = new JapaneseIndexer_MeCab($DataStructure);
					break;

				case 'phpBB3Native' :
					$this->Indexer = new JapaneseIndexer_phpBB3Native();
					$this->Indexer->set_wakachigaki_level($config['JapaneseSearchMod_phpBB3Native_wakachigaki_level']);
					break;

				default :
					$this->Indexer = new $indexer_className();
			}
		}
		catch (phpBB3_JapaneseSearchModException $e)
		{
			$e->getException();
		}
		catch (JapaneseIndexerException $e)
		{
			$message = '[JapaneseSearchMod Error] ';
			$message .= $user->lang['JSM_' . $e->getMessage()];
			if (defined('DEBUG'))
			{
				$message .= '<br /><br />';
				$message .= 'This error was thrown';
				$message .= ' in file ' . $e->getFile();
				$message .= ' on line ' . $e->getLine();
				$message .= '<br />';
				$message .= "\n";
			}
			trigger_error($message);
		}
	}

	function get_indexer_name()
	{
		return $this->indexer_name;
	}

	/**
	 * @param string $text
	 */
	function wakachigaki($text)
	{
		try {
			$text = $this->Indexer->wakachigaki($text);
			return $text;
		}
		catch (JapaneseIndexerException $e)
		{
			global $user;
				
			$message = '[JapaneseSearchMod Error] ';
			$message .= $user->lang['JSM_' . $e->getMessage()];
			if (defined('DEBUG'))
			{
				$message .= '<br /><br />';
				$message .= 'This error was thrown';
				$message .= ' in file ' . $e->getFile();
				$message .= ' on line ' . $e->getLine();
				$message .= '<br />';
				$message .= "\n";
			}
			trigger_error($message);
		}
	}

	static function load_lang()
	{
		global $user, $config;

		$lang = array();
		$lang_postfix = array();
		
		// $lang
		if (is_file(PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "language/{$user->lang_name}.php"))
		{
			include PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "language/{$user->lang_name}.php";
		}
		elseif (is_file(PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "language/en.php}"))
		{
			include PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "language/en.php}";
		}
		$user->lang += $lang;

		// $lang_postfix
		if (isset($config['fulltext_native_ja_canonical_transformation']) && !$config['fulltext_native_ja_canonical_transformation'])
		{
			if (isset($user->lang['SEARCH_KEYWORDS_EXPLAIN']) && isset($lang_postfix['SEARCH_KEYWORDS_EXPLAIN']))
			{
				$user->lang['SEARCH_KEYWORDS_EXPLAIN'] .= $lang_postfix['SEARCH_KEYWORDS_EXPLAIN'];
			}
		}
	}

	static function assert_setup()
	{
		global $user, $config;

		if (!isset($config['JapaneseSearchMod_version']) || ($config['JapaneseSearchMod_version'] != self::VERSION))
		{
			trigger_error(sprintf($user->lang['JSM_NOT_SETUP_YET'], self::VERSION));
		}
	}

	static function MeCab_get_config()
	{
		global $config;

		return array(
			'Ext' => array(
				'encoding'	=> $config['JapaneseSearchMod_MeCab_Ext_encoding'],
				'dicdir'	=> $config['JapaneseSearchMod_MeCab_Ext_dicdir'],
			),
			'CLI' => array(
				'encoding'	=> $config['JapaneseSearchMod_MeCab_CLI_encoding'],
				'exepath'	=> $config['JapaneseSearchMod_MeCab_CLI_exepath'],
			),
		);
	}

	/**
	 *
	 * @param array $config
	 */
	static function MeCab_set_config($config = null)
	{
		if (!isset($config))
		{
			try {
				$config = JapaneseIndexer_MeCab::autofind_config();
			}
			catch (JapaneseIndexerException $e)
			{
				throw new phpBB3_JapaneseSearchModException('JSM_' . $e->getMessage());
			}
		}

		set_config('JapaneseSearchMod_MeCab_Ext_encoding', $config['Ext']['encoding']);
		set_config('JapaneseSearchMod_MeCab_Ext_dicdir', $config['Ext']['dicdir']);
		set_config('JapaneseSearchMod_MeCab_CLI_encoding', $config['CLI']['encoding']);
		set_config('JapaneseSearchMod_MeCab_CLI_exepath', $config['CLI']['exepath']);
	}

	static function cache_purge()
	{
		global $phpbb_root_path;
		global $cache;

		$cache->destroy('_JSM_admpnl_MFA');

		$dirname = $phpbb_root_path . 'cache';
		$iterator = new DirectoryIterator($phpbb_root_path . 'cache');
		foreach ($iterator as $fileinfo)
		{
			if ($fileinfo->isFile())
			{
				$filename = $fileinfo->getFilename();
				if (preg_match('#^(data_JSM_|data_search_results_)#i', $filename))
				{
					$cache->remove_file($dirname . '/' . $filename);
				}
			}
		}
	}
}