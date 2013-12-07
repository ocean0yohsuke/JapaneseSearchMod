<?php
class UTF8_JapaneseChar {
	static function get_char($text, &$pos = 0, &$char_len = null) {
		if (! isset ( $text [$pos] )) {
			return null;
		}
		
		$utf_len_mask = array (
				"\xC0" => 2,
				"\xD0" => 2,
				"\xE0" => 3,
				"\xF0" => 4 
		);
		
		// ３２ビットのマスク演算
		// 右側の１６進数字を 0 にするが、左側の１６進数字は変化なし。
		$masked = ($text [$pos] & "\xF0");
		
		if (! isset ( $utf_len_mask [$masked] )) {
			// ASCII
			$char_len = 1;
			$char = substr ( $text, $pos, 1 );
			$pos += 1;
		} else {
			$char_len = $utf_len_mask [$masked];
			$char = substr ( $text, $pos, $char_len );
			$pos += $char_len;
		}
		return $char;
	}
	static function get_consecutive_chars($mode, $text, &$pos, &$next_char) {
		$string = '';
		$method = 'is' . $mode;
		
		do {
			$char = self::get_char ( $text, $pos );
			if (! isset ( $char ) || ! self::$method ( $char )) {
				break;
			}
			$string .= $char;
		} while ( 1 );
		
		$next_char = $char;
		
		return $string;
	}
	static function get_consecutive_ASCII_chars($text, &$pos) {
		$string = '';
		
		$legal_ascii = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
		
		/**
		 * Do all consecutive ASCII chars at once
		 */
		if ($spn = strspn ( $text, $legal_ascii, $pos )) {
			$string = substr ( $text, $pos, $spn );
			$pos += $spn;
		}
		
		return $string;
	}
	
	/* ------------------------------------------------------------------------------------------------------------------------ */
	const UTF8_KANJI_FIRST = "\xE4\xB8\x80";
	// const UTF8_KANJI_LAST = "\xE9\xBE\xBB";
	const UTF8_KANJI_LAST = "\xE9\xBE\xA5";
	static function isKanji($char) {
		if ($char >= self::UTF8_KANJI_FIRST && $char <= self::UTF8_KANJI_LAST) {
			return true;
		}
		return false;
	}
	const UTF8_HIRAGANA_FIRST = "\xE3\x81\x81";
	const UTF8_HIRAGANA_LAST = "\xE3\x82\x9E";
	static function isHiragana($char) {
		if ($char >= self::UTF8_HIRAGANA_FIRST && $char <= self::UTF8_HIRAGANA_LAST || $char === 'ー') {
			return true;
		}
		return false;
	}
	const UTF8_KATAKANA_FIRST = "\xE3\x82\xA1";
	const UTF8_KATAKANA_LAST = "\xE3\x83\xBE";
	const UTF8_HANKAKUKATAKANA_FIRST = "\xEF\xBD\xA6";
	const UTF8_HANKAKUKATAKANA_LAST = "\xEF\xBE\x9F";
	static function isKatakana($char) {
		if ($char >= self::UTF8_KATAKANA_FIRST && $char <= self::UTF8_KATAKANA_LAST || $char >= self::UTF8_HANKAKUKATAKANA_FIRST && $char <= self::UTF8_HANKAKUKATAKANA_FIRST || $char === '-') {
			return true;
		}
		return false;
	}
	const UTF8_ASCII_FIRST = "\x21";
	const UTF8_ASCII_LAST = "\x7E";
	static function isASCII($char) {
		if ($char >= self::UTF8_ASCII_FIRST && $char <= self::UTF8_ASCII_LAST) {
			return true;
		}
		return false;
	}
	const UTF8_ZENKAKUASCII_FIRST = "\xEF\xBC\x81";
	const UTF8_ZENKAKUASCII_LAST = "\xEF\xBD\x9E";
	static function isZenkakuASCII($char) {
		if ($char >= self::UTF8_ZENKAKUASCII_FIRST && $char <= self::UTF8_ZENKAKUASCII_LAST) {
			return true;
		}
		return false;
	}
	static function isKugiri($char) {
		return in_array ( $char, array (
				'　',
				'　',
				'。',
				'、',
				',',
				'・',
				'．',
				'・',
				'；',
				';',
				'：',
				':',
				"\n",
				"\r",
				"\t" 
		) );
	}
	static function isKakko($char) {
		return in_array ( $char, array (
				'<',
				'>',
				'(',
				')',
				'（',
				'）',
				'[',
				']',
				'{',
				'}',
				'｛',
				'｝',
				'＜',
				'＞',
				'「',
				'」',
				'『',
				'』',
				'〔',
				'〕',
				'〈',
				'〉',
				'《',
				'》',
				'【',
				'】',
				"'",
				'"',
				'`',
				'´',
				'“',
				'”',
				'＂',
				'゛',
				'’',
				'＇' 
		) );
	}
}
