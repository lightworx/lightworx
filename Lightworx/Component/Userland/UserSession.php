<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Userland;

use Lightworx\Component\Encryption\CryptString;

class UserSession
{
	/**
	 * The prefix of cookie name.
	 * @var string
	 */
	public $cookiePrefix = '';
	
	/**
	 * Specifying the session data storage component
	 * @var string
	 */
	public $sessionStorage = 'Lightworx.Component.Storage.CookieStorage';
	
	/**
	 * Set the http connection whether based on HTTPS connection 
	 * default false, that means it is a normal http connection.
	 * @var boolean
	 */
	public $secureConnection = false;

	/**
	 * The property $httpOnly protect your cookie just for HTTP communication,
	 * and the browser script cannot to access the cookies, 
	 * by defaults the Lightworx use this option to 'true', just to assign user identity.
	 * @var boolean defaults to true
	 */
	public $httpOnly = true;
	
	/**
	 * Specifying identity name for sessions.
	 * @var string
	 */
	protected $identityName = 'identity';
	
	/**
	 * When get the session storage component,should assign to  
	 * this property.
	 * @var mixed default null
	 */
	static private $sessionStorageInstance = null;
	
	/**
	 * The object CryptString properties
	 * @var array
	 */
	public $cryptStringConfig = array();


	public $enableCookieSign = true;
	public $cookieSignName = 'sign';
	public $signHashFunction = 'md5';
	public $cookieSignToken = 'lightworx.cookie.sign.token';
	
	/**
	 * Get sessions storage component
	 * @return object
	 */
	public function getSessionStorage()
	{
		if(self::$sessionStorageInstance===null)
		{
			self::$sessionStorageInstance = \Lightworx::getApplication()->getComponent($this->sessionStorage);
		}
		return self::$sessionStorageInstance;
	}
	
	/**
	 * Return the user identity cookie name, contained cookie prefix.
	 * @return string
	 */
	public function getIdentityName()
	{
		return $this->cookiePrefix.$this->identityName;
	}

	public function getCookieSignName()
	{
		return $this->cookiePrefix.$this->cookieSignName;
	}
	
	/**
	 * Get the user identity from the cookie
	 * @return array
	 */
	public function getUserIdentity()
	{
		$this->getSessionStorage()->setProperties(array('name'=>$this->getIdentityName()));
		$identity = $this->getSessionStorage()->getData($this->getIdentityName());
		
		if($identity=="")
		{
			$this->removeIdentity();
			return false;
		}
		
		$cookie = $this->decodeCookie($identity);
		
		if(($cookies = $this->safeUnserialize($cookie))===false)
		{
			$this->removeIdentity();
			return false;
		}
		return $cookies;
	}
	
	/**
	 * Unserialize data in safe way.
	 * @param string $serialized
	 */
	public function safeUnserialize($serialized)
	{
	    if (is_string($serialized))
		{
	        if(strpos($serialized, 'O:') === false)
			{
	            return @unserialize($serialized);
	        }
			if(!preg_match('/(^|;|{|})O:[0-9]+:"/', $serialized))
			{
	            return @unserialize($serialized);
	        }
	    }
	    return false;
	}
	
	public function cookieSign($cookie)
	{
		if(!is_callable($this->signHashFunction))
		{
			throw new \RuntimeException("The hash function ".$this->signHashFunction." doesn't exist.");
		}
		$cookieSignToken = \Lightworx::getApplication()->getState($this->cookieSignToken);
		$signHashFunction = $this->signHashFunction;
		return $signHashFunction($cookieSignToken.$cookie);
	}

	public function validateCookieSign()
	{
		if($this->enableCookieSign===false)
		{
			return true;
		}

		$this->getSessionStorage()->setProperties(array('name'=>$this->getIdentityName()));
		$userIdentity = $this->getSessionStorage()->getData($this->getIdentityName());

		return $this->getCookieSign() == $this->cookieSign($userIdentity);
	}

	public function getCookieSign()
	{
		$this->getSessionStorage()->setProperties(array('name'=>$this->getCookieSignName()));

		return $this->getSessionStorage()->getData($this->getCookieSignName());
	}

	public function setCookieSign($cookie,$expire=0,$path='/',$domain='')
	{
		if($this->enableCookieSign===true)
		{
			$properties = array(
				'name'=>$this->getCookieSignName(),
				'value'=>$this->cookieSign($cookie),
				'expire'=>$expire,
				'path'=>$path,
				'domain'=>$domain,
				'secure'=>$this->secureConnection,
				'httpOnly'=>$this->httpOnly
			);
			$this->getSessionStorage()->setProperties($properties);
			$this->getSessionStorage()->save();
		}
	}

	public function removeCookieSign($expire=0,$path='/',$domain='')
	{
		if($this->enableCookieSign===true)
		{
			$properties = array(
				'name'=>$this->getCookieSignName(),
				'value'=>'',
				'expire'=>$expire,
				'path'=>$path,
				'domain'=>$domain,
				'secure'=>$this->secureConnection,
				'httpOnly'=>$this->httpOnly
			);
			$this->getSessionStorage()->setProperties($properties);
			$this->getSessionStorage()->save();
		}
	}

	/**
	* Assign the identity to user
	* @param array $userinfo
	* @param integer expire
	* @param string $path
	*/
	public function assignIdentity(array $userinfo=array(),$expire=0,$path='/',$domain='')
	{
		$encodedCookie = $this->encodeCookie(serialize($userinfo));
		$properties = array(
			'name'=>$this->getIdentityName(),
			'value'=>$encodedCookie,
			'expire'=>$expire,
			'path'=>$path,
			'domain'=>$domain,
			'secure'=>$this->secureConnection,
			'httpOnly'=>$this->httpOnly
		);
		$this->getSessionStorage()->setProperties($properties);
		$this->getSessionStorage()->save();

		$this->setCookieSign($encodedCookie,$expire,$path,$domain);
	}
	
	/**
	 * Remove the user identity.
	 */
	public function removeIdentity($expire=0,$path='/',$domain='')
	{
		$properties = array(
			'name'=>$this->getIdentityName(),
			'value'=>'',
			'expire'=>$expire,
			'path'=>$path,
			'domain'=>$domain,
			'secure'=>$this->secureConnection,
			'httpOnly'=>$this->httpOnly
		);
		$this->getSessionStorage()->setProperties($properties);
		$this->getSessionStorage()->save();

		$this->removeCookieSign($expire,$path,$domain);
	}
	
	/**
	 * Check whether user is logged in or not.
	 * @return boolean
	 */
	public function isLoggedIn()
	{
		return $this->getSessionStorage()->isExists($this->getIdentityName());
	}
	
	/**
	 * Encryption a cookie
	 * @param string $cookie
	 * @return string
	 */
	public function encodeCookie($cookie)
	{
		return base64_encode($this->createCryptString()->encrypt($cookie));
	}
	
	/**
	 * Decryption a cookie
	 * @param string $cookie
	 * @return string
	 */
	public function decodeCookie($cookie)
	{
		return $this->createCryptString()->decrypt(base64_decode($cookie));
	}
	
	/**
	 * Create object CryptString
	 */
	public function createCryptString()
	{
		$cryptString = new CryptString;
		foreach($this->cryptStringConfig as $property=>$value)
		{
			$cryptString->{$property} = $value;
		}
		return $cryptString;
	}
}