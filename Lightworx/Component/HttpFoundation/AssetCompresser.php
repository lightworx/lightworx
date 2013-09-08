<?php

namespace Lightworx\Component\HttpFoundation;


class AssetCompresser
{
	public $cachePath;
	public $cacheExpired = 36000;
	
	
	/**
	* Compress the javascript code.
	* Note: The regular expressions can break the javascript if there is a double forward slash in the code, like the urls.
	*       The solution is to escape the slashes, just like the following: 
	*       'http:\/\/www.example.com'
	* @param string $code
	* @return string
	*/ 
	public static function scriptCompresser($code)
	{
		$code = preg_replace("/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/", "", $code);
        $code = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $code);
        $code = preg_replace(array('(( )+\))','(\)( )+)'), ')', $code);
        return $code;
	}
	
	/**
	* Compress the CSS code.
	* @param string $code
	* @return string
	*/
	public static function cssCompresser($code)
	{
		/* remove comments */
        $code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code);
        /* remove tabs, spaces, newlines, etc. */
        $code = str_replace(array("\r\n","\r","\n","\t",'  ','    ','     '), '', $code);
        /* remove other spaces before/after ; */
        $code = preg_replace(array('(( )+{)','({( )+)'), '{', $code);
        $code = preg_replace(array('(( )+})','(}( )+)','(;( )*})'), '}', $code);
        $code = preg_replace(array('(;( )+)','(( )+;)'), ';', $code);
        return $code;
	}
}