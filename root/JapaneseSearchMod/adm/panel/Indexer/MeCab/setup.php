<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer_MeCab_setup extends phpBB3_JapaneseSearchModAdmPanel_Base
{
	function main($referer)
	{
		$this->u_action	= append_sid($referer);

		include_once(PHPBB_JAPANESESEARCHMOD_ROOT_PATH . 'JapaneseIndexer/MeCab.php');

		global $user, $template;

		$template->assign_vars(array(
			'TITLE'			=> $user->lang['JSM_MECAB_SETUP_TITLE'],
			'TITLE_EXPLAIN'	=> $user->lang['JSM_MECAB_SETUP_TITLE_EXPLAIN'],
		));

		$submit = (isset($_POST['submit']) || isset($_POST['submit_auto'])) ? true : false;

		$form_key = 'MeCab_setup';
		add_form_key($form_key);

		/**
		 *	Validation types are:
		 *		string, int, bool,
		 *		script_path (absolute path in url - beginning with / and no trailing slash),
		 *		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		 */
		$display_vars = array(
			'title'	=> 'JSM_MECAB_SETTINGS',
			'vars'	=> array(
				'legend1'				=> 'JSM_MECAB_SETUP_EXT',
				'ext_encoding'			=> array('lang' => 'JSM_MECAB_SETUP_EXT_ENCODING',		'validate' => 'string',		'type' => 'text:10:20', 'explain' => true),
				'ext_dicdir'			=> array('lang' => 'JSM_MECAB_SETUP_EXT_DICDIR',			'validate' => 'string',		'type' => 'text:30:50', 'explain' => true),

				'legend2'				=> 'JSM_MECAB_SETUP_CLI',
				'cli_encoding'			=> array('lang' => 'JSM_MECAB_SETUP_CLI_ENCODING',	'validate' => 'string',		'type' => 'text:10:20', 'explain' => true),
				'cli_exepath'			=> array('lang' => 'JSM_MECAB_SETUP_CLI_EXEPATH',	'validate' => 'string',		'type' => 'text:30:50', 'explain' => true),
		));
			
		$old_config = $this->get_config_data();
		$new_config = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : array();
		$config = (sizeof($new_config))? $new_config : $old_config;

		$form_error = $notice = array();
		$setup_error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $config, $form_error);

		if ($submit && !check_form_key($form_key)) {
			$form_error[] = $user->lang['FORM_INVALID'];
		}

		// Do not write values if there is an error
		if (sizeof($form_error)) {
			$submit = false;
		}

		if ($submit){
			try {
				$submit_auto = (isset($_POST['submit_auto'])) ? true : false;
				if ($submit_auto) {
					phpBB3_JapaneseSearchModMain::MeCab_set_config();
				}
				else {
					phpBB3_JapaneseSearchModMain::MeCab_set_config(array(
						'Ext'	=> array(
							'encoding' 			=> $config['ext_encoding'],
							'dicdir'			=> $config['ext_dicdir'],
						),
						'CLI'	=> array(
							'encoding' 			=> $config['cli_encoding'],
							'exepath'			=> $config['cli_exepath'],
						),
					));
				}
				$config = $this->get_config_data();
				$notice[] = $user->lang['JSM_MECAB_SETUP_SUCCESSED'];
			}
			catch(phpBB3_JapaneseSearchModException $e) {
				$setup_error[] = $user->lang['JSM_MECAB_SETUP_FAILED'];
				$setup_error[] = $user->lang[$e->getMessage()];
			}
		}

		if (sizeof($form_error)) {
			$template->assign_vars(array(
				'S_FORM_ERROR'			=> true,
				'FORM_ERROR_MSG'		=> implode('<br />', $form_error),
			));
		} else {
			$template->assign_vars(array(
				'S_SHOW_BUTTONS'			=> true,
			));
		}
		if (sizeof($setup_error)) {
			$template->assign_vars(array(
				'S_SETUP_ERROR'			=> true,
				'SETUP_ERROR_MSG'		=> implode('<br />', $setup_error),
			));
		}
		if ($submit && !sizeof($form_error) && !sizeof($setup_error)) {
			$template->assign_vars(array(
				'S_OPENERWINDOW_RELOAD'		=> true,
			));
		}
		if (sizeof($notice)) {
			$template->assign_vars(array(
				'S_NOTICE'			=> true,
				'NOTICE_MSG'		=> implode('<br />', $notice),
			));
		}

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false) {
				continue;
			}

			if (strpos($config_key, 'legend') !== false) {
				try {
					$error_message = null;
					switch($vars)
					{
						case 'JSM_MECAB_SETUP_EXT' :
							JapaneseIndexer_MeCab::test_wakachigaki('Ext', array(
								'encoding' 	=> $config['ext_encoding'],
								'dicdir'	=> $config['ext_dicdir'],
							));
							break;
						case 'JSM_MECAB_SETUP_CLI' :
							JapaneseIndexer_MeCab::test_wakachigaki('CLI', array(
								'encoding' 	=> $config['cli_encoding'],
								'exepath'	=> $config['cli_exepath'],
							));
							break;
					}
				}
				catch(JapaneseIndexerException $e) {
					$error_message = $e->getMessage();
				}

				$wakachigakitest_result = (!isset($error_message))?
					'<strong style="color:green">' . $user->lang['JSM_SUCCESSED'] . '</strong>' : 
					'<strong style="color:red">' . $user->lang['JSM_' . $error_message] . '</strong>';

				$template->assign_block_vars('options', array(
					'S_LEGEND'				=> true,
					'LEGEND'				=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars,
					'LEGEND_EXPLAIN'		=> (isset($user->lang[$vars . '_EXPLAIN'])) ? $user->lang[$vars. '_EXPLAIN'] : '',
					'WAKACHIGAKITEST_RESULT' => $wakachigakitest_result,
				));

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain'])) {
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain']){
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $config, $config_key, $vars);

			if (empty($content))	{
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

	private function get_config_data()
	{
		$config = phpBB3_JapaneseSearchModMain::MeCab_get_config();

		return $config_data = array(
			'ext_encoding' 			=> $config['Ext']['encoding'],
			'ext_dicdir'			=> $config['Ext']['dicdir'],
			'cli_encoding'			=> $config['CLI']['encoding'],
			'cli_exepath'			=> $config['CLI']['exepath'],
		);
	}
}