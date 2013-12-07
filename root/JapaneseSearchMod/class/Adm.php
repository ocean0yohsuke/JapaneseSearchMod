<?php
class phpBB3_JapaneseSearchModAdm {
	private $panel;
	function __construct($panel) {
		global $phpbb_root_path;
		
		$this->panel = $panel;
		
		$this->common_boot ();
	}
	function output_panel() {
		global $template, $user;
		
		switch ($this->panel) {
			case 'indexer' :
				$panel_title = $user->lang ['JSM_INDEXERPANEL_TITLE'];
				break;
			case 'setup' :
				$panel_title = $user->lang ['JSM_SETUPPANEL_TITLE'];
				break;
			default :
				throw new phpBB3_JapaneseSearchModException ( $user->lang ['JSM_PANEL_INVALID_PANEL_SPECIFIED'] );
		}
		
		$Panel = new phpBB3_JapaneseSearchModAdmPanel ();
		$Panel->main ( $this->panel );
		
		// common
		$template->assign_vars ( array (
				'S_PANEL' => $this->panel,
				'PANEL_TITLE' => $panel_title,
				'JSM_VERSION' => phpBB3_JapaneseSearchModMain::VERSION,
				
				'U_INDEXERPANEL_INDEX' => append_sid ( 'indexer.php' ),
				'U_SETUPPANEL_INDEX' => append_sid ( 'setup.php' ) 
		) );
		
		// Output page
		page_header ( $panel_title . ' &bull; JapaneseSearchMod' );
		$template->set_filenames ( array (
				'body' => 'start.html' 
		) );
		page_footer ();
	}
	private function common_boot() {
		global $phpbb_root_path, $phpEx;
		global $template, $user, $auth;
		global $phpbb_admin_path;
		
		// Start session management
		$user->session_begin ();
		$auth->acl ( $user->data );
		$user->setup ( array (
				'acp/common' 
		) );
		phpBB3_JapaneseSearchModMain::load_lang ();
		
		// Is user any type of admin? No, then stop here, each script needs to
		// check specific permissions but this is a catchall
		if (! $auth->acl_get ( 'a_' )) {
			throw new phpBB3_JapaneseSearchModException ( 'NO_ADMIN' );
		}
		
		// Have they authenticated (again) as an admin for this session?
		if (! isset ( $user->data ['session_admin'] ) || ! $user->data ['session_admin']) {
			throw new phpBB3_JapaneseSearchModException ( 'JSM_PANEL_LOGIN_ADMIN_CONFIRM' );
		}
		
		// We define the admin variables now, because the user is now able to use the admin related features...
		define ( 'IN_ADMIN', true );
		$phpbb_admin_path = (defined ( 'PHPBB_ADMIN_PATH' )) ? PHPBB_ADMIN_PATH : '../../../adm/';
		
		// setup template
		$template_path = $phpbb_root_path . 'JapaneseSearchMod/adm/style';
		$template_name = 'JapaneseSearchModAdm';
		$fallback_template_path = false;
		$template->set_custom_template ( $template_path, $template_name, $fallback_template_path );
		
		include_once $phpbb_root_path . 'JapaneseSearchMod/adm/includes/functions.php';
	}
}
