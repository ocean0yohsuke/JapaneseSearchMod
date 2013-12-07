<?php
class phpBB3_JapaneseSearchModAdmPanel_Setup extends phpBB3_JapaneseSearchModAdmPanel_BaseMenu {
	private $start_page = 'setup.php';
	function main() {
		$this->set_pageinfo ( $this->start_page, 'tabmenu', 'overview' );
		
		global $template;
		
		$template->assign_vars ( array (
				'S_TABMENU' => $this->get_pageinfo ( 'menu_var' ) 
		) );
		
		// tabular blocks
		$this->create_tabular_block ( 'overview' );
		$this->create_tabular_block ( 'setup' );
		$this->create_tabular_block ( 'unsetup' );
		
		switch ($this->get_pageinfo ( 'menu_var' )) {
			case 'overview' :
				$this->overview ( $this->get_referred_page () );
				break;
			case 'setup' :
				$this->setup ( $this->get_referred_page () );
				break;
			case 'unsetup' :
				$this->unsetup ( $this->get_referred_page () );
				break;
			default :
				throw new phpBB3_JapaneseSearchModException ( "Invalid tabmenu was specified. Specified tabmenu is '{$this->get_pageinfo('menu_var')}'." );
		}
	}
	protected function create_tabular_block($block) {
		parent::create_tabular_block ( $block, 'SETUPPANEL' );
	}
}
