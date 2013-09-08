<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Userland\Authentication;

use Lightworx\Exception\HttpException;

class HttpAuthenticator extends BaseAuthenticator
{
	public $users = array();
	
	/**
	 * The HTTP authentication type, this value must be "Basic" or "Digest" one of them.
	 * @var string
	 */
	public $type = "Basic";
	
	/**
	 * Setting the realm
	 * @var string
	 */
	public $realm = "Authentication system";
	
	/**
	 * Authentication flag
	 * @var string
	 */
	 public $authFlag = "php_http_auth";
	
	/**
	 * When authentication type is digest, you should assignment value for this property
	 * @var string
	 */
	public $otherParameters;
	
	/**
	 * Set the session data storage component
	 * @var string
	 */
	public $sessionStorage = 'Lightworx.Component.Storage.CookieStorage';
	
	/**
	 * Set the status to cookie, defaults to false.
	 */
	public $storeSession = false;
	
	/**
	 * Validating the user login information
	 * @return boolean
	 */
	public function validate()
	{
		$sessionStorage = $this->getComponent($this->getSessionStorage());
		$flag = $sessionStorage->isExists($this->authFlag);

		if(!isset($_SERVER['PHP_AUTH_USER']) or !isset($_SERVER['PHP_AUTH_PW']))
		{
			return false;
		}
		
		if($flag and isset($this->users[$_SERVER['PHP_AUTH_USER']]) and $this->users[$_SERVER['PHP_AUTH_USER']] == $_SERVER['PHP_AUTH_PW'])
		{
			$params = array($_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
			$this->assignIdentity($params);
			return true;
		}
		return false;
	}
	
	public function assignIdentity($userInfo)
	{
		if($this->storeSession===true)
		{
			$this->user->getUserSession()->assignIdentity($userInfo);
		}
	}
	
	protected function isValid()
	{
		$method = 'authentication'.$this->type;
		if(method_exists($this,$method))
		{
			$this->{$method}();
		}
	}
	
	/**
	 * Authentication user identity in basic type
	 * @throws HttpException
	 */
	public function authenticationBasic()
	{
		if($this->validate()===false)
		{
    		header('WWW-Authenticate: Basic realm="'.$this->realm.'"');
    		header('HTTP/1.0 401 Unauthorized');
    		
			$properties = array('name' => $this->authFlag,'value' => '1','expire' => 0,'path' => '/');
			$sessionStorage = $this->getComponent($this->getSessionStorage());
			$sessionStorage->setProperties($properties);
			$sessionStorage->save();
			
			throw new HttpException("401","Unable to authenticate your identity.");
		}
		return true;
	}
	
	public function authenticationDigest()
	{
		if($this->user->validate()===false)
		{
    		header('WWW-Authenticate: Digest realm="'.$this->realm.'" '.$this->otherParameters.'');
    		header('HTTP/1.0 401 Unauthorized');
			throw new HttpException("401","Unable to authenticate your identity.");
		}
	}
	
	/**
	 * Because the Authentication information post from the browser,
	 * cannot clear the PHP_AUTH_USER and PHP_AUTH_PW
	 * if you dependency both of key to validate user identity, and you want to logout, 
	 * you should reminding the user to close the current browser windows.
	 */
	public function logout()
	{
		unset($_SERVER['PHP_AUTH_USER']);
	    unset($_SERVER['PHP_AUTH_PW']);
	
		$properties = array('name' => $this->authFlag,'value' =>'','expire' =>time()-3600,'path' => '/');
		
		$sessionStorage = $this->getComponent($this->getSessionStorage());
		$sessionStorage->setProperties($properties);
		$sessionStorage->save();
	}
}