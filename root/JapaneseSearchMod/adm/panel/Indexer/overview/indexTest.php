<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer_overview_indexTest extends phpBB3_JapaneseSearchModAdmPanel_Base
{
	function main($current_page)
	{
		global $phpbb_root_path, $phpEx;
		global $user, $template;

		$submit = (isset($_POST['submit'])) ? true : false;
		$input_text = utf8_normalize_nfc(request_var('input_text', '', true));

		$form_key = 'index_test';
		add_form_key($form_key);
		
		$template->assign_vars(array(
			'TITLE'			=> $user->lang['JSM_INDEXTEST_TITLE'],
			'TITLE_EXPLAIN'	=> $user->lang['JSM_INDEXTEST_TITLE_EXPLAIN'],

			'U_ACTION'		=> append_sid($current_page) . '#main',
			'INPUT_TEXT'	=> $input_text,
		));

		if (!$input_text)
		{
			return;
		}

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
			    throw new phpBB3_JapaneseSearchModException($user->lang['FORM_INVALID']);
			}
			
			$JapaneseSearchMod = new phpBB3_JapaneseSearchModMain();
				
			$engine_list = phpBB3_JapaneseSearchModFulltextNativeJa::get_indexer_list();

			foreach($engine_list as $engine_name)
			{
				$starttime = explode(' ', microtime());
				$starttime = $starttime[1] + $starttime[0];
				
				$JapaneseSearchMod = new phpBB3_JapaneseSearchModMain($engine_name);
				$wakachigaki_text = $JapaneseSearchMod->wakachigaki($input_text);

				$mtime = explode(' ', microtime());
				$totaltime = pow(10,6) * ($mtime[0] + $mtime[1] - $starttime);

				$template->assign_block_vars('engine', array(
					'NAME'					=> $engine_name,
					'WAKACHIGAKI_TEXT'	=> $wakachigaki_text,
					'TIME'					=> $totaltime,
				));
			}
		}
	}
}
