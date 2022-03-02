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

use dokuwiki\lib\plugins\safehtmltags\inc as inc;


if(!defined('DOKU_INC'))
	die();	// 必须在 Dokuwiki 下运行 · Must be run within Dokuwiki

if (!defined('DOKU_LF'))
	define('DOKU_LF', "\n");
if (!defined('DOKU_TAB'))
	define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN'))
	define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

abstract class syntax_plugin_GeneralSyntax extends DokuWiki_Syntax_Plugin {
	public $tagName;	// TODO: 后续应当加入非标准的标签，如 WRAP（输出 HTML 时实际上是 div）
	public $isCouple;
	public $isSingle;	// TODO: 可能可以改成 protected

	public function getSort() {
		return 195;
	}

	public function connectTo($mode) {
		if ($this->isSingle == true) {
			$this->Lexer->addSpecialPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::SELF_CLOSLING_TAG_REGEX_MODE), 
				$mode, 
				'plugin_safehtmltags_' . $this->getPluginComponent()
			);
		}
		if ($this->isCouple == true) {
			$this->Lexer->addEntryPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::START_TAG_REGEX_MODE), 
				$mode, 
				'plugin_safehtmltags_' . $this->getPluginComponent());
		}
	}

	public function postConnect() {
		if ($this->isCouple == true) {
			$this->Lexer->addExitPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::END_TAG_REGEX_MODE), 
				'plugin_safehtmltags_' . $this->getPluginComponent()
			);
		}
	}

	public function handle($match, $state, $pos, Doku_Handler $handler){
		return $match;
	}

	public function render($mode, Doku_Renderer $renderer, $data) {
		if ($mode != 'xhtml') {
			return false;
		}
        if (!$data) {
			return false;
		}
		$renderer->doc .= $data;
		return true;
	}
}