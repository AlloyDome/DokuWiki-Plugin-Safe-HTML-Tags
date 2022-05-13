<?php
/**
 * DokuWiki safehtmltags 插件 · DokuWiki Plugin Safe HTML Tags
 *
 * @license	MIT License
 * @author	AlloyDome
 * 
 * @since	1.0.0 (220311)
 * @version	1.0.2 (220513)
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
	protected $allowHtmlPrefix = true;
	protected $isCouple;
	protected $isSingle;

	protected $tagRegex;
	protected $pluginMode;

	public function __construct() {
		/* parent::__construct(); */

		if ($this->realTagName == false) {
			$this->realTagName = $this->tagName;
		} elseif ($this->realTagName != $this->tagName) {
			$this->allowHtmlPrefix = false;
		}

		$this->tagRegex = array();

		for ($i = 0; $i <= 1; $i++) {
			if ($i == 0 || ($i == 1 && $this->allowHtmlPrefix == true)){
				$this->tagRegex['selfClosing'][$i] = inc\HtmlTagUtils::tagRegex(
					$this->tagName, inc\HtmlTagUtils::SELF_CLOSING_TAG_REGEX_MODE, ($i == 1 ? true : false)
				); 	// 0: <tag attribute='value' ... />, 1: <html:tag attribute='value' />
				$this->tagRegex['selfClosingNoAttributes'][$i] = inc\HtmlTagUtils::tagRegex(
					$this->tagName, inc\HtmlTagUtils::SELF_CLOSING_TAG_NO_ATTRIBUTES_REGEX_MODE, ($i == 1 ? true : false)
				); 	// 0: <tag />, 1: <html:tag />
				$this->tagRegex['start'][$i] = inc\HtmlTagUtils::tagRegex(
					$this->tagName, inc\HtmlTagUtils::START_TAG_REGEX_MODE, ($i == 1 ? true : false)
				); 	// 0: <tag attribute='value' ...>, 1: <html:tag attribute='value' ...>
				$this->tagRegex['startNoAttributes'][$i] = inc\HtmlTagUtils::tagRegex(
					$this->tagName, inc\HtmlTagUtils::START_TAG_NO_ATTRIBUTES_REGEX_MODE, ($i == 1 ? true : false)
				); 	// 0: <tag>, 1: <html:tag>
				$this->tagRegex['end'][$i] = inc\HtmlTagUtils::tagRegex(
					$this->tagName, inc\HtmlTagUtils::END_TAG_REGEX_MODE, ($i == 1 ? true : false)
				); 	// 0: </tag>, 1: </html:tag>
			}
		}
		
		$this->pluginMode = 'plugin_safehtmltags_' . $this->getPluginComponent();
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
		if ($mode == substr(get_class($this), 7) /* || $mode == 'header' */ ) {
			return true;
		}
		return parent::accepts($mode);
	}

	public function connectTo($mode) {
		for ($i = 0; $i <= 1; $i++) {
			if ($i == 0 || ($i == 1 && $this->allowHtmlPrefix == true)) {
				if ($this->isSingle == true) {
					$this->Lexer->addSpecialPattern($this->tagRegex['selfClosing'][$i], $mode, $this->pluginMode . ($i == 1 ? '_htmlPrefixAlt' : ''));	// 0: <tag attribute='value' ... />, 1: <html:tag attribute='value' />
					$this->Lexer->addSpecialPattern($this->tagRegex['selfClosingNoAttributes'][$i], $mode, $this->pluginMode . ($i == 1 ? '_htmlPrefixAlt' : ''));	// 0: <tag />, 1: <html:tag />
				}
				if ($this->isCouple == true) {
					$this->Lexer->addEntryPattern($this->tagRegex['start'][$i], $mode, $this->pluginMode . ($i == 1 ? '_htmlPrefixAlt' : ''));	// 0: <tag attribute='value' ...>, 1: <html:tag attribute='value' ...>
					$this->Lexer->addEntryPattern($this->tagRegex['startNoAttributes'][$i], $mode, $this->pluginMode . ($i == 1 ? '_htmlPrefixAlt' : ''));	// 0: <tag>, 1: <html:tag>
				}
			}
		}
	}

	public function postConnect() {
		if ($this->isCouple == true) {
			for ($i = 0; $i <= 1; $i++) {
				if ($i == 0 || ($i == 1 && $this->allowHtmlPrefix == true)) {
					$this->Lexer->addExitPattern($this->tagRegex['end'][$i], $this->pluginMode . ($i == 1 ? '_htmlPrefixAlt' : ''));	// 0: </tag>, 1: </html:tag>
					if ($i == 1) {
						$this->Lexer->mapHandler($this->pluginMode . '_htmlPrefixAlt', $this->pluginMode);
							// "plugin_safehtmltags_***_htmlPrefixAlt" and "plugin_safehtmltags_***" should be processed in the same way
					}
				}
			}		
			$this->Lexer->addPattern('[ \t]*={2,}[^\n]+={2,}[ \t]*(?=\n)', $this->pluginMode);
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
				$header = trim($match);
				$level = 7 - strspn($header, '=');
				if($level < 1) {
					$level = 1;
				}
				$header = trim(trim($header, '='));

				$handler->_addCall('header', array($header, $level, $pos), $pos);
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