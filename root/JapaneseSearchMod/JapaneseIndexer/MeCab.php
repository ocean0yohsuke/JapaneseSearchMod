<?php
include_once 'JapaneseIndexer_AL.php';

/**
 * JapaneseIndexer_MeCab is an indexer class for Japanese, using MeCab as engine for wakachigaki.
 * ( MeCab : Yet Another Part-of-Speech and Morphological Analyzer )
 *
 * Error message thrown by any exception would be one of the following strings :
 * MECAB_ERROR_CONFIG_ENCODING_EMPTY
 * MECAB_ERROR_MBSTRING_UNSUPPORTED
 * MECAB_ERROR_NOT_AVAILABLE_BOTH_MODE
 * MECAB_ERROR_COULDNOT_FIND_CONFIG_SETTINGS
 * MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_EXT
 * MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_CLI
 * MECAB_ERROR_WAKACHIGAKI_FAILED_BY_WRONG_ENCODING
 * MECAB_ERROR_WAKACHIGAKI_FAILED_BY_STDERR
 * MECAB_ERROR_EXTENSION_MECAB_UNSUPPORTED
 * MECAB_ERROR_MECABFUNC_MECABNEW_UNSUPPORTED
 * MECAB_ERROR_FUNC_PROCOPEN_UNSUPPORTED
 */
class JapaneseIndexer_MeCabData {
	const WAKACHIGAKI_LEVEL_KIGOU = 0;
	const WAKACHIGAKI_LEVEL_MEISHI = 1;
	const WAKACHIGAKI_LEVEL_JOSHI = 2;
	const WAKACHIGAKI_LEVEL_ALL = 3;
	private $wakachigaki_level = self::WAKACHIGAKI_LEVEL_ALL;
	private $renzoku_hinshi = true;
	private $only_meishi = false;
	private $Ext = array (
			'encoding' => '',
			'dicdir' => '' 
	);
	private $CLI = array (
			'encoding' => '',
			'exepath' => '' 
	);
	function set_Ext($encoding, $dicdir) {
		$this->Ext ['encoding'] = $encoding;
		$this->Ext ['dicdir'] = $dicdir;
	}
	function get_Ext() {
		return $this->Ext;
	}
	function set_CLI($encoding, $exepath) {
		$this->CLI ['encoding'] = $encoding;
		$this->CLI ['exepath'] = $exepath;
	}
	function get_CLI() {
		return $this->CLI;
	}
	function set_wakachigaki_level($level) {
		$this->wakachigaki_level = $level;
	}
	function get_wakachigaki_level() {
		return $this->wakachigaki_level;
	}
	function set_only_meishi($bool) {
		$this->only_meishi = $bool;
	}
	function get_only_meishi() {
		return $this->only_meishi;
	}
	function set_renzoku_hinshi($bool) {
		$this->renzoku_hinshi = $bool;
	}
	function get_renzoku_hinshi() {
		return $this->renzoku_hinshi;
	}
}
class JapaneseIndexer_MeCab extends JapaneseIndexer_AL {
	private $mode; // 'Ext' or 'CLI'
	private $engine;
	private $DataStructure;
	
	/**
	 *
	 * @param JapaneseIndexer_MeCabData $DataStructure        	
	 * @param string $mode
	 *        	'Ext' or 'CLI' or null(autofind)
	 */
	function __construct(JapaneseIndexer_MeCabData $DataStructure, $mode = null) {
		@set_time_limit ( 0 );
		
		$this->DataStructure = $DataStructure;
		
		$this->set_mode ( $mode );
		$this->set_engine ();
	}
	
