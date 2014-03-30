<?php
if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
if (empty($lang_postfix) || !is_array($lang_postfix))
{
	$lang_postfix = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
/*
 * Common
 */
'JSM_MODTITLE'		=> 'JapaneseSearchMod',
'JSM_NOT_SETUP_YET' 		=> 'JapaneseSearchMod %s がまだセットアップされていないため、このアクションを実行することはできません',
'JSM_FOUND'			=> '見つかりました',
'JSM_NOT_FOUND'		=> '見つかりません',
'JSM_AVAILABLE'		=> '利用できます',
'JSM_NOT_AVAILABLE'		=> '利用できません',
'JSM_SUCCESSED'		=> '成功しました',
'JSM_FAILED'			=> '失敗しました',
'JSM_CONFIG_SUCCESSED'	=> 'コンフィグの更新に成功しました',

/*
 * Warning
 */
'JSM_WARNING_CONTAIN_SPECIALCHARS' 	=> '次の文字列は不正です : %s <br />()| を含む文字を検索することはできません',
'JSM_WARNING_CONTAIN_MINUSCHAR_IN_BRAKET' => '次の文字列は不正です : %s <br />OR検索 内でキーワードの先頭に - を置くことはできません',
 
/*
 * AdminCP
 */
'JSM_FULLTEXTNATIVEJA_NOT_SELECTED_YET'	=> 'Fulltext native ja のコンフィグを設定するには上にある設定オプション "検索バックエンド" を "Fulltext native ja"　に指定して送信ボタンをクリックしてください。JapaneseSearchMod %1$s について詳しくは %2$sコチラ%3$s をクリックしてください。',
'JSM_FULLTEXTNATIVEJA_NOT_SETUP_YET'	=> 'JapaneseSearchMod %1$s のセットアップがまだ完了していません。<span style="color: red;">セットアップが完了するまで記事の 投稿/検索 はできません</span>。セットアップを完了するには %2$sコチラ%3$s をクリックしてください。',

'JSM_FULLTEXTNATIVEJA_INDEX_ENGINE'		=> 'インデクスエンジン',
'JSM_FULLTEXTNATIVEJA_INDEX_ENGINE_EXPLAIN'	=> 'インデクス処理を行うエンジンを選択します。詳しくは %sコチラ%s をクリックしてください。<br />インデクスエンジンを変更した場合はインデクスを再構築してください。',
'JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE'		=> '検索一致タイプ',
'JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE_FULL'	=> '完全一致',
'JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE_PARTIAL'	=> '部分一致',
'JSM_FULLTEXTNATIVEJA_CANONICAL_TRANSFORMATION'	=> '文字を正準化する',
'JSM_FULLTEXTNATIVEJA_CANONICAL_TRANSFORMATION_EXPLAIN'	=> 'インデクス作成時と検索処理時に文字を正準化します。<br /> 半角ｶﾀｶﾅ は 全角カタカナ に、全角ａｌｐｈａｎｕｍｅｒｉｃ は 半角alphanumeric に、大文字ALPHABET は 小文字alphabet に自動的に変換されます。検索精度は向上しますが、サーバの CPU負荷 も増大します。',
'JSM_FULLTEXTNATIVEJA_SEARCH_MATCH_TYPE_EXPLAIN'	=> '<b>完全一致</b> : 検索キーワードに完全に一致するインデクスのみ検索にヒットします。<br /><b>部分一致</b> : 部分一致するインデクスは全て検索にヒットします。例えば検索キーワードが "サーチ" の場合、 "サーチエンジン" や "ファンサーチ" といったインデクスもヒットします。検索精度は非常に向上しますが、サーバの CPU負荷 も増大します。',
'JSM_FULLTEXTNATIVEJA_MIN_SEARCH_CHARS_EXPLAIN'	=> 'この数より小さい字数のキーワードはインデクスの対象となりません。ただし漢字を１つでも含むキーワードは必ずインデクス化されます。',
'JSM_FULLTEXTNATIVEJA_MAX_SEARCH_CHARS_EXPLAIN'	=> 'この数を超える字数のキーワードはインデクスの対象となりません。ただし漢字を１つでも含むキーワードは必ずインデクス化されます。',

/*
 * Panel Common
 */
'JSM_PANEL_LOGIN_ADMIN_CONFIRM'	=> 'JapaneseSearachMOD のコントロールパネルへ入室するには先に Administration Control Panel にログインしておく必要があります',
'JSM_PANEL_INVALID_PANEL_SPECIFIED'=> '指定されたパネルが不正です', 

/*
 * Setup Panel 
 */
'JSM_SETUPPANEL_TITLE'		=> 'Setup Panel',
'JSM_SETUPPANEL_OVERVIEW'		=> 'overview',
'JSM_SETUPPANEL_SETUP'		=> 'setup',
'JSM_SETUPPANEL_UNSETUP'		=> 'unsetup',
'JSM_SETUPPANEL_SETUP_DONE'		=> 'JapaneseSearchMod %s のセットアップは完了しています',
'JSM_SETUPPANEL_SETUP_NOT_DONE'	=> 'JapaneseSearchMod %s のセットアップが完了していません',
'JSM_SETUPPANEL_INDEXER_ALTERED'	=> 'インデクサ %1$s を利用できないため、インデクサを %2$s に指定しました',
'JSM_SETUPPANEL_CONFIGKEY_ADDED'	=> 'コンフィグデータ %s を追加しました',	
'JSM_SETUPPANEL_CONFIGKEY_UPDATED'	=> 'コンフィグデータ %1$s の値を %2$s に更新しました',
'JSM_SETUPPANEL_CONFIGKEY_DELETED'	=> 'コンフィグデータ  %s を削除しました',	
// Overview
'JSM_SETUPPANEL_OVERVIEW_INTRO'	=> 'はじめに',
// Setup
'JSM_SETUPPANEL_SETUP_INTRO'	=> '導入',
'JSM_SETUPPANEL_SETUP_RUN'		=> '実行',
'JSM_SETUPPANEL_SETUP_SUCCESSED'	=> 'JapaneseSearchMod %s のセットアップに成功しました',
// Unsetup
'JSM_SETUPPANEL_UNSETUP_INTRO'	=> '導入',
'JSM_SETUPPANEL_UNSETUP_RUN'	=> '実行',
'JSM_SETUPPANEL_UNSETUP_SUCCESSED'	=> 'JapaneseSearchMod %s のアンセットアップに成功しました',


/*
 * Indexer Panel 
 */
'JSM_INDEXERPANEL_TITLE'		=> 'Indexer Panel',
'JSM_INDEXERPANEL_OVERVIEW'		=> 'overview',
'JSM_INDEXERPANEL_PHPBB3NATIVE'	=> 'phpBB3Native',
'JSM_INDEXERPANEL_TINYSEGMENTER'	=> 'TinySegmenter',
'JSM_INDEXERPANEL_MECAB'		=> 'MeCab',
// Overview
'JSM_INDEXERPANEL_OVERVIEW_INTRO'		=> 'はじめに',
'JSM_INDEXERPANEL_OVERVIEW_INDEXTEST'	=> 'インデクステスト',
'JSM_INDEXERPANEL_OVERVIEW_SEARCHTEST'	=> '検索テスト',
	// Index test
	'JSM_INDEXTEST_TITLE'		=> 'インデクステスト',
	'JSM_INDEXTEST_TITLE_EXPLAIN'	=> 'ここでは利用可能な各インデクサが実際にどのように文章をインデクスするか確認できます。テキストエリアに試したい文章を入力して送信ボタンをクリックしてください。インデクスされる各ワードが半角スペースで区切られて表示されます。',
	'JSM_INDEXTEST_MICROSECONDS'	=> 'マイクロ秒',
	// Search test
	'JSM_SEARCHTEST_TITLE'		=> '検索テスト',
	'JSM_SEARCHTEST_TITLE_EXPLAIN'	=> 'ここでは検索ワードに一致するインデクスがどのインデクスなのかを実際に確認できます。実際の検索時に於いては入力された検索キーワードは分かち書き等の前処理が行われて検索されますが、ここでは入力した検索ワードが前処理されずにそのまま検索される点にご注意ください。',
// phpBB3Native
'JSM_INDEXERPANEL_PHPBB3NATIVE_INTRO'	=> 'はじめに',
'JSM_INDEXERPANEL_PHPBB3NATIVE_CONFIG'	=> 'コンフィグ',
	// Config
	'JSM_PHPBB3NATIVE_CONFIG_TITLE'		=> 'phpBB3Native コンフィグ',
	'JSM_PHPBB3NATIVE_CONFIG_TITLE_EXPLAIN'	=> 'ここでは phpBB3Native のコンフィグを設定できます。設定を変更した場合はインデクスを再構築してください。',
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI'		=> '分かち書き',	
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL'	=> '分かち書きレベル',
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_EXPLAIN'	=> '低いレベルであればあるほど部分一致検索時に検索精度は上がりますが、逆に完全一致検索時には下がります。各レベルはそれより低いレベルの条件を含みます。<br />新しい分かち書きレベルを過去に投稿された記事に対して適用するにはインデクスを再構築する必要があります。',
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_0'	=> 'レベル０： 句読点 で分割する',
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_1'	=> 'レベル１： 連続する ひらがな で分割する',
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_2'	=> 'レベル２： 連続する 英数字 で分割する',
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_3'	=> 'レベル３： 連続する カタカナ で分割する',
	'JSM_PHPBB3NATIVE_CONFIG_WAKACHIGAKI_LEVEL_4'	=> 'レベル４： 連続する 漢字 で分割する',
// MeCab
'JSM_INDEXERPANEL_MECAB_INTRO'		=> 'はじめに',
'JSM_INDEXERPANEL_MECAB_CONFIG'		=> 'コンフィグ',
'JSM_INDEXERPANEL_MECAB_SETUP' 		=> 'セットアップ',
	// Config
	'JSM_MECAB_CONFIG_TITLE'		=> 'MeCab コンフィグ',
	'JSM_MECAB_CONFIG_TITLE_EXPLAIN'	=> 'ここでは MeCab のコンフィグを設定できます。設定を変更した場合はインデクスを再構築してください。',
	'JSM_MECAB_CONFIG_WAKACHIGAKI'			=> '分かち書き',	
	'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL'		=> '分かち書きレベル',
	'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_EXPLAIN'	=> '低いレベルであればあるほど部分一致検索時に検索精度は上がりますが、逆に完全一致検索時には下がります。各レベルはそれより低いレベルの条件を含みます。<br />新しい分かち書きレベルを過去に投稿された記事に対して適用するにはインデクスを再構築する必要があります。',
	'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_MEISHI'	=> 'レベル１： 名詞で分割する',
	'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_JOSHI'	=> 'レベル２： 助詞で分割する',
	'JSM_MECAB_CONFIG_WAKACHIGAKI_LEVEL_MECABDEFAULT'=> 'レベル３： あらゆる品詞で分割する（MeCabデフォルト）',
	'JSM_MECAB_CONFIG_RENZOKU_HINSHI'			=> '連続する品詞を繋げる',
	'JSM_MECAB_CONFIG_RENZOKU_HINSHI_EXPLAIN'		=> '同じ品詞が連続する場合、それらを１つのまとまりとみなします。名詞を分かち書きする際に複合名詞で分かち書きしたい場合は “はい” にしてください。',
	'JSM_MECAB_CONFIG_ONLY_MEISHI'			=> '名詞だけ取り出す',
	'JSM_MECAB_CONFIG_ONLY_MEISHI_EXPLAIN'		=> '"はい" にした場合、名詞だけ取り出され、それ以外の品詞はすべて捨てられます',
	// Setup
	'JSM_MECAB_SETUP_TITLE'		=> 'MeCab セットアップ',
	'JSM_MECAB_SETUP_TITLE_EXPLAIN'	=> 'ここでは MeCab をセットアップできます。MeCab は “PHP拡張モジュール経由” または “コマンドライン経由” で動作します。“PHP拡張モジュール経由” または “コマンドライン経由” のどちらか一方を設定し、“更新” ボタンをクリックしてください。もし両方設定して両方とも利用できる場合、PHP拡張モジュール経由 で MeCab は動作します。',
	'JSM_MECAB_SETUP_TEST'		=> '動作テスト',
	'JSM_MECAB_SETUP_EXT' 		=> 'PHP拡張モジュール経由',
	'JSM_MECAB_SETUP_EXT_EXPLAIN' 	=> 'PHP拡張モジュール mecab がサポートされていて MeCab を利用できる場合はこちらを設定してください。もし “MeCab辞書の文字コード” が utf-8 でない場合、PHP拡張モジュール mbstring がサポートされている必要があります。',
	'JSM_MECAB_SETUP_EXT_ENCODING'	=> 'MeCab辞書の文字コード',
	'JSM_MECAB_SETUP_EXT_ENCODING_EXPLAIN'	=> 'eg. utf-8, euc-jp, shift_jis',
	'JSM_MECAB_SETUP_EXT_DICDIR'		=> 'MeCab辞書パス',
	'JSM_MECAB_SETUP_EXT_DICDIR_EXPLAIN'	=> 'eg. /usr/local/lib/mecab/dic/ipadic',
	'JSM_MECAB_SETUP_CLI' 						=> 'コマンドライン経由',
	'JSM_MECAB_SETUP_CLI_EXPLAIN' 		=> 'コマンドライン経由で MeCab を利用できる場合はこちらを設定してください。もし “MeCab辞書の文字コード” が utf-8 でない場合、PHP拡張モジュール mbstring がサポートされている必要があります。コマンドライン経由では PHP の proc_open 関数を利用して MeCab にアクセスしているため、proc_open 関数の利用が禁止されている場合はコマンドライン経由を利用できません。またもし PHP設定ディレクティブ safe_mode が On に設定されている場合、PHP設定ディレクティブ safe_mode_exec_dir に MeCab 実行パスのディレクトリが指定されていない場合もコマンドライン経由を利用できません。',
	'JSM_MECAB_SETUP_CLI_ENCODING'		=>'MeCab辞書の文字コード',
	'JSM_MECAB_SETUP_CLI_ENCODING_EXPLAIN'	=>'eg. utf-8, euc-jp, shift_jis',
	'JSM_MECAB_SETUP_CLI_EXEPATH'		=>'MeCab実行パス',
	'JSM_MECAB_SETUP_CLI_EXEPATH_EXPLAIN'	=>'eg. /usr/local/mecab, /usr/local/bin/mecab, /usr/local/php/bin/mecab, C:/MeCab/bin/mecab.exe',
	'JSM_MECAB_SETUP_SUCCESSED'			=> '設定値の更新に成功しました',
	'JSM_MECAB_SETUP_FAILED'			=> '設定値の更新に失敗しました',
	'JSM_MECAB_SETUP_SUBMIT'			=> '更新',
	'JSM_MECAB_SETUP_SUBMIT_EXPLAIN'		=> 'もし正しい設定値がどうしても分からない場合、 “自動更新” を利用してみてください。PHP プログラムが設定値の割り出しを試み、割り出しに成功すれば設定値が自動的に作成されます。',
	'JSM_MECAB_SETUP_SUBMIT_AUTO'		=> '自動更新',
	
// TinySegmenter
'JSM_INDEXERPANEL_TINYSEGMENTER_INTRO'	=> 'はじめに',
'JSM_INDEXERPANEL_TINYSEGMENTER_SETUP' 	=> 'セットアップ',
//　Setup
	'JSM_TINYSEGMENTER_TITLE'		=> 'TinySegmenter セットアップ',
	'JSM_TINYSEGMENTER_TITLE_EXPLAIN'	=> 'TinySegmenter エンジンを利用するには下に表示される条件を満たす必要があります。条件を満たすまで TinySegmenter エンジンを利用することはできません。',
	'JSM_TINYSEGMENTER_FILE'		=> 'ファイル',
	'JSM_TINYSEGMENTER_FILE_EXPLAIN'	=> 'TinySegmenter エンジンを利用するには  JapaneseSearchMod/JapaneseIndexer/TinySegmenter ディレクトリ直下にファイル tiny_segmenter.php が存在している必要があります',
	'JSM_TINYSEGMENTER_FILEEXIST'	=> 'tiny_segmenter.php',
	'JSM_TINYSEGMENTER_FILEEXIST_EXPLAIN'	=> '',

/*
 * Error Message
 */
// TinySegmenter
'JSM_TINYSEGMENTER_ERROR_COULD_NOT_FIND_CLASSFILE'	=> '[TinySegmenter] Could not find class file tiny_segmenter.php in the TinySegmenter directory.',
'JSM_TINYSEGMENTER_ERROR_COULD_NOT_FIND_CLASS'		=> '[TinySegmenter] Could not find class TinySegmenter in tiny_segmenter.php.',
// MeCab
'JSM_MECAB_ERROR_CONFIG_ENCODING_EMPTY'		=> '[MeCab] Encoding value in the configuration file is empty.',
'JSM_MECAB_ERROR_MBSTRING_UNSUPPORTED'		=> '[MeCab] PHP extension mbstring is not supported.',
'JSM_MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_EXT'		=> '[MeCab] Failed to do wakachigaki with Ext mode.',
'JSM_MECAB_ERROR_WAKACHIGAKI_FAILED_WITH_CLI'		=> '[MeCab] Failed to do wakachigaki with CLI mode.',
'JSM_MECAB_ERROR_WAKACHIGAKI_FAILED_BY_WRONG_ENCODING'	=> '[MeCab] Failed to do wakachigaki by wrong encoding.',
'JSM_MECAB_ERROR_WAKACHIGAKI_FAILED_BY_STDERR'	=> '[MeCab] Failed to do wakachigaki.',
'JSM_MECAB_ERROR_NOT_AVAILABLE_BOTH_MODE'		=> '[MeCab] Both modes are not available.',
'JSM_MECAB_ERROR_COULDNOT_FIND_CONFIG_SETTINGS'	=> '[MeCab] Could not find any configuration settings with both modes.',
'JSM_MECAB_ERROR_EXTENSION_MECAB_UNSUPPORTED'	=> '[MeCab] PHP extension mecab is not supported.',
'JSM_MECAB_ERROR_MECABFUNC_MECABNEW_UNSUPPORTED' => '[MeCab] MeCab function mecab_new() is not supported.',
'JSM_MECAB_ERROR_FUNC_PROCOPEN_UNSUPPORTED'	=> '[MeCab] Function proc_open() is not supported.',

));

$lang_postfix = array_merge($lang_postfix, array(
'SEARCH_KEYWORDS_EXPLAIN'  => '<br /> 英数字カタカナは 大文字/小文字 と 全角/半角 の区別があります。',
));
