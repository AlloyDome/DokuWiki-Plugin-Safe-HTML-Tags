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
	public const START_TAG_REGEX_MODE 						= 1;
	public const START_TAG_NO_ATTRIBUTES_REGEX_MODE			= 2;
	public const END_TAG_REGEX_MODE 						= 3;
	public const SELF_CLOSLING_TAG_REGEX_MODE 				= 4;
	public const SELF_CLOSLING_TAG_NO_ATTRIBUTES_REGEX_MODE	= 5;

	private const START_TAG_RENDERING_MODE		= 1;
	private const SELF_CLOSLING_RENDERING_MODE	= 2;

	private const LAST_TOKEN_ATTRIBUTE_NAME		= 1;
	private const LAST_TOKEN_EQUAL				= 2;
	private const LAST_TOKEN_ATTRIBUTE_VALUE	= 3;
	private const LAST_TOKEN_TAG_START			= 4;
	private const LAST_TOKEN_TAG_END			= 5;

	public static function tagRegex($tagName, $mode = self::SELF_CLOSLING_TAG_REGEX_MODE) {
		$tagName = self::removeCharEntities($tagName);

		switch ($mode) {
			case self::START_TAG_REGEX_MODE:
			case self::SELF_CLOSLING_TAG_REGEX_MODE: {
				$regex = '<' . $tagName . '\s+?[^>/]*?' /* '(\s+?[^>"\'/=]+?(\s*?=\s*?("[^"]*?"|\'[^\']*?\'|[^>"\']*?|[^>"\'/=]+?))*?)*?\s*?' */ ;
					// FIXME:	1、注释掉的表达式无法使用，我也不知道为啥，可能是因为 DokuWiki 会将子表达式的 “(”、“)” 转义
					// 			2、注释掉的正则表达式会匹配到连等的属性，如 “attribute="1"="2"”
				if ($mode === self::START_TAG_REGEX_MODE) {
					return $regex . '>(?=.*?</' . $tagName . '>)';
				} else {
					return $regex . '\s*?/>';	// TODO: 后续加入省略 “/” 的写法
				}
			} case self::START_TAG_NO_ATTRIBUTES_REGEX_MODE: 
			case self::SELF_CLOSLING_TAG_NO_ATTRIBUTES_REGEX_MODE: {
				$regex = "<$tagName";
				if ($mode === self::START_TAG_NO_ATTRIBUTES_REGEX_MODE) {
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
		return str_replace(array('<', '>', '/', '&', ' ', ';', '"', '\'', '='), '', $s);
	}

	public static function tagAttributeTidier($s, $tagName = false, $mode = false) {
		$tokens = self::tagAttributeLexer($s);
		$instructions = self::tagAttributeParser($tokens);
		return self::tagAttributeRenderer($instructions, $tagName, $mode);
	}

	/**
	 * 该函数不能对闭标签作解析
	 */
	private static function tagAttributeLexer($s) {
		$isInSingleQuote = false;
		$isInDoubleQuote = false;
		$specialChars = array(' ', '=', '"', '\'', '<', '>', '/>');

		preg_replace('/\s+?/', ' ', $s = trim($s));	// 去除两侧空格，并将连续空格截短
		
		$tokens = array();

		$reducedS = $s;
		while ($reducedS != '') {
			$poses = array();
			foreach ($specialChars as $specialChar) {
				switch ($specialChar) {
					case '"': {
						$findResult = preg_match('/"[^"]*?"/', $reducedS, $match, PREG_OFFSET_CAPTURE);
						if ($findResult != 0) {
							$poses[$match[0][1]] = $match[0][0];
						}
						break;
					} case '\'': {
						$findResult = preg_match('/\'[^\']*?\'/', $reducedS, $match, PREG_OFFSET_CAPTURE);
						if ($findResult != 0) {
							$poses[$match[0][1]] = $match[0][0];
						}
						break;
					} default: {
						$pos = strpos($reducedS, $specialChar);
						if ($pos !== false) {
							$poses[$pos] = $specialChar;
						}
					}
				}
			}
			if (empty($poses)) {
				$tokens[] = array('s', $reducedS);
				break;
			} else {
				ksort($poses);
				$firstSpecialChar = array(key($poses), reset($poses));

				$unmatchedS = substr($reducedS, 0, $firstSpecialChar[0]);
				$reducedS = substr($reducedS, $firstSpecialChar[0] + strlen($firstSpecialChar[1]));
				if ($unmatchedS != '') {
					$tokens[] = array('s', $unmatchedS);
				}
				if ($firstSpecialChar[1] != ' ') {
					switch (substr($firstSpecialChar[1], 0, 1)) {
						case '"': 
						case '\'': {
							$tokens[] = array('s', substr($firstSpecialChar[1], 1, -1));
							break;
						} default: {
							$tokens[] = array($firstSpecialChar[1], $firstSpecialChar[1]);
							break;
						}
					}
				}
			}
		}

		return $tokens;
	}

	private static function tagAttributeParser($tokens) {
		$instructions = array(array('tagMode' => false, 'attributeCount' => 0));
		$insCount = 0;
		$lastTokenType = false;
		$findTagEnd = false;
		foreach ($tokens as /* $order => */ $token) {
			if ($token[0] == '>') {
				$instructions[0]['tagMode'] = self::START_TAG_RENDERING_MODE;
				break;
			} elseif ($token[0] == '/>') {
				$instructions[0]['tagMode'] = self::SELF_CLOTHING_TAG_RENDERING_MODE;
				break;
			} elseif ($token[0] == '=') {
				if ($lastTokenType == self::LAST_TOKEN_ATTRIBUTE_NAME) {
					$lastTokenType = self::LAST_TOKEN_EQUAL;
				} else {
					$lastTokenType = false;
				}
			} elseif ($token[0] == 's') {
				if (
					$lastTokenType === false 
					|| $lastTokenType == self::LAST_TOKEN_ATTRIBUTE_NAME 
					|| $lastTokenType == self::LAST_TOKEN_TAG_START
					|| $lastTokenType == self::LAST_TOKEN_ATTRIBUTE_VALUE
				) {
					$insCount++;
					$instructions[$insCount] = array(0 => $token[1], 1 => '');
					$lastTokenType = self::LAST_TOKEN_ATTRIBUTE_NAME;
				} elseif ($lastTokenType == self::LAST_TOKEN_EQUAL) {
					$instructions[$insCount][1] = $token[1];
					$lastTokenType = self::LAST_TOKEN_ATTRIBUTE_VALUE;
				}
			}
		}

		$instructions[0]['attributeCount'] = $insCount;

		if ($instructions[0]['tagMode'] === false) {
			$instructions[0]['tagMode'] = self::SELF_CLOTHING_TAG_RENDERING_MODE;
		}

		return $instructions;
	}

	private static function tagAttributeRenderer($instructions, $specifiedTagName = false, $mode = false) {
		if ($mode == false) {
			$mode = $instructions[0]['tagMode'];
		}

		if ($specifiedTagName == false || $specifiedTagName == '') {
			if ($instructions[0]['attributeCount'] >= 1) {
				$tagName = self::removeCharEntities($instructions[1][0]);
				if ($tagName == '') {
					$tagName = false;
					return '';
				}
			} else {
				$tagName = false;
				return '';
			}
		} else {
			$tagName = $specifiedTagName;
		}

		if ($instructions[0]['attributeCount'] == 0) {
			if ($mode == self::START_TAG_RENDERING_MODE) {
				return "<$tagName>";
			} elseif ($mode == self::SELF_CLOTHING_TAG_RENDERING_MODE) {
				return "<$tagName />";
			} else {
				return '';
			}
		} else {
			$tagText = "<$tagName";
			foreach ($instructions as $order => $instruction) {
				if ($order > 1 && self::removeCharEntities($instruction[0]) != '') {
					$tagText .= ' ' . self::removeCharEntities($instruction[0]);
					if ($instruction[1] != '') {
						$tagText .= '="' . htmlentities($instruction[1]) . '"';
					}
				}
			}

			if ($mode == self::START_TAG_RENDERING_MODE) {
				$tagText .= '>';
			} else {
				$tagText .= '/>';
			}

			return $tagText;
		}
	}

	public static function replaceSyntaxSugar($s) {
		// TODO: 将 “#”、“.”、“:” 开头的属性替换成 id、class、lang（以后再写）
	}

}