<?php
include_once 'JapaneseIndexer_AL.php';
include_once 'phpBB3Native/UTF8_JapaneseChar.php';

class JapaneseIndexer_phpBB3Native extends JapaneseIndexer_AL
{
	const WAKACHIGAKI_LEVEL_KUGIRI 			= 0;
	const WAKACHIGAKI_LEVEL_HIRAGANA 		= 1;
	const WAKACHIGAKI_LEVEL_ALPHANUMERIC 	= 2;
	const WAKACHIGAKI_LEVEL_KATAKANA 		= 3;
	const WAKACHIGAKI_LEVEL_KANJI 			= 4;
	
	private $wakachigaki_level = self::WAKACHIGAKI_LEVEL_HIRAGANA;

	function __construct()
	{
		@set_time_limit(0);
	}

	function set_wakachigaki_level($level)
	{
		$this->wakachigaki_level = $level;
	}
	
	function wakachigaki($text)
	{
		/**
		 * The first thing we do is:
		 *
		 * - remove some ASCII-7 non-alpha characters "\r", "\n", "\t2" and ","
		 * - remove the bytes that should not appear in a valid UTF-8 string: 0xC0, 0xC1 and 0xF5-0xFF
		 */
		//$sb_match	= "ISTCPAMELRDOJBNHFGVWUQKYXZ\r\n\t!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0C\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\xC0\xC1\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";
		//$sb_replace	= 'istcpamelrdojbnhfgvwuqkyxz                                                                              ';
		$sb_match	= "\r\n\t,\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0B\x0C\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\xC0\xC1\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";
		$sb_replace	= '                                             ';
		$text = strtr($text, $sb_match, $sb_replace);

		$len = strlen($text);

		$ret = '';
		$pos = 0;

		do
		{
			if (!isset($char))
			{
				$char = UTF8_JapaneseChar::get_char($text, $pos, $char_len);
			}
			else
			{
				$char_len = strlen($char);
			}

			if ($pos > $len || !isset($char))
			{
				return $ret;
			}

			if ($char_len == 1) //ASCII
			{
				$string = $char;
				$string .= UTF8_JapaneseChar::get_consecutive_chars('ASCII', $text, $pos, $char);
				$ret .= ($this->wakachigaki_level >= self::WAKACHIGAKI_LEVEL_ALPHANUMERIC)?	' ' . $string . ' ' : $string;
				continue;
			}
			else if ($char_len == 3) // Japanese
			{
				if (UTF8_JapaneseChar::isKugiri($char))
				{
					$ret .= ' ';
					$char = null;
				}
				else if (UTF8_JapaneseChar::isHiragana($char))
				{
					$string = $char;
					$string .= UTF8_JapaneseChar::get_consecutive_chars('Hiragana', $text, $pos, $char);
					$ret .= ($this->wakachigaki_level >= self::WAKACHIGAKI_LEVEL_HIRAGANA)? ' ' . $string . ' ' : $string;
					continue;
				}
				else if (UTF8_JapaneseChar::isKatakana($char))
				{
					$string = $char;
					$string .= UTF8_JapaneseChar::get_consecutive_chars('Katakana', $text, $pos, $char);
					$ret .= ($this->wakachigaki_level >= self::WAKACHIGAKI_LEVEL_KATAKANA)? ' ' . $string . ' ' : $string;
					continue;
				}
				else if (UTF8_JapaneseChar::isKanji($char))
				{
					$string = $char;
					$string .= UTF8_JapaneseChar::get_consecutive_chars('Kanji', $text, $pos, $char);
					$ret .= ($this->wakachigaki_level >= self::WAKACHIGAKI_LEVEL_KANJI)? ' ' . $string . ' ' : $string;
					continue;
				}
				else if (UTF8_JapaneseChar::isZenkakuASCII($char))
				{
					$string = $char;
					$string .= UTF8_JapaneseChar::get_consecutive_chars('ZenkakuASCII', $text, $pos, $char);
					$ret .= ($this->wakachigaki_level >= self::WAKACHIGAKI_LEVEL_ALPHANUMERIC)? ' ' . $string . ' ' : $string;
					continue;
				}
				else if (UTF8_JapaneseChar::isKakko($char))
				{
					$ret .= ' ';
					$char = null;
				}
				else
				{
					$ret .= $char;
					$char = null;
				}
			}
			else if ($char_len == 2 || $char_len == 4) // Latin or Chinese, etc.
			{
				$ret .= $char;
				$char = null;
				continue;
			}
		}
		while (1);

		return $ret;
	}
}