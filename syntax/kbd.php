<?php
/**
 * DokuWiki safehtmltags 插件 · DokuWiki Plugin Safe HTML Tags
 *
 * @license	MIT License
 * @author	AlloyDome
 * 
 * @since	1.0.0 (220311)
 * @version	1.0.0 (220311)
 */

if(!defined('DOKU_INC'))
	die('It works!');	// 必须在 Dokuwiki 下运行 · Must be run within Dokuwiki

require_once(__DIR__ . '/../inc/init.php');

class syntax_plugin_safehtmltags_kbd extends syntax_plugin_GeneralSyntax {
	protected $tagName = 'kbd';
	protected $isCouple = true;
	protected $isSingle = false;

	public function getPType() {
		return 'normal';
	}
}