<?php
class Util_JapaneseChar {
	private $pcre_properties = false;
	private $mbstring_regex = false;
	function __construct($indexer_name = null) {
		if (version_compare ( PHP_VERSION, '5.1.0', '>=' ) || (version_compare ( PHP_VERSION, '5.0.0-dev', '<=' ) && version_compare ( PHP_VERSION, '4.4.0', '>=' ))) {
			// While this is the proper range of PHP versions, PHP may not be linked with the bundled PCRE lib and instead with an older version
			if (@preg_match ( '/\p{L}/u', 'あ' ) !== false) {
				$this->pcre_properties = true;
			}
		}
		
		if (function_exists ( 'mb_ereg' )) {
			$this->mbstring_regex = true;
			mb_regex_encoding ( 'UTF-8' );
		}
	}
	
	/**
	 *
	 * @param string $mode        	
	 * @param string $str        	
	 * @throws Util_JapaneseChar_Exception
	 * @return bool
	 */
	function partialMatch($mode, $str) {
		return $this->match ( $mode, $str, false );
	}
	
	/**
	 *
	 * @param string $mode        	
	 * @param string $str        	
	 * @throws Util_JapaneseChar_Exception
	 * @return bool
	 */
	function fullMatch($mode, $str) {
		return $this->match ( $mode, $str, true );
	}
	
	/**
	 *
	 * @param string $mode        	
	 * @param string $str        	
	 * @param bool $full        	
	 * @throws Util_JapaneseChar_Exception
	 */
	private function match($mode, $str, $full = false) {
		if ($str == '') {
			return false;
		}
		
		if ($this->pcre_properties) {
			switch ($mode) {
				case 'ASCII' :
					$pattern = '([\x01-\x7f])+';
					break;
				case 'Alphanumeric' :
					$pattern = '([a-zA-Z0-9_]|[ａ-ｚＡ-Ｚ０-９])+';
					break;
				case 'Hiragana' :
					$pattern = '\p{Hiragana}+';
					break;
				case 'Katakana' :
					$pattern = '([ァ-ヶー]|[ｱ-ﾝﾞﾟｧｨｩｪｫｬｭｮｯ]|-)+';
					break;
				case 'Kanji' :
					$pattern = '\p{Han}+';
					break;
				case 'Kigou' :
					$pattern = '\p{P}+';
					break;
				default :
					throw new Util_JapaneseChar_Exception ( "Specified mode is invalid." );
					break;
			}
		} else {
			switch ($mode) {
				case 'ASCII' :
					$pattern = '([\x21-\x7f])+';
					break;
				case 'Alphanumeric' :
					$pattern = '([a-zA-Z0-9_]|[ａ-ｚＡ-Ｚ０-９])+';
					break;
				case 'Hiragana' :
					$pattern = '[ぁ-ん]+';
					break;
				case 'Katakana' :
					$pattern = '([ァ-ヶー]|[ｱ-ﾝﾞﾟｧｨｩｪｫｬｭｮｯ]|-)+';
					break;
				case 'Kanji' :
					$pattern = '[一-龠]+';
					break;
				case 'Kigou' :
					$shiftkeyJi = '!"#$%&\'()=~|`{+*}<>?_！”＃＄％＆’（）＝～｜‘｛＋＊｝＜＞？＿';
					$shiftkeyHennkanJi = '＂゛“〝〟♯゜´＇「」『』〔〕〈〉《》【】{}()｛｝≠≒￣∥￤|';
					$sonotaJi = ',，.．・／/\；;：:';
					$pattern = '[' . preg_quote ( $shiftkeyJi . $shiftkeyHennkanJi . $sonotaJi ) . ']+';
					break;
				default :
					throw new Util_JapaneseChar_Exception ( "Specified mode is invalid." );
					break;
			}
		}
		
		if ($full) {
			$pattern = '^' . $pattern . '$';
		}
		
		if ($this->pcre_properties) {
			$pattern = '/' . $pattern . '/';
			if ($mode !== 'ASCII') {
				$pattern .= 'u';
			}
			return preg_match ( $pattern, $str );
		} else {
			if ($this->mbstring_regex) {
				return mb_ereg ( $pattern, $str );
			} else {
				$pattern = '/' . $pattern . '/';
				if ($mode !== 'ASCII') {
					$pattern .= 'u';
				}
				return preg_match ( $pattern, $str );
			}
		}
	}
	function convertToZenkaku($text) {
		$text = $this->convertAlphanumericToZenkaku ( $text );
		$text = $this->convertKatakanaToZenkaku ( $text );
		return $text;
	}
	function convertToHankaku($text) {
		$text = $this->convertAlphanumericToHankaku ( $text );
		$text = $this->convertKatakanaToHankaku ( $text );
		return $text;
	}
	function convertAlphanumericToHankaku($str) {
		$replace_of = array (
				'１',
				'２',
				'３',
				'４',
				'５',
				'６',
				'７',
				'８',
				'９',
				'０',
				'Ａ',
				'Ｂ',
				'Ｃ',
				'Ｄ',
				'Ｅ',
				'Ｆ',
				'Ｇ',
				'Ｈ',
				'Ｉ',
				'Ｊ',
				'Ｋ',
				'Ｌ',
				'Ｍ',
				'Ｎ',
				'Ｏ',
				'Ｐ',
				'Ｑ',
				'Ｒ',
				'Ｓ',
				'Ｔ',
				'Ｕ',
				'Ｖ',
				'Ｗ',
				'Ｘ',
				'Ｙ',
				'Ｚ',
				'ａ',
				'ｂ',
				'ｃ',
				'ｄ',
				'ｅ',
				'ｆ',
				'ｇ',
				'ｈ',
				'ｉ',
				'ｊ',
				'ｋ',
				'ｌ',
				'ｍ',
				'ｎ',
				'ｏ',
				'ｐ',
				'ｑ',
				'ｒ',
				'ｓ',
				'ｔ',
				'ｕ',
				'ｖ',
				'ｗ',
				'ｘ',
				'ｙ',
				'ｚ' 
		);
		$replace_by = array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9',
				'0',
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'O',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z',
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z' 
		);
		$result = str_replace ( $replace_of, $replace_by, $str );
		
