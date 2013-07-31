<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer extends phpBB3_JapaneseSearchModAdmPanel_BaseMenu
{
	public $start_page = 'indexer.php';
	
	function main()
	{
		$this->set_pageinfo($this->start_page, 'tabmenu', 'overview');

		global $user, $template;

		if (!$this->setup_done) {
			throw new phpBB3_JapaneseSearchModException((sprintf($user->lang['JSM_SETUPPANEL_SETUP_NOT_DONE'], phpBB3_JapaneseSearchModMain::VERSION)));
		}

		$template->assign_vars(array(
			'S_TABMENU' 	=> $this->get_pageinfo('menu_var'),
		));

		// tabular blocks
		$this->create_tabular_block('overview');
		$this->create_tabular_block('phpBB3Native');
		$this->create_tabular_block('TinySegmenter');
		$this->create_tabular_block('MeCab');

		switch($this->get_pageinfo('menu_var'))
		{
			case 'overview' :
				$this->overview($this->get_referred_page());
				break;
			case 'phpBB3Native' :
				$this->phpBB3Native($this->get_referred_page());
				break;
			case 'TinySegmenter' :
				$this->TinySegmenter($this->get_referred_page());
				break;
			case 'MeCab' :
				$this->MeCab($this->get_referred_page());
				break;
			default :
				throw new phpBB3_JapaneseSearchModException("Invalid tabmenu was specified. Specified tabmenu is '{$this->get_pageinfo('menu_var')}'.");
		}
	}
	
	protected function create_tabular_block($block)
	{
		parent::create_tabular_block($block, 'INDEXERPANEL');
	}	
}
