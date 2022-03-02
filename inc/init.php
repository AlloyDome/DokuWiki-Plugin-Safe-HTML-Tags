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

namespace dokuwiki\lib\plugins\safehtmltags\inc;

use dokuwiki\Extension as ext;

if(!defined('DOKU_INC'))
	die();	// 必须在 Dokuwiki 下运行 · Must be run within Dokuwiki

require_once(__DIR__ . '/HtmlTagUtils.php');
require_once(__DIR__ . '/GeneralSyntax.php');	//（注：加载顺序不可颠倒）

class inc_plugin_safehtmltags extends ext\Plugin{

}