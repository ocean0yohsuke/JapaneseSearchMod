<?php

class phpBB3_JapaneseSearchModAdmPanel
{
	function __construct()
	{
		if (!class_exists('ObjectFileSystem')) {
			include_once PHPBB_JAPANESESEARCHMOD_ROOT_PATH . 'include/FileSystemOOP/ObjectFileSystem.php';
		}
	}

	function main($panel)
	{
		global $phpbb_root_path;
		global $config, $user, $template;

		try {
			$AdmPanel = phpBB3_JapaneseSearchModUtil::ObjectFileSystem('adm/panel', 'AdmPanel');
			switch ($panel)
			{
				case 'indexer':
					$AdmPanel->Indexer();
					break;
				case 'setup':
					$AdmPanel->Setup();
					break;
				default:
					throw new phpBB3_JapaneseSearchModException($user->lang['JSM_PANEL_INVALID_PANEL_SPECIFIED']);
			}
		}
		catch (ObjectFileSystemException $e) {
			if (defined('DEBUG')) {
				$e->getException();
			} else {
				throw new phpBB3_JapaneseSearchModException('[ObjectFileSystem Error] ' . $e->getMessage(), E_USER_ERROR);
			}
		}
	}
}
