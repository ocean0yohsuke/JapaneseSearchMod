<?php
define ( 'PHPBB_JAPANESESEARCHMOD_ROOT_PATH', $phpbb_root_path . 'JapaneseSearchMod/' );

// PHP version must be 5.1.2 or higher
spl_autoload_register ( 'phpBB3_JapaneseSearchMod_autoload' );
function phpBB3_JapaneseSearchMod_autoload($class_name) {
	$class_list = array (
			'phpBB3_JapaneseSearchModAdm' => 0,
			'phpBB3_JapaneseSearchModAdmPanel' => 0,
			'phpBB3_JapaneseSearchModException' => 0,
			'phpBB3_JapaneseSearchModFulltextNativeJa' => 0,
			'phpBB3_JapaneseSearchModHighlight' => 0,
			'phpBB3_JapaneseSearchModMain' => 0,
			'phpBB3_JapaneseSearchModSetup' => 0,
			'phpBB3_JapaneseSearchModUtil' => 0 
	);
	if (isset ( $class_list [$class_name] )) {
		$base_name = preg_replace ( '#^phpBB3_JapaneseSearchMod#', '', $class_name );
		include_once (PHPBB_JAPANESESEARCHMOD_ROOT_PATH . "class/{$base_name}.php");
	}
}
