<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Encryption;

class XorEncrypt
{
	public static function hash($string)
	{
		return function_exists('sha1') ? sha1($string) : md5($string);
	}
	
	public static function xorOperate($string,$key)
	{
		$hash = self::hash($key);
		$xorString = '';
		
		for($i=0; $i < strlen($string); $i++)
		{
			$xorString .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}
		return $xorString;
	}
	
	public static function encrypt($string,$key)
	{
		$rand = self::hash(mt_rand(0,getrandmax()).time());
		$encode = '';
		
		for($i=0; $i < strlen($string); $i++)
		{
			$enkey = substr($rand,($i % strlen($rand)),1);
			$encode .= $enkey.($enkey ^ substr($string,$i,1));
		}
		return self::xorOperate($encode,$key);
	}
	
	public static function decrypt($string,$key)
	{
		$string = self::xorOperate($string,$key);
		$decode = '';
		
		for($i=0; $i < strlen($string); $i++)
		{
			$decode .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}
		return $decode;
	}
}