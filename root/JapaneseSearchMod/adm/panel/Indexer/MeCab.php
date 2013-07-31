<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer_MeCab extends phpBB3_JapaneseSearchModAdmPanel_BaseMenu
{
	function main($current_page)
	{
		$this->set_pageinfo($current_page, 'sidemenu', 'intro');

		global $template;
		
		$template->assign_vars(array(
			'S_SIDEMENU'	=> $this->get_pageinfo('menu_var'),
		));
		
		// linear blocks
		$this->create_linear_block('intro');
		$this->create_linear_block('config');
		$this->create_linear_block('setup');

		switch($this->get_pageinfo('menu_var'))
		{
			case 'intro' :
				$this->intro();
				break;
			case 'config' :
				$this->config();
				break;
			case 'setup' :
				$this->setup($this->get_referred_page());
				break;
			default :
				throw new phpBB3_JapaneseSearchModException("Invalid sidemenu was specified. Specified sidemenu is '$this->get_pageinfo('menu_var')'.");
		}
	}

	protected function create_linear_block($block, $block_type = '1')
	{
		parent::create_linear_block($block, 'INDEXERPANEL_MECAB', $block_type);
	}
}
