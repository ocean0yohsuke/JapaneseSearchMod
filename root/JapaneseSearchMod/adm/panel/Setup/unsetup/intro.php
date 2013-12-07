<?php
class phpBB3_JapaneseSearchModAdmPanel_setup_unsetup_intro extends phpBB3_JapaneseSearchModAdmPanel_Base {
	function main($referer, $request_id) {
		global $user, $template;
		
		add_form_key ( 'unsetup_run' );
		
		$template->assign_vars ( array (
				'U_ACTION' => append_sid ( $referer, "{$request_id}=run" ) 
		) );
	}
}
