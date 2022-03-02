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

if(!defined('DOKU_INC'))
	die();	// 必须在 Dokuwiki 下运行 · Must be run within Dokuwiki

class HtmlTagUtils{
	public const START_TAG_REGEX_MODE 			= 1;
	public const END_TAG_REGEX_MODE 			= 2;
	public const SELF_CLOSLING_TAG_REGEX_MODE 	= 3;

	public static function tagRegex($tagName, $mode = self::SELF_CLOSLING_TAG_REGEX_MODE) {
		$tagName = self::removeCharEntities($tagName);

		switch ($mode) {
			case self::START_TAG_REGEX_MODE:
			case self::SELF_CLOSLING_TAG_REGEX_MODE: {
				$regex = '<' . $tagName . '[^>/]*?'/* '(\s+?[^>"\'/=]+?(\s*?=\s*?("[^"]*?"|\'[^\']*?\'|[^>"\']*?|[^>"\'/=]+?))*?)*?\s*?' */;
					// FIXME:	1、注释掉的表达式无法使用，我也不知道为啥，可能是因为 DokuWiki 会将子表达式的 “(”、“)” 转义
					// 			2、注释掉的正则表达式会匹配到连等的属性，如 “attribute="1"="2"”
				if ($mode === self::START_TAG_REGEX_MODE) {
					return $regex . '>(?=.*?</' . $tagName . '>)';
				} else {
					return $regex . '\s*?/>';	// TODO: 后续加入省略 “/” 的写法
				}
			} case self::END_TAG_REGEX_MODE: {
				return "</$tagName>";
			} default: {
				return '';
			}
		};
	}

	public static function removeCharEntities($s) {
		return str_replace(array('<', '>', '/', '&', ' ', ';', '"', '\''), '', $s);
	}

	public static function replaceSyntaxSugar($s) {
		// TODO: 将 “#”、“.”、“:” 开头的属性替换成 id、class、lang（以后再写）
	}
}