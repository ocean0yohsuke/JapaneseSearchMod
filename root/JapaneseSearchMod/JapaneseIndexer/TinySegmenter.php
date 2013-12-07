<?php
include_once 'JapaneseIndexer_AL.php';

/**
 * This class is an indexer for Japanese, using TinySegmenter as engine for wakachigaki.
 *
 * To use wakachigaki() method, following requirements have to be met in advance.
 * 1. tiny_segmenter.php file exists directly under JapaneseIndexer/TinySegmenter directory.
 *
 * Error message thrown by exception would be one of the following strings :
 * COULD_NOT_FIND_CLASSFILE
 * COULD_NOT_FIND_CLASS
 */
class JapaneseIndexer_TinySegmenter extends JapaneseIndexer_AL {
	private $engine;
	function __construct() {
		@set_time_limit ( 0 );
		
		if (! file_exists ( dirname ( __FILE__ ) . '/TinySegmenter/tiny_segmenter.php' )) {
			throw new JapaneseIndexerException ( 'TINYSEGMENTER_ERROR_COULD_NOT_FIND_CLASSFILE' );
		}
		@include_once ('TinySegmenter/tiny_segmenter.php');
		
		if (! class_exists ( 'TinySegmenterarray' )) {
			throw new JapaneseIndexerException ( 'TINYSEGMENTER_ERROR_COULD_NOT_FIND_CLASS' );
		}
		
		$this->set_engine ();
	}
	function wakachigaki($text) {
		$wakachigaki = '';
		$segment_array = $this->engine->segment ( $text );
		
		foreach ( $segment_array as $segment ) {
			$wakachigaki .= $segment . ' ';
		}
		
		return $wakachigaki;
	}
	function isAbleToWakachigaki() {
		if (! isset ( $this->engine )) {
			return false;
		}
		
		return true;
	}
	private function set_engine() {
		$this->engine = new TinySegmenterarray ();
	}
}
