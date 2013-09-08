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

class Mcrypt
{
	public static $cryptAlgorithm = 'des';
	
	public static function initialize()
	{
		if(!extension_loaded('mcrypt'))
		{
			throw new \RuntimeException("Cannot be found extension mcrypt");
		}
	}
	
	public static function encrypt($string,$key)
	{
		self::initialize();
		$td = mcrypt_module_open(self::$cryptAlgorithm,'',MCRYPT_MODE_ECB,'');
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		$iv_size = mcrypt_enc_get_iv_size($td);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	
		if(mcrypt_generic_init($td,$key,$iv)!==false)
		{
		    $encode = mcrypt_generic($td, $string);
		    mcrypt_generic_deinit($td);
		    mcrypt_module_close($td);
			return $encode;
		}
	}
	
	public static function decrypt($string,$key)
	{
		self::initialize();
		$td = mcrypt_module_open(self::$cryptAlgorithm,'',MCRYPT_MODE_ECB,'');
		$key = substr($key, 0, mcrypt_enc_get_key_size($td));
		$iv_size = mcrypt_enc_get_iv_size($td);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		if(mcrypt_generic_init($td,$key,$iv)!==false)
		{
			$decode = mdecrypt_generic($td, $string);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			return $decode;
		}
	}
	
	public static function strCrypt($string,$key,$action='encode')
	{
		self::initialize();
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	    $encode = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB, $iv));
		if($action=='encode')
		{
	    	return $encode;
		}

		$decrypt = trim(base64_decode($string));
		$decode = mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$key,$decrypt,MCRYPT_MODE_ECB,$iv);
		if($action=='decode')
		{
			return $decode;
		}
	}
}