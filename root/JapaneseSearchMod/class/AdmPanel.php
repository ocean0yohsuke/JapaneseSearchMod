<?php
class phpBB3_JapaneseSearchModAdmPanel {
	function __construct() {
		if (! class_exists ( 'ObjectFileSystem' )) {
			include_once PHPBB_JAPANESESEARCHMOD_ROOT_PATH . 'include/FileSystemOOP/ObjectFileSystem.php';
		}
	}
	function main($panel) {
		global $phpbb_root_path;
		global $config, $user, $template;
		
		try {
			$setup_done = (! isset ( $config ['JapaneseSearchMod_version'] ) || ($config ['JapaneseSearchMod_version'] != phpBB3_JapaneseSearchModMain::VERSION)) ? false : true;
			$searchtype_is_fulltextnativeja = ($config ['search_type'] == 'fulltext_native_ja') ? true : false;
			$template->assign_vars ( array (
					'S_SETUP_DONE' => $setup_done,
					'S_SEARCHTYPE_IS_FULLTEXTNATIVEJA' => $searchtype_is_fulltextnativeja 
			) );
			$AdmPanel = phpBB3_JapaneseSearchModUtil::ObjectFileSystem ( 'adm/panel', 'AdmPanel', array (
					'setup_done' => $setup_done,
					'searchtype_is_fulltextnativeja' => $searchtype_is_fulltextnativeja 
			) );
			switch ($panel) {
				case 'indexer' :
					$AdmPanel->Indexer ();
					break;
				case 'setup' :
					$AdmPanel->Setup ();
					break;
				default :
					throw new phpBB3_JapaneseSearchModException ( $user->lang ['JSM_PANEL_INVALID_PANEL_SPECIFIED'] );
			}
		} catch ( ObjectFileSystemException $e ) {
			if (defined ( 'DEBUG' )) {
				$e->getException ();
			} else {
				throw new phpBB3_JapaneseSearchModException ( '[ObjectFileSystem Error] ' . $e->getMessage (), E_USER_ERROR );
			}
		}
	}
}
