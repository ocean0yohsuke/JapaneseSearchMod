<?php
/**
 */
define('IN_PHPBB', true);
//define('ADMIN_START', true);
define('NEED_SID', true);

// Include files
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);

try {
	require_once($phpbb_root_path . "JapaneseSearchMod/autoload.php");
	$phpBB3_JapaneseSearchModAdm = new phpBB3_JapaneseSearchModAdm('indexer');
	$phpBB3_JapaneseSearchModAdm->output_panel();
}
catch (phpBB3_JapaneseSearchModException $e) {
	$e->getException();
}
?>