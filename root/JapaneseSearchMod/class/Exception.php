<?php
class phpBB3_JapaneseSearchModException extends Exception {
	function getException() {
		$message = '[JapaneseSearchMod Error] ';
		$message .= $this->message;
		if (defined ( 'DEBUG' )) {
			$message .= '<br /><br />';
			$message .= 'This error was thrown';
			$message .= ' in file ' . $this->file;
			$message .= ' on line ' . $this->line;
			$message .= '<br />';
			$message .= "\n";
			
			trigger_error ( $message );
		} else {
			trigger_error ( $message, E_USER_ERROR );
		}
	}
}