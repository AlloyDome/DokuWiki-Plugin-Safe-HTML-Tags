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
	die();	// must be run within Dokuwiki · 必须在 DokuWiki 下运行

class syntax_plugin_safehtmltags_htmlComment extends DokuWiki_Syntax_Plugin {

	public function getType(){
		return 'substition';
	}

	public function getSort(){
		return 195;
	}

	public function connectTo($mode) {
		$this->Lexer->addSpecialPattern('<\!--.*?-->', $mode, 'plugin_safehtmltags_htmlComment');
	}

	public function handle($match, $state, $pos, Doku_Handler $handler) {
		if ($state == DOKU_LEXER_SPECIAL) {
			return array($state, trim(substr($match, 4, -3)));
		}	
	}

	public function render($format, Doku_Renderer $renderer, $data) {
		list($state, $match) = $data;
		if ($state == DOKU_LEXER_SPECIAL) {
			if ($format == 'xhtml') {
				$renderer->doc .= '<!-- * ' . $match . ' -->';
			}
			return true;
		}
		return false;
	}
}
