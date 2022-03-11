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

namespace dokuwiki\lib\plugins\safehtmltags\inc;

use dokuwiki\Extension as ext;

if(!defined('DOKU_INC'))
	die();	// 必须在 Dokuwiki 下运行 · Must be run within Dokuwiki

require_once(__DIR__ . '/HtmlTagUtils.php');
require_once(__DIR__ . '/GeneralSyntax.php');	// 注：加载顺序不可颠倒 · Note: the load order matters

class inc_plugin_safehtmltags extends ext\Plugin{

}