	/**
	 *
	 * @param string $text        	
	 * @throws JapaneseIndexerException
	 */
	function wakachigaki($text) {
		$words = array ();
		
		if ($this->DataStructure->get_renzoku_hinshi ()) {
			$text = strtr ( $text, "\r\n\t", '   ' );
			$text = preg_replace ( '#\s+#u', ' ', $text );
			$strings = explode ( ' ', $text );
		} else {
			$text = str_replace ( array (
					"\r\n",
					"\r" 
			), "\n", $text );
			$strings = explode ( "\n", $text );
		}
		
		foreach ( $strings as $string ) {
			$string = trim ( $string );
			if (empty ( $string )) {
				continue;
			}
			$words [] = $this->engine->wakachigaki ( $string );
		}
		
		$words = implode ( ' ', $words );
		
		return $words;
	}
	function isAbleToWakachigaki() {
		try {
			switch ($this->mode) {
				case 'Ext' :
					$config = $this->DataStructure->get_Ext ();
					break;
				case 'CLI' :
					$config = $this->DataStructure->get_CLI ();
					break;
			}
			
			self::test_wakachigaki ( $this->mode, $config );
		} catch ( JapaneseIndexerException $e ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 *
	 * @param string $mode        	
	 * @param string $config        	
	 * @throws JapaneseIndexerException
	 */
	static function test_wakachigaki($mode, $config) {
		switch ($mode) {
			case 'Ext' :
				JapaneseIndexer_MeCabExt::test_wakachigaki ( $config ['encoding'], $config ['dicdir'] );
				break;
			
			case 'CLI' :
				JapaneseIndexer_MeCabCLI::test_wakachigaki ( $config ['encoding'], $config ['exepath'] );
				break;
		}
	}
	function get_mode() {
		return $this->mode;
	}
	private function set_mode($mode = null) {
		if (isset ( $mode )) {
			$this->mode = $mode;
		} else {
			if (JapaneseIndexerUtil::loadExtension ( 'mecab' ) && is_callable ( 'mecab_new' )) {
				$this->mode = 'Ext';
			} else {
				if (is_callable ( 'proc_open' )) {
					$this->mode = 'CLI';
				}
			}
			if (! isset ( $this->mode )) {
				throw new JapaneseIndexerException ( 'MECAB_ERROR_NOT_AVAILABLE_BOTH_MODE' );
			}
		}
	}
	private function set_engine() {
		$MeCab_class = 'JapaneseIndexer_MeCab' . $this->mode;
		$this->engine = new $MeCab_class ( $this->DataStructure );
	}
	
	/**
	 *
	 * @throws JapaneseIndexerException
	 */
	static function autofind_config() {
		$encodings = array (
				'utf-8',
				'euc-jp',
				'shift-jis' 
		);
		
		$dicdirs = array (
				'/usr/local/lib/mecab/dic/ipadic',
				'/usr/bin/mecab/dic/ipadic',
				'/usr/sbin/mecab/dic/ipadic',
				'/usr/local/bin/mecab/dic/ipadic',
				'/usr/local/sbin/mecab/dic/ipadic',
				'/opt/mecab/dic/ipadic',
				'/usr/mecab/dic/ipadic',
				'C:/WINDOWS/MeCab/dic/ipadic',
				'C:/WINNT/MeCab/dic/ipadic',
				'C:/WINDOWS/SYSTEM/MeCab/dic/ipadic',
				'C:/WINNT/SYSTEM/MeCab/dic/ipadic',
				'C:/WINDOWS/SYSTEM32/MeCab/dic/ipadic',
				'C:/WINNT/SYSTEM32/MeCab/dic/ipadic' 
		);
		
		$config ['Ext'] = JapaneseIndexer_MeCabExt::autofind_config ( $encodings, $dicdirs );
		
		$config ['CLI'] = JapaneseIndexer_MeCabCLI::autofind_config ( $encodings );
		
		return $config;
	}
}
class JapaneseIndexer_MeCabExt extends JapaneseIndexer_MeCabBase {
	private $config;
	private $engine;
	
	/**
	 *
	 * @throws JapaneseIndexerException
	 */
	protected function boot() {
		$this->set_config ();
		$this->set_engine ();
	}
	
	/**
	 *
	 * @author tEnd, ocean=Yohsuke
	 */
	function wakachigaki($text) {
		$encoding = strtolower ( $this->config ['encoding'] );
		$encoding_isnt_utf8 = ($encoding === 'utf-8') ? false : true;
		
		if ($encoding_isnt_utf8) {
			$text = @mb_convert_encoding ( $text, $encoding, 'utf-8' );
		}
		
		$text_pieces = array ();
		$node = mecab_sparse_tonode ( $this->engine, $text );
		while ( $node ) {
			$stat = mecab_node_stat ( $node );
			if ($stat == 2 || $stat == 3) {
				continue;
			}
			
			$surface = mecab_node_surface ( $node );
			$feature = mecab_node_feature ( $node );
			
			$feature = explode ( ',', $feature );
			$hinshi = $feature [0];
			
			if ($encoding_isnt_utf8) {
				$surface = @mb_convert_encoding ( $surface, 'utf-8', $encoding );
				$hinshi = @mb_convert_encoding ( $hinshi, 'utf-8', $encoding );
			}
			
			$text_pieces [] = array (
					'surface' => $surface,
					'hinshi' => $hinshi 
			);
			
			$node = mecab_node_next ( $node );
		}
		
		$wakachigaki = $this->assemble_wakachigaki ( $text_pieces );
		
		return $wakachigaki;
	}
	
	/**
	 *
	 * @param string $encoding        	
	 * @param string $dicdir        	
	 * @throws JapaneseIndexerException
	 */
	static function test_wakachigaki($encoding, $dicdir) {
		// requirement
		if (! JapaneseIndexerUtil::loadExtension ( 'mecab' )) {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_EXTENSION_MECAB_UNSUPPORTED' );
		}
		if (! is_callable ( 'mecab_new' )) {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_MECABFUNC_MECABNEW_UNSUPPORTED' );
		}
		if (strtolower ( $encoding ) != 'utf-8') {
			if (! JapaneseIndexerUtil::loadExtension ( 'mbstring' )) {
				throw new JapaneseIndexerException ( 'MECAB_ERROR_MBSTRING_UNSUPPORTED' );
			}
		}
		
		// test wakachigaki
		if ($dicdir != '') {
			$engine = mecab_new ( array (
					'-d',
					$dicdir 
			) );
		} else {
			$engine = mecab_new ();
		}
		if (! is_resource ( $engine )) {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_EXT' );
		}
		
		$input_word = '愛';
		$surface = '';
		$hinshi = '';
		
		if (strtolower ( $encoding ) != 'utf-8') {
			$input_word = @mb_convert_encoding ( $input_word, $encoding, 'utf-8' );
		}
		
		$node = mecab_sparse_tonode ( $engine, $input_word );
		$stat = mecab_node_stat ( $node );
		$surface = mecab_node_surface ( $node );
		$feature = mecab_node_feature ( $node );
		mecab_destroy ( $engine );
		
		$feature = explode ( ',', $feature );
		$hinshi = $feature [0];
		
		if (strtolower ( $encoding ) != 'utf-8') {
			$surface = @mb_convert_encoding ( $surface, 'utf-8', $encoding );
			$hinshi = @mb_convert_encoding ( $hinshi, 'utf-8', $encoding );
		}
		
		if ($input_word == $surface && $hinshi == '名詞') {
			return;
		}
		
		throw new JapaneseIndexerException ( 'MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_EXT' );
	}
	
	/**
	 *
	 * @param string $encodings        	
	 * @param string $dicdirs        	
	 * @throws JapaneseIndexerException
	 */
	static function autofind_config($encodings, $dicdirs) {
		$config = array (
				'encoding' => '',
				'dicdir' => '' 
		);
		
		// dicdir
		if (! JapaneseIndexerUtil::loadExtension ( 'mecab' ) || ! is_callable ( 'mecab_new' )) {
			return $config;
		}
		
		$engine = mecab_new ();
		if (is_resource ( $engine )) {
			$config ['dicdir'] = '';
		} else {
			foreach ( $dicdirs as $dicdir ) {
				$engine = @mecab_new ( array (
						'-d',
						$dicdir 
				) );
				if (is_resource ( $engine )) {
					$config ['dicdir'] = $dicdir;
					break;
				}
			}
		}
		
		// encoding
		foreach ( $encodings as $encoding ) {
			try {
				self::test_wakachigaki ( $encoding, $config ['dicdir'] );
			} catch ( JapaneseIndexerException $e ) {
				continue;
			}
			$config ['encoding'] = $encoding;
			break;
		}
		
		if ($config ['dicdir'] == '' && $config ['encoding'] == '') {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_COULDNOT_FIND_CONFIG_SETTINGS' );
		}
		
		return $config;
	}
	
	/**
	 *
	 * @throws JapaneseIndexerException
	 */
	private function set_config() {
		$config = $this->DataStructure->get_Ext ();
		
		if ($config ['encoding'] == '') {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_CONFIG_ENCODING_EMPTY' );
		}
		
		$this->config = $config;
	}
	private function set_engine() {
		if ($this->config ['dicdir'] != '') {
			$this->engine = mecab_new ( array (
					'-d',
					$this->config ['dicdir'] 
			) );
		} else {
			$this->engine = mecab_new ();
		}
		register_shutdown_function ( array (
				$this,
				'mecab_destroy' 
		) );
	}
	private function mecab_destroy() {
		mecab_destroy ( $this->engine );
	}
}
class JapaneseIndexer_MeCabCLI extends JapaneseIndexer_MeCabBase {
	private $config;
	
	/**
	 *
	 * @throws JapaneseIndexerException
	 */
	protected function boot() {
		$this->set_config ();
	}
	
	/**
	 *
	 * @param string $text        	
	 * @throws JapaneseIndexerException
	 */
	function wakachigaki($text) {
		$text_analisis = self::cli_pipe ( $text, $this->config ['encoding'], $this->config ['exepath'] );
		
		if (! sizeof ( $text_analisis )) {
			return '';
		}
		
		$text_pieces = array ();
		foreach ( $text_analisis as $analisis ) {
			$text_pieces [] = array (
					'surface' => $analisis ['word'],
					'hinshi' => $analisis ['class'] 
			);
		}
		
		$wakachigaki = $this->assemble_wakachigaki ( $text_pieces );
		
		return $wakachigaki;
	}
	
	/**
	 *
	 * @param string $encoding        	
	 * @param string $exepath        	
	 * @throws JapaneseIndexerException
	 */
	static function test_wakachigaki($encoding, $exepath) {
		// requirement
		if (! is_callable ( 'proc_open' )) {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_FUNC_PROCOPEN_UNSUPPORTED' );
		}
		if (strtolower ( $encoding ) != 'utf-8') {
			if (! JapaneseIndexerUtil::loadExtension ( 'mbstring' )) {
				throw new JapaneseIndexerException ( 'MECAB_ERROR_MBSTRING_UNSUPPORTED' );
			}
		}
		
		// test wakachigaki
		$input_word = '愛';
		$output_word = '';
		$output_class = '';
		if ($text_analisis = self::cli_pipe ( $input_word, $encoding, $exepath )) {
			if (isset ( $text_analisis [0] )) {
				$output_word = $text_analisis [0] ['word'];
				$output_class = $text_analisis [0] ['class'];
			}
		}
		if ($input_word == $output_word && $output_class == '名詞') {
			return;
		}
		
		throw new JapaneseIndexerException ( 'MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_CLI' );
	}
	
	/**
	 *
	 * @param string $encodings        	
	 * @throws JapaneseIndexerException
	 */
	static function autofind_config($encodings) {
		$config = array (
				'encoding' => '',
				'exepath' => '' 
		);
		
		if (! is_callable ( 'proc_open' )) {
			return $config;
		}
		
		// path
		$config ['exepath'] = self::find_MeCab ();
		
		// encoding
		foreach ( $encodings as $encoding ) {
			try {
				self::test_wakachigaki ( $encoding, $config ['exepath'] );
			} catch ( JapaneseIndexerException $e ) {
				continue;
			}
			$config ['encoding'] = $encoding;
			break;
		}
		
		if ($config ['exepath'] == '' && $config ['encoding'] == '') {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_COULDNOT_FIND_CONFIG_SETTINGS' );
		}
		
		return $config;
	}
	
	/**
	 *
	 * @throws JapaneseIndexerException
	 */
	private function set_config() {
		$config = $this->DataStructure->get_CLI ();
		
		if ($config ['encoding'] == '') {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_CONFIG_ENCODING_EMPTY' );
		}
		
		$this->config = $config;
	}
	
	/**
	 *
	 * @param string $text        	
	 * @param string $encoding        	
	 * @param string $exepath        	
	 * @throws JapaneseIndexerException
	 */
	static private function cli_pipe($text, $encoding, $exepath) {
		if (strtolower ( $encoding ) != 'utf-8') {
			$text = @mb_convert_encoding ( $text, $encoding, 'utf-8' );
			if ($text === false) {
				throw new JapaneseIndexerException ( 'MECAB_ERROR_WAKACHIGAKI_FAILED_BY_WRONG_ENCODING' );
			}
		}
		
		$descriptorspec = array (
				0 => array ( 'pipe',	'r' ), // stdin
				1 => array ( 'pipe', 'w' ), // stdout
				2 => array ( 'pipe',	'w' ), // stderr
		);
		$stdout = '';
		$pipes = array ();
		
		$process = proc_open ( $exepath, $descriptorspec, $pipes );
		if (! is_resource ( $process )) {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_CLI' );
		}
		
		stream_set_blocking ( $pipes [0], 0 );
		stream_set_blocking ( $pipes [1], 0 );
		stream_set_blocking ( $pipes [2], 0 );
		
		// stdin
		fwrite ( $pipes [0], $text );
		fclose ( $pipes [0] );
		
		// stdout
		while ( ! feof ( $pipes [1] ) ) {
			$stdout .= fgets ( $pipes [1] );
		}
		fclose ( $pipes [1] );
		
		// stderr
		$stderr = @stream_get_contents ( $pipes [2] );
		
		proc_close ( $process );
		
		if (! empty ( $stderr )) {
			throw new JapaneseIndexerException ( 'MECAB_ERROR_WAKACHIGAKI_FAILED_BY_STDERR' );
		}
		
		if (strtolower ( $encoding ) != 'utf-8') {
			$stdout = @mb_convert_encoding ( $stdout, 'utf-8', $encoding );
			if ($stdout === false) {
				throw new JapaneseIndexerException ( 'MECAB_ERROR_WAKACHIGAKI_FAILED_BY_WRONG_ENCODING' );
			}
		}
		
		$stdout = str_replace ( array (
				"\r\n",
				"\r" 
		), "\n", $stdout );
		$lines = explode ( "\n", $stdout );
		$analisis = array ();
		foreach ( $lines as $line ) {
			if (in_array ( trim ( $line ), array (
					'EOS',
					'' 
			) )) {
				continue;
			}
			$s = explode ( "\t", $line );
			$word = $s [0];
			$info = explode ( ',', $s [1] );
			$analisis [] = array (
					'word' => $word,
					'class' => $info [0] 
			// 'detail1' => $info[1],
			// 'detail2' => $info[2],
			// 'detail3' => $info[3],
			// 'conjugation1' => $info[4],
			// 'conjugation2' => $info[5]
						);
		}
		
		return $analisis;
	}
	static private function find_MeCab() {
		$mecab_location_path = self::find_MeCab_location ();
		
		if (! $mecab_location_path) {
			return '';
		}
		
		return $mecab_location_path . 'mecab' . ((defined ( 'PHP_OS' ) && preg_match ( '#^win#i', PHP_OS )) ? '.exe' : '');
	}
	
	/**
	 * Search MeCab location
	 */
	static private function find_MeCab_location() {
		$mecab_location = '';
		
		$exe = ((defined ( 'PHP_OS' )) && (preg_match ( '#^win#i', PHP_OS ))) ? '.exe' : '';
		
		$mecab_home = getenv ( 'MECAB_HOME' );
		
		if (empty ( $mecab_home )) {
			$locations = array (
					'/usr/bin/',
					'/usr/sbin/',
					'/usr/local/bin/',
					'/usr/local/sbin/',
					'/opt/',
					'/usr/mecab/',
					'/usr/bin/mecab/',
					'C:/WINDOWS/',
					'C:/WINNT/',
					'C:/WINDOWS/SYSTEM/',
					'C:/WINNT/SYSTEM/',
					'C:/WINDOWS/SYSTEM32/',
					'C:/WINNT/SYSTEM32/' 
			);
			$path_locations = str_replace ( '\\', '/', (explode ( ($exe) ? ';' : ':', getenv ( 'PATH' ) )) );
			
			$locations = array_merge ( $path_locations, $locations );
			
			foreach ( $locations as $location ) {
				// The path might not end properly, fudge it
				if (substr ( $location, - 1 ) !== '/') {
					$location .= '/';
				}
				
				if (@file_exists ( $location ) && @is_readable ( $location . 'mecab' . $exe ) && @filesize ( $location . 'mecab' . $exe ) > 3000) {
					$mecab_location = str_replace ( '\\', '/', $location );
					break;
				}
			}
		} else {
			$mecab_location = str_replace ( '\\', '/', $mecab_home );
		}
		
		return $mecab_location;
	}
}
class JapaneseIndexer_MeCabBase {
	protected $DataStructure;
	function __construct(JapaneseIndexer_MeCabData $data) {
		$this->DataStructure = $data;
		
		$this->boot ();
	}
	protected function assemble_wakachigaki($text_pieces) {
		$wakachigaki_level = $this->DataStructure->get_wakachigaki_level ();
		$renzoku_hinshi = $this->DataStructure->get_renzoku_hinshi ();
		$only_meishi = $this->DataStructure->get_only_meishi ();
		
		$wakachigaki = '';
		
		foreach ( $text_pieces as $i => $piece ) {
			if ($i > 0) {
				$previous_hinshi = $hinshi;
			}
			
			$surface = $piece ['surface'];
			$hinshi = $piece ['hinshi'];
			
			if ($only_meishi && $hinshi != '名詞') {
				continue;
			}
			if ($i == 0) {
				$wakachigaki .= $surface;
				continue;
			}
			
			if ($renzoku_hinshi && $hinshi == $previous_hinshi) {
				$wakachigaki .= $surface;
			} else if (($hinshi == '記号' && $wakachigaki_level >= JapaneseIndexer_MeCabData::WAKACHIGAKI_LEVEL_KIGOU) || ($hinshi == '名詞' && $wakachigaki_level >= JapaneseIndexer_MeCabData::WAKACHIGAKI_LEVEL_MEISHI) || ($hinshi == '助詞' && $wakachigaki_level >= JapaneseIndexer_MeCabData::WAKACHIGAKI_LEVEL_JOSHI)) {
				$wakachigaki .= ' ' . $surface;
			} else if (($previous_hinshi == '記号' && $wakachigaki_level >= JapaneseIndexer_MeCabData::WAKACHIGAKI_LEVEL_KIGOU) || ($previous_hinshi == '名詞' && $wakachigaki_level >= JapaneseIndexer_MeCabData::WAKACHIGAKI_LEVEL_MEISHI) || ($previous_hinshi == '助詞' && $wakachigaki_level >= JapaneseIndexer_MeCabData::WAKACHIGAKI_LEVEL_JOSHI)) {
				$wakachigaki .= ' ' . $surface;
			} else {
				$wakachigaki .= $surface;
			}
		}
		
		return $wakachigaki;
	}
}
