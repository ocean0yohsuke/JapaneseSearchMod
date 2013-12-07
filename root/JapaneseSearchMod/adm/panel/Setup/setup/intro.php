<?php
class phpBB3_JapaneseSearchModAdmPanel_Setup_setup_intro extends phpBB3_JapaneseSearchModAdmPanel_Base {
	function main($referer, $request_id) {
		global $user, $template;
		
		add_form_key ( 'setup_run' );
		
		$template->assign_vars ( array (
				'U_ACTION' => append_sid ( $referer, "{$request_id}=run" ) 
		) );
	}
}
