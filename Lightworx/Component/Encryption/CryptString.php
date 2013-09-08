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

use Lightworx\Component\Encryption\Mcrypt;
use Lightworx\Component\Encryption\XorEncrypt;

/**
 * This class dependency the extension Mcrypt
 * @see http://www.php.net/manual/en/book.mcrypt.php
 * @since version 0.1
 * @author Stephen Lee <stephen.lee@lightworx.io>
 */
class CryptString
{
	public $key;
	
	public $hashAlgorithm = 'md5';
	
	public static function getState($key='session.key')
	{
		return \Lightworx::getApplication()->getState($key);
	}
	
	public static function generateState()
	{
		return \Lightworx\Helper\String\generate_random(128);
	}
	
	public function encrypt($string,$key=null)
	{
		if($key===null)
		{
			$key = $this->getState();
		}
		
		if(extension_loaded('mcrypt'))
		{
			return Mcrypt::encrypt($string,$key);
		}else{
			return XorEncrypt::encrypt($string,$key);
		}
	}
	
	public function decrypt($string,$key=null)
	{
		if($key===null)
		{
			$key = $this->getState();
		}
		
		if(extension_loaded('mcrypt'))
		{
			return Mcrypt::decrypt($string,$key);
		}else{
			return XorEncrypt::decrypt($string,$key);
		}
	}
}