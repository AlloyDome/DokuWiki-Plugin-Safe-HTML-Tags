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

class syntax_plugin_GeneralSyntax extends DokuWiki_Syntax_Plugin {
	protected $tagName;
	protected $realTagName = false;
	protected $isCouple;
	protected $isSingle;

	public function __construct() {
		if ($this->realTagName == false) {
			$this->realTagName = $this->tagName;
		}
	}

	public function getSort() {
		return 195;
	}

	public function getAllowedTypes()
	{
		return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
	}

	public function getType() {
		return 'formatting';
	}

	public function accepts($mode) {
		if ($mode == substr(get_class($this), 7)) {
			return true;
		}
		return parent::accepts($mode);
	}

	public function connectTo($mode) {
		if ($this->isSingle == true) {
			$this->Lexer->addSpecialPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::SELF_CLOSING_TAG_REGEX_MODE), 
				$mode, 
				'plugin_safehtmltags_' . $this->getPluginComponent()
			);
			$this->Lexer->addSpecialPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::SELF_CLOSING_TAG_NO_ATTRIBUTES_REGEX_MODE), 
				$mode, 
				'plugin_safehtmltags_' . $this->getPluginComponent()
			);
		}
		if ($this->isCouple == true) {
			$this->Lexer->addEntryPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::START_TAG_REGEX_MODE), 
				$mode, 
				'plugin_safehtmltags_' . $this->getPluginComponent());
			$this->Lexer->addEntryPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::START_TAG_NO_ATTRIBUTES_REGEX_MODE), 
				$mode, 
				'plugin_safehtmltags_' . $this->getPluginComponent()
			);
		}
	}

	public function postConnect() {
		if ($this->isCouple == true) {
			$this->Lexer->addExitPattern(
				inc\HtmlTagUtils::tagRegex($this->tagName, inc\HtmlTagUtils::END_TAG_REGEX_MODE), 
				'plugin_safehtmltags_' . $this->getPluginComponent()
			);
			$this->Lexer->addPattern(
				'[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)', 
				'plugin_safehtmltags_' . $this->getPluginComponent()
			);
		}
	}

	public function handle($match, $state, $pos, Doku_Handler $handler) {
		switch ($state) {
			case DOKU_LEXER_EXIT: {
				return array($state, '</' . $this->realTagName . '>');
			} case DOKU_LEXER_ENTER: {
				return array(
					$state, 
					inc\HtmlTagUtils::tagAttributeTidier(
						$match, 
						$this->realTagName, 
						inc\HtmlTagUtils::START_TAG_RENDERING_MODE
					)
				);
			} case DOKU_LEXER_SPECIAL: {
				return array(
					$state, 
					inc\HtmlTagUtils::tagAttributeTidier(
						$match, 
						$this->realTagName, 
						inc\HtmlTagUtils::SELF_CLOSING_TAG_RENDERING_MODE
					)
				);
			} case DOKU_LEXER_UNMATCHED: {
				$handler->_addCall('cdata', array($match), $pos);
				break;
			} case DOKU_LEXER_MATCHED:{
				$title = trim($match);
				$level = 7 - strspn($title, '=');
				if($level < 1) {
					$level = 1;
				}
				$title = trim($title, '=');
				$title = trim($title);

				$handler->_addCall('header', array($title, $level, $pos), $pos);

				if ($title && $level <= $conf['maxseclevel']) {
					$handler->addPluginCall('wrap_closesection', array(), DOKU_LEXER_SPECIAL, $pos, '');
				}
			}
		}
	}

	public function render($mode, Doku_Renderer $renderer, $data) {
		list($state, $tidyTag) = $data;

		if ($mode != 'xhtml') {
			return false;
		}
        if (!$tidyTag) {
			return false;
		}

		switch ($state) {
			case DOKU_LEXER_EXIT:
			case DOKU_LEXER_ENTER:
			case DOKU_LEXER_SPECIAL: {
				$renderer->doc .= $tidyTag;
			}
		}	
		return false;
	}
}