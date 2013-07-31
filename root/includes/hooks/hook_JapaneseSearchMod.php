<?php
/**
 * DO NOT CHANGE!
 */
if(!defined('IN_PHPBB'))
{
	exit;
}

// register hook
if (isset($phpbb_root_path) && $phpbb_root_path == './')
{
	$phpbb_hook->register('phpbb_user_session_handler', array(new phpBB3_hook_JapaneseSearchMod(), 'session'));
}

class phpBB3_hook_JapaneseSearchMod
{
	private $current_page;

	function __construct()
	{
		global $phpEx;

		$this->current_page = basename($_SERVER['SCRIPT_NAME']);
	}

	function session()
	{
		global $phpEx;
		global $user, $config;

		switch($this->current_page)
		{
			case "posting.$phpEx":
				$this->common();
				if ($config['search_type'] == 'fulltext_native_ja')
				{
					phpBB3_JapaneseSearchModMain::assert_setup();
				}
				break;

			case "search.$phpEx" :
			case "viewtopic.$phpEx" :
				$this->common();
				$GLOBALS['phpBB3_JapaneseSearchModHighlight'] = new phpBB3_JapaneseSearchModHighlight();
				break;
			default : 
		}
	}
	
	private function common()
	{
		global $phpbb_root_path;

		include_once($phpbb_root_path . "JapaneseSearchMod/autoload.php");
		phpBB3_JapaneseSearchModMain::load_lang();
	}
}
