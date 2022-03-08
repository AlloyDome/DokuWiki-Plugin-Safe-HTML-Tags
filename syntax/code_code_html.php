<?php
/**
 * 
 *
 * @license	MIT License
 * @author	
 * 
 * @since	1.0.0, beta (------)
 * @version 1.0.0, beta (------)
 */

if(!defined('DOKU_INC'))
	die('It works!');	// 必须在 Dokuwiki 下运行 · Must be run within Dokuwiki

require_once(__DIR__ . '/../inc/init.php');

class syntax_plugin_safehtmltags_code_code_html extends syntax_plugin_GeneralSyntax {
	protected $tagName = 'code_html';
	protected $realTagName = 'code';
	protected $isCouple = true;
	protected $isSingle = false;

	public function getPType() {
		return 'normal';
	}
}