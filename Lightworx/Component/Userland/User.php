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

use Lightworx\Component\Userland\BaseUser;
use Lightworx\Component\Encryption\CryptString;
use Lightworx\Component\Encryption\XorEncrypt;

class User extends BaseUser
{
	public $defaultGuestRole = array('guest');
	public $defaultMemberRole = array('member');
	public $userSalt;
	public $validateCsrfToken = false;
	
	/**
	 * The application was defined user roles.
	 */
	protected $userRoles = array();
	
	/**
	 * The UserSession instance.
	 */
	protected static $userSession;
	
	/**
	 * The user access authenticator instance
	 */
	protected static $accessAuthenticator;
	
	/**
	 * check the user whether is some one role
	 * and get some one property from cookie, if it is exists.
	 */
	public function __call($method,$value)
	{
		if(substr($method,0,2)==='is')
		{
			if(in_array(lcfirst(substr($method,2)),$this->getUserRole()))
			{
				return true;
			}
			return false;
		}
		if(substr($method,0,3)=='get' and $this->isGuest()===false)
		{
			$property = lcfirst(substr($method,3));
			$userSession = $this->getUserSession()->getUserIdentity();
			return isset($userSession[$property]) ? $userSession[$property] : '';
		}
	}
	
	/**
	 * Get the role of the user.
	 * @return array
	 */
	public function getUserRole()
	{
		if($this->isGuest())
		{
			return $this->defaultGuestRole;
		}
		$userSession = $this->getUserSession()->getUserIdentity();
		return isset($userSession['userRole']) ? (array)$userSession['userRole'] : array();
	}
	
	/**
	 * Check whether user is logged in or not.
	 * @return boolean
	 */
	public function isGuest()
	{
		if($this->getUserSession()->isLoggedIn()===true)
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Get the UserSession instance.
	 */
	public function getUserSession()
	{
		if(self::$userSession===null)
		{
			self::$userSession =  new UserSession();
		}
		return self::$userSession;
	}
	
	/**
	 * Attach a role for a user.
	 * @param string $roleName
	 */
	public function attachRole($roleName)
	{
		$this->userRoles[] = $roleName;
	}
	
	/**
	 * Remove a role form userRole.
	 * @param string $roleName
	 */
	public function removeRole($roleName)
	{
		foreach($this->userRoles as $key=>$userRole)
		{
			if($userRole===$roleName)
			{
				unset($this->userRoles[$key]);
			}
		}
	}
	
	/**
	 * Validate the user whether have permission to access an action or not.
	 */
	public function validateAccess($controller,$action)
	{
		if(\Lightworx::getApplication()->validateCsrfToken())
		{
			$this->validateCsrfToken = true;
		}
		if($this->getUserSession()->validateCookieSign()===false)
		{
			$this->getUserSession()->removeIdentity();
		}
		$this->getAccessAuthenticator()->isValid($controller,$action,$this->getUserRole());
	}
	
	/**
	 * Get the AccessAuthenticator instance
	 */
	public function getAccessAuthenticator()
	{
		if(self::$accessAuthenticator===null)
		{
			self::$accessAuthenticator = new AccessAuthenticator;
		}
		return self::$accessAuthenticator;
	}
	
	public function getUserSalt()
	{
		if($this->userSalt!==null)
		{
			return $this->userSalt;
		}
		
		$this->userSalt = \Lightworx::getApplication()->request->ip;
		
		if($this->isGuest()===false and ($userIdentity = $this->getUserSession()->getUserIdentity())!==false)
		{
			$this->userSalt = serialize($userIdentity);
		}
		return md5($this->userSalt);
	}
	
	public function setUserSalt($userSalt)
	{
		$this->userSalt = $userSalt;
	}
	
	public function encryptData($data)
	{
		return base64_encode(XorEncrypt::encrypt(serialize($data),CryptString::getState($this->getEncryptKey())));
	}
	
	public function decryptData($data)
	{
		return unserialize(XorEncrypt::decrypt(base64_decode($data),CryptString::getState($this->getEncryptKey())));
	}
	
	public function getEncryptKey()
	{
		return \Lightworx::getApplication()->SRFLEncryptKey;
	}
	
	public function beforeLogin(){return true;}

	public function afterLogin(){}
	
	public function beforeLogout(){return true;}

	public function afterLogout(){}
}