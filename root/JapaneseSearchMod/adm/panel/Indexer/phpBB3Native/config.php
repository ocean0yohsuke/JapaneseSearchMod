<?php
class phpBB3_JapaneseSearchModAdmPanel_Indexer_phpBB3Native_config extends phpBB3_JapaneseSearchModAdmPanel_Base {
	function main() {
		global $template, $user;
		
		$template->assign_vars ( array (
				'TITLE' => $user->lang ['JSM_PHPBB3NATIVE_CONFIG_TITLE'],
				'TITLE_EXPLAIN' => $user->lang ['JSM_PHPBB3NATIVE_CONFIG_TITLE_EXPLAIN'] 
		) );
		
		$submit = (isset ( $_POST ['submit'] )) ? true : false;
		
		$form_key = 'phpBB3Native_config';
		add_form_key ( $form_key );
		
		/**
		 * Validation types are:
		 * string, int, bool,
		 * script_path (absolute path in url - beginning with / and no trailing slash),
		 * rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		 */
		$display_vars = array (
				'vars' => array (
						'legend1' => 'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI',
						'wakachigaki_level' => array (
								'lang' => 'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL',
								'validate' => 'string',
								'type' => 'select',
								'object' => $this,
								'method' => 'build_cfg_template_select_wakachigaki_level',
								'explain' => true 
						) 
				) 
		);
		
		$old_config = $this->get_config ();
		$new_config = (isset ( $_REQUEST ['config'] )) ? utf8_normalize_nfc ( request_var ( 'config', array (
				'' => '' 
		), true ) ) : array ();
		$config = (sizeof ( $new_config )) ? $new_config : $old_config;
		
		$form_error = $notice = array ();
		
		// We validate the complete config if whished
		validate_config_vars ( $display_vars ['vars'], $config, $form_error );
		
		if ($submit && ! check_form_key ( $form_key )) {
			$form_error [] = $user->lang ['FORM_INVALID'];
		}
		
		if ($submit) {
			if (sizeof ( $form_error )) {
				$template->assign_vars ( array (
						'S_ERROR' => true,
						'ERROR_MSG' => implode ( '<br />', $form_error ) 
				) );
			} else {
				$notice [] = $user->lang ['JSM_CONFIG_SUCCESSED'];
				$template->assign_vars ( array (
						'S_NOTICE' => true,
						'NOTICE_MSG' => implode ( '<br />', $notice ) 
				) );
			}
			$this->set_config ( $config );
		}
		
		// Output relevant page
		foreach ( $display_vars ['vars'] as $config_key => $vars ) {
			if (! is_array ( $vars ) && strpos ( $config_key, 'legend' ) === false) {
				continue;
			}
			
			if (strpos ( $config_key, 'legend' ) !== false) {
				$template->assign_block_vars ( 'options', array (
						'S_LEGEND' => true,
						'LEGEND' => (isset ( $user->lang [$vars] )) ? $user->lang [$vars] : $vars 
				) );
				continue;
			}
			
			$type = explode ( ':', $vars ['type'] );
			
			$l_explain = '';
			if ($vars ['explain'] && isset ( $vars ['lang_explain'] )) {
				$l_explain = (isset ( $user->lang [$vars ['lang_explain']] )) ? $user->lang [$vars ['lang_explain']] : $vars ['lang_explain'];
			} else if ($vars ['explain']) {
				$l_explain = (isset ( $user->lang [$vars ['lang'] . '_EXPLAIN'] )) ? $user->lang [$vars ['lang'] . '_EXPLAIN'] : '';
			}
			
			$content = build_cfg_template ( $type, $config_key, $config, $config_key, $vars );
			
			if (empty ( $content )) {
				continue;
			}
			
			$template->assign_block_vars ( 'options', array (
					'KEY' => $config_key,
					'TITLE' => (isset ( $user->lang [$vars ['lang']] )) ? $user->lang [$vars ['lang']] : $vars ['lang'],
					'S_EXPLAIN' => $vars ['explain'],
					'TITLE_EXPLAIN' => $l_explain,
					'CONTENT' => $content 
			) );
			
			unset ( $display_vars ['vars'] [$config_key] );
		}
	}
	private function set_config($this_config) {
		global $cache;
		
		set_config ( 'JapaneseSearchMod_phpBB3Native_wakachigaki_level', $this_config ['wakachigaki_level'] );
		$cache->destroy ( 'config' );
	}
	private function get_config() {
		global $config;
		
		$this_config = array (
				'wakachigaki_level' => $config ['JapaneseSearchMod_phpBB3Native_wakachigaki_level'] 
		);
		
		return $this_config;
	}
	
	/**
	 * Required chars in passwords
	 */
	function build_cfg_template_select_wakachigaki_level($selected_value) {
		global $user;
		
		$type_ary = array (
				0 => 'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_0',
				1 => 'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_1',
				2 => 'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_2',
				3 => 'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_3',
				4 => 'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_4' 
		);
		$char_options = '';
		foreach ( $type_ary as $value => $type ) {
			$selected = ($selected_value == $value) ? ' selected="selected"' : '';
			$char_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang [$type] . '</option>';
		}
		
		return $char_options;
	}
}
