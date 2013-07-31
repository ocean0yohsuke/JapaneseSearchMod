<?php
class phpBB3_JapaneseSearchModAdmPanel_setup_unsetup_run extends phpBB3_JapaneseSearchModAdmPanel_Base
{
	function main()
	{
		if (!check_form_key('unsetup_run'))
		{
			throw new phpBB3_JapaneseSearchModException('FORM_INVALID');
		}

		global $template, $user;
				
		$Setup = new phpBB3_JapaneseSearchModSetup();
		$Setup->unsetup();

		$this->setup_done = false;
		
		$message = sprintf($user->lang['JSM_SETUPPANEL_UNSETUP_SUCCESSED'], phpBB3_JapaneseSearchModMain::VERSION);
		$message .= '<br /><br />';
		$message .= implode('<br />', $Setup->getMessages());
		
		$template->assign_vars(array(
			'MESSAGE'		=> $message,
			'S_SETUP_DONE' 	=> $this->setup_done,
		));
	}
}
