<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer_TinySegmenter_setup extends phpBB3_JapaneseSearchModAdmPanel_Base {
	function main() {
		global $user, $template;
		
		$template->assign_vars ( array (
				'TITLE' => $user->lang ['JSM_TINYSEGMENTER_TITLE'],
				'TITLE_EXPLAIN' => $user->lang ['JSM_TINYSEGMENTER_TITLE_EXPLAIN'] 
		) );
		
		$template->assign_block_vars ( 'checks', array (
				'S_LEGEND' => true,
				'LEGEND' => $user->lang ['JSM_TINYSEGMENTER_FILE'],
				'LEGEND_EXPLAIN' => $user->lang ['JSM_TINYSEGMENTER_FILE_EXPLAIN'] 
		) );
		
		if (! file_exists ( PHPBB_JAPANESESEARCHMOD_ROOT_PATH . 'JapaneseIndexer/TinySegmenter/tiny_segmenter.php' )) {
			$result = '<strong style="color:red">' . $user->lang ['JSM_NOT_FOUND'] . '</strong>';
		} else {
			$result = '<strong style="color:green">' . $user->lang ['JSM_FOUND'] . '</strong>';
		}
		
		$template->assign_block_vars ( 'checks', array (
				'TITLE' => $user->lang ['JSM_TINYSEGMENTER_FILEEXIST'],
				'TITLE_EXPLAIN' => $user->lang ['JSM_TINYSEGMENTER_FILEEXIST_EXPLAIN'],
				'RESULT' => $result,
				
				'S_EXPLAIN' => true,
				'S_LEGEND' => false 
		) );
	}
}