<?php

class phpBB3_JapaneseSearchModFulltextNativeJa
{
	const ABSTRACTION_LAYER_INDEXER_FILE = 'JapaneseIndexer_AL.php';

	const SEARCHMATCHTYPE_FULL 		= 1;
	const SEARCHMATCHTYPE_PARTIAL 	= 0;

	function __construct()
	{
	}

	static function get_indexer_list()
	{
		$indexer_list = array();

		if ($dp = @opendir(PHPBB_JAPANESESEARCHMOD_ROOT_PATH . '/JapaneseIndexer'))
		{
			while (($file = readdir($dp)) !== false)
			{
				if ((preg_match('#\.php$#', $file)) && ($file != self::ABSTRACTION_LAYER_INDEXER_FILE))
				{
					$indexer = preg_replace('#^(.*?)\.php$#', '\1', $file);
					if(self::indexerIsValid($indexer))
					{
						$indexer_list[] = $indexer;
					}
				}
			}
			closedir($dp);
		}

		return $indexer_list;
	}

	static private function indexerIsValid($engine)
	{
		global $user;

		if (!preg_match('#^\w+$#', $engine) || !file_exists(PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "/JapaneseIndexer/{$engine}.php"))
		{
			return false;
		}

		include_once(PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "/JapaneseIndexer/{$engine}.php");

		$class_name = 'JapaneseIndexer_' . $engine;
		if (!class_exists($class_name))
		{
			return false;
		}

		try {
			if ($engine != 'MeCab')
			{
				$class_obj = new $class_name();
			}
			else
			{
				$DataStructure = new JapaneseIndexer_MeCabData();
				$MeCab_config = phpBB3_JapaneseSearchModMain::MeCab_get_config();
				$DataStructure->set_Ext($MeCab_config['Ext']['encoding'], $MeCab_config['Ext']['dicdir']);
				$DataStructure->set_CLI($MeCab_config['CLI']['encoding'], $MeCab_config['CLI']['exepath']);
				$class_obj = new JapaneseIndexer_MeCab($DataStructure);
			}
		}
		catch (JapaneseIndexerException $e)
		{
			return false;
		}
		catch (phpBB3_JapaneseSearchModException $e)
		{
			return false;
		}
		
		if(!method_exists($class_obj, 'wakachigaki'))
		{
			return false;
		}
		if(method_exists($class_obj, 'isAbleToWakachigaki'))
		{
			if(!$class_obj->isAbleToWakachigaki())
			{
				return false;
			}
		}

		return true;
	}
}
