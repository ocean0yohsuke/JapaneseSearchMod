<?php
/**
 * Abstraction Layer of JapaneseIndexer
 */
class JapaneseIndexer_AL
{
	function __construct()
	{
		//setlocale(LC_CTYPE, 'ja_JP.UTF-8');

		//@set_time_limit(0);
	}

	/**
	 * [ MUST BE OVERRIDDEN ]
	 * Execute wakachigaki.
	 * @param string $text Must be Japanese sentences.
	 * @return string UTF-8 sentences
	 */
	public function wakachigaki($text)
	{
		//
		// 分かち書き
		//

		return $text;
	}

	/**
	 * [ OPTIONAL ]
	 * @return bool
	 */
	public function isAbleToWakachigaki()
	{
		//
		// 分かち書きテスト
		//

		return true;
	}
}

class JapaneseIndexerUtil
{
	/**
	 * Load a PHP extension.
	 * @param string $extension PHP extension, eg. 'mbstring', 'mecab'
	 * @return bool
	 */
	static function loadExtension($extension)
	{
		if (@extension_loaded($extension))
		{
			return true;
		}

		if((!@ini_get('enable_dl') && strtolower(@ini_get('enable_dl')) != 'on') ||
		(@ini_get('safe_mode') && strtolower(@ini_get('safe_mode')) != 'off') ||
		!function_exists('dl'))
		{
			return false;
		}

		if (defined('PHP_SHLIB_SUFFIX'))
		{
			$suffix = PHP_SHLIB_SUFFIX;
		}
		else
		{
			$suffix = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')? 'dll' : 'so';
		}

		$prefix = ($suffix === 'dll')? 'php_' : '';
		$libraries = array_unique(array(
				"{$prefix}{$extension}.{$suffix}",
				"{$extension}.so",
				"php_{$extension}.dll"
		));

		foreach ($libraries as $library)
		{
			if (@dl($library))
			{
				return true;
			}
		}

		return false;
	}

}

class JapaneseIndexerException extends Exception
{

}