		return $result;
	}
	function convertAlphanumericToZenkaku($str) {
		$replace_of = array (
				'1',
				'2',
				'3',
				'4',
				'5',
				'6',
				'7',
				'8',
				'9',
				'0',
				'A',
				'B',
				'C',
				'D',
				'E',
				'F',
				'G',
				'H',
				'I',
				'J',
				'K',
				'L',
				'M',
				'N',
				'O',
				'P',
				'Q',
				'R',
				'S',
				'T',
				'U',
				'V',
				'W',
				'X',
				'Y',
				'Z',
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z' 
		);
		$replace_by = array (
				'１',
				'２',
				'３',
				'４',
				'５',
				'６',
				'７',
				'８',
				'９',
				'０',
				'Ａ',
				'Ｂ',
				'Ｃ',
				'Ｄ',
				'Ｅ',
				'Ｆ',
				'Ｇ',
				'Ｈ',
				'Ｉ',
				'Ｊ',
				'Ｋ',
				'Ｌ',
				'Ｍ',
				'Ｎ',
				'Ｏ',
				'Ｐ',
				'Ｑ',
				'Ｒ',
				'Ｓ',
				'Ｔ',
				'Ｕ',
				'Ｖ',
				'Ｗ',
				'Ｘ',
				'Ｙ',
				'Ｚ',
				'ａ',
				'ｂ',
				'ｃ',
				'ｄ',
				'ｅ',
				'ｆ',
				'ｇ',
				'ｈ',
				'ｉ',
				'ｊ',
				'ｋ',
				'ｌ',
				'ｍ',
				'ｎ',
				'ｏ',
				'ｐ',
				'ｑ',
				'ｒ',
				'ｓ',
				'ｔ',
				'ｕ',
				'ｖ',
				'ｗ',
				'ｘ',
				'ｙ',
				'ｚ' 
		);
		$result = str_replace ( $replace_of, $replace_by, $str );
		
		return $result;
	}
	function convertKatakanaToHankaku($str) {
		$replace_of = array (
				'ヴ',
				'ガ',
				'ギ',
				'グ',
				'ゲ',
				'ゴ',
				'ザ',
				'ジ',
				'ズ',
				'ゼ',
				'ゾ',
				'ダ',
				'ヂ',
				'ヅ',
				'デ',
				'ド',
				'バ',
				'ビ',
				'ブ',
				'ベ',
				'ボ',
				'パ',
				'ピ',
				'プ',
				'ペ',
				'ポ' 
		);
		$replace_by = array (
				'ｳﾞ',
				'ｶﾞ',
				'ｷﾞ',
				'ｸﾞ',
				'ｹﾞ',
				'ｺﾞ',
				'ｻﾞ',
				'ｼﾞ',
				'ｽﾞ',
				'ｾﾞ',
				'ｿﾞ',
				'ﾀﾞ',
				'ﾁﾞ',
				'ﾂﾞ',
				'ﾃﾞ',
				'ﾄﾞ',
				'ﾊﾞ',
				'ﾋﾞ',
				'ﾌﾞ',
				'ﾍﾞ',
				'ﾎﾞ',
				'ﾊﾟ',
				'ﾋﾟ',
				'ﾌﾟ',
				'ﾍﾟ',
				'ﾎﾟ' 
		);
		$result = str_replace ( $replace_of, $replace_by, $str );
		
		$replace_of = array (
				'ア',
				'イ',
				'ウ',
				'エ',
				'オ',
				'カ',
				'キ',
				'ク',
				'ケ',
				'コ',
				'サ',
				'シ',
				'ス',
				'セ',
				'ソ',
				'タ',
				'チ',
				'ツ',
				'テ',
				'ト',
				'ナ',
				'ニ',
				'ヌ',
				'ネ',
				'ノ',
				'ハ',
				'ヒ',
				'フ',
				'ヘ',
				'ホ',
				'マ',
				'ミ',
				'ム',
				'メ',
				'モ',
				'ヤ',
				'ユ',
				'ヨ',
				'ラ',
				'リ',
				'ル',
				'レ',
				'ロ',
				'ワ',
				'ヲ',
				'ン',
				'ァ',
				'ィ',
				'ゥ',
				'ェ',
				'ォ',
				'ャ',
				'ュ',
				'ョ',
				'ッ',
				'ー' 
		);
		$replace_by = array (
				'ｱ',
				'ｲ',
				'ｳ',
				'ｴ',
				'ｵ',
				'ｶ',
				'ｷ',
				'ｸ',
				'ｹ',
				'ｺ',
				'ｻ',
				'ｼ',
				'ｽ',
				'ｾ',
				'ｿ',
				'ﾀ',
				'ﾁ',
				'ﾂ',
				'ﾃ',
				'ﾄ',
				'ﾅ',
				'ﾆ',
				'ﾇ',
				'ﾈ',
				'ﾉ',
				'ﾊ',
				'ﾋ',
				'ﾌ',
				'ﾍ',
				'ﾎ',
				'ﾏ',
				'ﾐ',
				'ﾑ',
				'ﾒ',
				'ﾓ',
				'ﾔ',
				'ﾕ',
				'ﾖ',
				'ﾗ',
				'ﾘ',
				'ﾙ',
				'ﾚ',
				'ﾛ',
				'ﾜ',
				'ｦ',
				'ﾝ',
				'ｧ',
				'ｨ',
				'ｩ',
				'ｪ',
				'ｫ',
				'ｬ',
				'ｭ',
				'ｮ',
				'ｯ',
				'ｰ' 
		);
		$result = str_replace ( $replace_of, $replace_by, $result );
		
		return $result;
	}
	function convertKatakanaToZenkaku($str) {
		$replace_of = array (
				'ｳﾞ',
				'ｶﾞ',
				'ｷﾞ',
				'ｸﾞ',
				'ｹﾞ',
				'ｺﾞ',
				'ｻﾞ',
				'ｼﾞ',
				'ｽﾞ',
				'ｾﾞ',
				'ｿﾞ',
				'ﾀﾞ',
				'ﾁﾞ',
				'ﾂﾞ',
				'ﾃﾞ',
				'ﾄﾞ',
				'ﾊﾞ',
				'ﾋﾞ',
				'ﾌﾞ',
				'ﾍﾞ',
				'ﾎﾞ',
				'ﾊﾟ',
				'ﾋﾟ',
				'ﾌﾟ',
				'ﾍﾟ',
				'ﾎﾟ' 
		);
		$replace_by = array (
				'ヴ',
				'ガ',
				'ギ',
				'グ',
				'ゲ',
				'ゴ',
				'ザ',
				'ジ',
				'ズ',
				'ゼ',
				'ゾ',
				'ダ',
				'ヂ',
				'ヅ',
				'デ',
				'ド',
				'バ',
				'ビ',
				'ブ',
				'ベ',
				'ボ',
				'パ',
				'ピ',
				'プ',
				'ペ',
				'ポ' 
		);
		$result = str_replace ( $replace_of, $replace_by, $str );
		
		$replace_of = array (
				'ｱ',
				'ｲ',
				'ｳ',
				'ｴ',
				'ｵ',
				'ｶ',
				'ｷ',
				'ｸ',
				'ｹ',
				'ｺ',
				'ｻ',
				'ｼ',
				'ｽ',
				'ｾ',
				'ｿ',
				'ﾀ',
				'ﾁ',
				'ﾂ',
				'ﾃ',
				'ﾄ',
				'ﾅ',
				'ﾆ',
				'ﾇ',
				'ﾈ',
				'ﾉ',
				'ﾊ',
				'ﾋ',
				'ﾌ',
				'ﾍ',
				'ﾎ',
				'ﾏ',
				'ﾐ',
				'ﾑ',
				'ﾒ',
				'ﾓ',
				'ﾔ',
				'ﾕ',
				'ﾖ',
				'ﾗ',
				'ﾘ',
				'ﾙ',
				'ﾚ',
				'ﾛ',
				'ﾜ',
				'ｦ',
				'ﾝ',
				'ｧ',
				'ｨ',
				'ｩ',
				'ｪ',
				'ｫ',
				'ｬ',
				'ｭ',
				'ｮ',
				'ｯ',
				'ｰ',
				'ﾞ',
				'ﾟ' 
		);
		$replace_by = array (
				'ア',
				'イ',
				'ウ',
				'エ',
				'オ',
				'カ',
				'キ',
				'ク',
				'ケ',
				'コ',
				'サ',
				'シ',
				'ス',
				'セ',
				'ソ',
				'タ',
				'チ',
				'ツ',
				'テ',
				'ト',
				'ナ',
				'ニ',
				'ヌ',
				'ネ',
				'ノ',
				'ハ',
				'ヒ',
				'フ',
				'ヘ',
				'ホ',
				'マ',
				'ミ',
				'ム',
				'メ',
				'モ',
				'ヤ',
				'ユ',
				'ヨ',
				'ラ',
				'リ',
				'ル',
				'レ',
				'ロ',
				'ワ',
				'ヲ',
				'ン',
				'ァ',
				'ィ',
				'ゥ',
				'ェ',
				'ォ',
				'ャ',
				'ュ',
				'ョ',
				'ッ',
				'ー',
				'',
				'' 
		);
		$result = str_replace ( $replace_of, $replace_by, $result );
		
		return $result;
	}
}
class Util_JapaneseChar_Exception extends Exception {
	function getException() {
		if (isset ( $this->xdebug_message ) && $this->xdebug_message) {
			die ( '<table>' . $this->xdebug_message . '</table>' );
		} else {
			$message = '[Util_JapaneseChar Error] ';
			$message .= $this->getMessage ();
			die ( $message );
		}
	}
}