<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Helper;

class Html
{
	public static function stripWhitespace($content,$stripStr,$replaceStr,$stripStrNum=1,$trimLeft=false,$trimRight=true)
	{
		$stripStrs = '';
		for($i=0;$i<$stripStrNum;$i++)
		{
			$stripStrs .= $stripStr;
		}
		return str_replace($stripStrs,$replaceStr,$content);
	}
	
	public static function encode($text)
	{
		return htmlspecialchars($text,ENT_QUOTES,\Lightworx::getApplication()->charset);
	}
	
	public static function decode($text)
	{
		return htmlspecialchars_decode($text,ENT_QUOTES);
	}
	
	public static function createLink($link,$content,array $htmlOptions=array())
	{
		$htmlOptions['href'] = $link;
		return self::tag('a',$content,$htmlOptions);
	}
	
	/**
	 * Creates a custom tag
	 */
	public static function tag($name,$text='',array $options=array(),$closeTag=true)
	{
		$tag = '<'.$name.self::getHtmlOptions($options);
		if($closeTag===true)
		{
			$tag .= '>'.$text.'</'.$name.'>';
		}else{
			$tag .= ' />';
		}
		return $tag;
	}
	
	/**
	 * Convert the parameter key&value pairs to a string
	 * @param array $options
	 * @return string
	 */
	public static function getHtmlOptions(array $options)
	{
		$tagOptions = array();
		foreach($options as $option=>$value)
		{
			$tagOptions[] = $option.'="'.$value.'"';
		}
		return count($tagOptions)>0 ? ' '.implode(" ",$tagOptions) : '';
	}
}