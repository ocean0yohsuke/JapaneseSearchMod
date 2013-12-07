<?php
class phpBB3_JapaneseSearchModAdmPanel_Setup_overview extends phpBB3_JapaneseSearchModAdmPanel_BaseMenu {
	function main($current_page) {
		$this->set_pageinfo ( $current_page, 'sidemenu', 'intro' );
		
		global $user, $template;
		
		$template->assign_vars ( array (
				'S_SIDEMENU' => $this->get_pageinfo ( 'menu_var' ),
				
				'SETUP_DONE_MESSAGE' => sprintf ( $user->lang ['JSM_SETUPPANEL_SETUP_DONE'], phpBB3_JapaneseSearchModMain::VERSION ),
				'SETUP_NOT_DONE_MESSAGE' => sprintf ( $user->lang ['JSM_SETUPPANEL_SETUP_NOT_DONE'], phpBB3_JapaneseSearchModMain::VERSION ) 
		) );
		
		// linear blocks
		$this->create_linear_block ( 'intro' );
	}
	protected function create_linear_block($block, $block_type = '1') {
		parent::create_linear_block ( $block, 'SETUPPANEL_OVERVIEW', $block_type );
	}
}
