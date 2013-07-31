<?php
class phpBB3_JapaneseSearchModAdmPanel_Base extends ObjectFileSystemFile
{
	public $start_page;

	public $setup_done;
	public $searchtype_is_fulltextnativeja;

	function __construct()
	{
		global $config, $template;

		$this->setup_done = (!isset($config['JapaneseSearchMod_version']) || ($config['JapaneseSearchMod_version'] != phpBB3_JapaneseSearchModMain::VERSION))? false : true;
		$this->searchtype_is_fulltextnativeja = ($config['search_type'] == 'fulltext_native_ja')? true : false;
		
		$template->assign_vars(array(
			'S_SETUP_DONE' 						=> $this->setup_done,
			'S_SEARCHTYPE_IS_FULLTEXTNATIVEJA'	=> $this->searchtype_is_fulltextnativeja,
		));
	}
}

class phpBB3_JapaneseSearchModAdmPanel_BaseMenu extends phpBB3_JapaneseSearchModAdmPanel_Base
{
	private $pageinfo = array(
		'current_page' 	=> '',
		'menu_name'		=> '',
		'menu_var'			=> '',
	);

	protected function set_pageinfo($current_page, $menu_name, $menu_default_var)
	{
		$this->pageinfo['current_page'] 	= $current_page;
		$this->pageinfo['menu_name'] 		= $menu_name;
		$this->pageinfo['menu_var'] 		= request_var($menu_name, $menu_default_var);
	}
	protected function get_pageinfo($key)
	{
		return $this->pageinfo[$key];
	}
	
	protected function get_referred_page()
	{
		$page = $this->pageinfo['current_page'];
		if(strpos($page, "?") !== false) {
			$page .= '&amp;';
		} else {
			$page .= '?';
		}
		$page .= $this->pageinfo['menu_name'] . '=' . $this->pageinfo['menu_var'];
		return $page;
	}

	protected function create_tabular_block($block, $title)
	{
		global $template, $user;

		$template->assign_block_vars('t_block1', array(
			'L_TITLE'		=> $user->lang['JSM_' . $title . '_' . strtoupper($block)],
			'S_SELECTED'	=> ($this->pageinfo['menu_var'] == $block)? true : false,
			'U_TITLE'		=> append_sid($this->pageinfo['current_page'], $this->pageinfo['menu_name'] . '=' . $block),
		));
	}

	protected function create_linear_block($block, $title, $block_type = '1')
	{
		global $template, $user;

		$template->assign_block_vars('l_block' . $block_type, array(
			'L_TITLE'		=> $user->lang['JSM_' . $title . '_' . strtoupper($block)],
			'S_SELECTED'	=> ($this->pageinfo['menu_var'] == $block)? true : false,
			'U_TITLE'		=> append_sid($this->pageinfo['current_page'], $this->pageinfo['menu_name'] . '=' . $block),
		));
	}
}