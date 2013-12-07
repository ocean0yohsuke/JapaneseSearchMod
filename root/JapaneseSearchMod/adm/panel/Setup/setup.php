<?php
class phpBB3_JapaneseSearchModAdmPanel_Setup_setup extends phpBB3_JapaneseSearchModAdmPanel_BaseMenu {
	function main($current_page) {
		$this->set_pageinfo ( $current_page, 'sidemenu', 'intro' );
		
		global $user, $template;
		
		$template->assign_vars ( array (
				'S_SIDEMENU' => $this->get_pageinfo ( 'menu_var' ) 
		) );
		
		// linear blocks
		$this->create_linear_block ( 'intro', 2 );
		$this->create_linear_block ( 'run', 2 );
		
		if ($this->setup_done) {
			$template->assign_vars ( array (
					'INFORMATION_MESSAGE' => sprintf ( $user->lang ['JSM_SETUPPANEL_SETUP_DONE'], phpBB3_JapaneseSearchModMain::VERSION ) 
			) );
		} else {
			switch ($this->get_pageinfo ( 'menu_var' )) {
				case 'intro' :
					$this->intro ( $this->get_pageinfo ( 'current_page' ), $this->get_pageinfo ( 'menu_name' ) );
					break;
				case 'run' :
					$this->run ();
					break;
				default :
					throw new phpBB3_JapaneseSearchModException ( "Invalid sidemenu was specified. Specified sidemenu is '$this->get_pageinfo('menu_var')'." );
			}
		}
	}
	protected function create_linear_block($block, $block_type = '1') {
		parent::create_linear_block ( $block, 'SETUPPANEL_SETUP', $block_type );
	}
}
