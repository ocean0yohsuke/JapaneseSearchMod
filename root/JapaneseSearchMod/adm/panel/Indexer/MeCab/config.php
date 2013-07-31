<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer_MeCab_config extends phpBB3_JapaneseSearchModAdmPanel_Base
{
	function main()
	{
		global $template, $user;

		$template->assign_vars(array(
			'TITLE'			=> $user->lang['JSM_MECAB_CONFIG_TITLE'],
			'TITLE_EXPLAIN'	=> $user->lang['JSM_MECAB_CONFIG_TITLE_EXPLAIN'],
		));

		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'MeCab_config';
		add_form_key($form_key);

		/**
		 *	Validation types are:
		 *		string, int, bool,
		 *		script_path (absolute path in url - beginning with / and no trailing slash),
		 *		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		 */
		$display_vars = array(
			'vars'	=> array(
				'legend1'			=> 'JSM_MECAB_CONFIG_WAKACHIGAKI',
				'wakachigaki_level'	=> array('lang' => 'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL', 'validate' => 'string', 'type' => 'select', 'object' => $this, 'method' => 'build_cfg_template_wakachigaki_wakachigaki_level', 'explain' => true),
				'renzoku_hinshi'	=> array('lang' => 'JSM_MECAB_CONFIG_RENZOKU_HINSHI', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
				'only_meishi'		=> array('lang' => 'JSM_MECAB_CONFIG_ONLY_MEISHI', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
		));
			
		$old_config = $this->get_config();
		$new_config = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : array();
		$config = (sizeof($new_config))? $new_config : $old_config;

		$form_error = $notice = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $config, $form_error);

		if ($submit && !check_form_key($form_key)) {
			$form_error[] = $user->lang['FORM_INVALID'];
		}

		if ($submit) {
			if (sizeof($form_error)) {
				$template->assign_vars(array(
				'S_ERROR'		=> true,
				'ERROR_MSG'		=> implode('<br />', $form_error),
				));
			} else {
				$notice[] = $user->lang['JSM_CONFIG_SUCCESSED'];
								
				$template->assign_vars(array(
				'S_NOTICE'		=> true,
				'NOTICE_MSG'		=> implode('<br />', $notice),
				));
			}
			$this->set_config($config);
		}

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false) {
				continue;
			}

			if (strpos($config_key, 'legend') !== false) {
				$template->assign_block_vars('options', array(
					'S_LEGEND'				=> true,
					'LEGEND'				=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars,
				));
				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain'])) {
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain']) {
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $config, $config_key, $vars);

			if (empty($content)){
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
			));

			unset($display_vars['vars'][$config_key]);
		}
	}

	private function set_config($config)
	{
		global $cache;
		
		set_config('JapaneseSearchMod_MeCab_wakachigaki_level', $config['wakachigaki_level']);
		set_config('JapaneseSearchMod_MeCab_renzoku_hinshi', $config['renzoku_hinshi']);
		set_config('JapaneseSearchMod_MeCab_only_meishi', $config['only_meishi']);
		$cache->destroy('config');
	}
	
	private function get_config()
	{
		global $config;

		$MeCab_config = array(
			'wakachigaki_level' => $config['JapaneseSearchMod_MeCab_wakachigaki_level'],
			'renzoku_hinshi' => $config['JapaneseSearchMod_MeCab_renzoku_hinshi'],
			'only_meishi' => $config['JapaneseSearchMod_MeCab_only_meishi'],
		);

		return $MeCab_config;
	}
	
	/**
	* Required chars in passwords
	*/
	function build_cfg_template_wakachigaki_wakachigaki_level($selected_value)
	{
		global $user;
	
		$type_ary = array(
		//0 => 'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_KIGOU',
		1 => 'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_MEISHI',
		2 => 'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_JOSHI',
		3 => 'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_MECABDEFAULT',
		);
		$char_options = '';
		foreach ($type_ary as $value => $type)
		{
			$selected = ($selected_value == $value) ? ' selected="selected"' : '';
			$char_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$type] . '</option>';
		}
	
		return $char_options;
	}	
}