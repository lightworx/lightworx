<?php

namespace Lightworx\Helper\String;

use Lightworx\Foundation\ClassLoader;

function substr($string,$start,$length,$encoding=null)
{
	if($encoding===null)
	{
		$encoding = \Lightworx::getApplication()->charset;
	}
	if(function_exists('mb_substr'))
	{
		return mb_substr($string,$start,$length,$encoding);
	}
	return substr($string,$start,$length);
}

/*
 * The function 'generate_random' to generate a random string.
 * and the length defaults to 32.
 */
function generate_random($length=32)
{
	if(function_exists('openssl_random_pseudo_bytes'))
	{
		$string = base64_encode(openssl_random_pseudo_bytes($length,$strong));
		if($strong===true)
		{
			return substr($string, 0, $length);
		}
	}
	$hash = str_shuffle(md5(rand().microtime().time()));
	$string = base64_encode(str_shuffle(base64_encode($hash).sha1($hash)));
	return substr($string, 0, $length);
}

function html_purifier($content,$options=array())
{
	if(ClassLoader::hasNamespace('HtmlPurifier')===false)
	{
		ClassLoader::registerNamespace('HtmlPurifier',LIGHTWORX_PATH.'Vendors'.DS.'Security'.DS.'HtmlPurifier'.DS);
	}

	$purifier=new \HtmlPurifier($options);
	$purifier->config->set('Cache.SerializerPath',\Lightworx::getApplication()->getRuntimePath());
	return $purifier->purify($content);
}