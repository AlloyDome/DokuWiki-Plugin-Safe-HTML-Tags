<?php
/**
 * DokuWiki safehtmltags 插件 · DokuWiki Plugin Safe HTML Tags
 *
 * @license	MIT License
 * @author	AlloyDome
 * 
 * @since	1.0.0 (220311)
 * @version	1.0.1 (220318)
 */

if(!defined('DOKU_INC'))
	die(); // must be run within Dokuwiki · 必须在 DokuWiki 下运行

class syntax_plugin_safehtmltags_htmlCharEntity extends DokuWiki_Syntax_Plugin {

	public function getType(){
		return 'substition';
	}

	public function getSort(){
		return 195;
	}

	public function connectTo($mode) {
		$this->Lexer->addSpecialPattern('&\w+?;', $mode, 'plugin_safehtmltags_htmlCharEntity');	// 实体名称 · Entity name
		$this->Lexer->addSpecialPattern('&#\d+?;', $mode, 'plugin_safehtmltags_htmlCharEntity');	// 实体编号（十进制） · Decimal
		$this->Lexer->addSpecialPattern('&#[x|X][\dA-Fa-f]+?;', $mode, 'plugin_safehtmltags_htmlCharEntity');	// 实体编号（十六进制） · Hexadecimal
	}

	public function handle($match, $state, $pos, Doku_Handler $handler) {
		return array($state, $match);
	}

	public function render($format, Doku_Renderer $renderer, $data) {
		list($state, $match) = $data;

		if ($state == DOKU_LEXER_SPECIAL) {
			switch ($format) {
				case 'xhtml': {
					$renderer->doc .= $match;
					return true;
				} case 'metadata': {
					if ($renderer->capture) {
						$renderer->doc .= $match;
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}
	
}
