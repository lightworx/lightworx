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

use Lightworx\Controller\Controller;
use Lightworx\Foundation\Object;
use Lightworx\Foundation\Dispatcher;
use Lightworx\Exception\HttpException;

class AccessAuthenticator extends Object
{
	/**
	 * The authentication method name, defaults to auth.
	 * @var string
	 */
	public $authMethod = 'auth';
	
	public $authenticatorPath;
	
	public $authenticatorSuffix = 'Authenticator';
	
	/**
	 * Initialize the authenticator path
	 */
	public function __construct()
	{
		$this->authenticatorPath = \Lightworx::getApplicationPath().'authenticator/';
	}
	
	/**
	 * Get user allow actions
	 * @param array $authRules
	 * @return array if the key actions not exists,that to return an empty array
	 */
	public function getUserAllowActions($role,$authRules)
	{		
		if(!isset($authRules[$role]))
		{
			return array();
		}
		return isset($authRules[$role]['allowActions']) ? (array)$authRules[$role]['allowActions'] : array();
	}
	
	public function getUserDeniedActions($role,$authRules)
	{		
		if(!isset($authRules[$role]))
		{
			return array();
		}
		return isset($authRules[$role]['deniedActions']) ? (array)$authRules[$role]['deniedActions'] : array();
	}

	public function getAccessRules($controller)
	{
		$authRules = array();

		if(method_exists($controller,$this->authMethod))
		{
			$authRules = $controller->{$this->authMethod}();
		}

		if(!is_array($authRules))
		{
			throw new \RuntimeException("The method auth must be return an array");
		}

		$authorizationFile = Dispatcher::getAuthorizationConfig(\Lightworx::getApplication()->getRouter()->controller);

		if(is_array($authorizationFile))
		{
			$authRules = array_replace_recursive($authRules,$authorizationFile);
		}
		return $authRules;
	}
	
	/**
	 * Validation the user access rules.
	 * @param Controller $controller
	 * @param string $action
	 * @param array $userRoles
	 */
	public function isValid(Controller $controller, $action, array $userRoles=array())
	{
		$action = strtolower($action);

		$authRules = $this->getAccessRules($controller);
		
		if(count($authRules)===0)
		{
			return;
		}

		if(isset($authRules['*']))
		{
			array_push($userRoles,'*');
		}
		
		$message = "Access to the requested resource has been denied.";
		
		foreach($userRoles as $role)
		{
			$authenticator = $this->getAuthenticator($role);
			
			if($authenticator!==false)
			{
				if($this->runAuthenticator($controller,$action,$authenticator))
				{
					return;
				}else{
					$message = $authenticator->message;
				}
			}

			if($this->hasRule($role,$authRules)===false)
			{
				continue;
			}

			if($this->validateAllowActions($role,$authRules,$action)===true)
			{
				return;
			}

			if($this->validateDeniedActions($role,$authRules,$action)===true)
			{
				return;
			}
			
			if(isset($authRules[$role]) and isset($authRules[$role]['redirect']))
			{
				$controller->redirect($authRules[$role]['redirect']);
			}
			
			if(isset($authRules[$role]) and isset($authRules[$role]['message']))
			{
				$message = $authRules[$role]['message'];
			}
		}
		throw new HttpException(403,$message);
	}

	public function validateAllowActions($role,$authRules,$action)
	{
		$userAllowActions = $this->getUserAllowActions($role,$authRules);
		
		if($userAllowActions!==array())
		{
			$func = "\Lightworx\Helper\ArrayHelper\iin_array";
			if(in_array("*",$userAllowActions) or $func($action,$userAllowActions))
			{
				return true;
			}
			return false;
		}
		return;
	}

	public function validateDeniedActions($role,$authRules,$action)
	{
		$userDeniedActions = $this->getUserDeniedActions($role,$authRules);

		if($userDeniedActions!==array())
		{
			$func = "\Lightworx\Helper\ArrayHelper\iin_array";
			if(!in_array("*",$userDeniedActions) and !$func($action,$userDeniedActions))
			{
				return true;
			}
			return false;
		}
		return;
	}

	public function hasRule($role,$authRules)
	{
		if(isset($authRules[$role]))
		{
			return true;
		}
		return false;
	}

	public function runAuthenticator($controller,$action,$authenticator)
	{
		if(!method_exists($authenticator,'isValid'))
		{
			throw new \RuntimeException("The method isValid have no define.");
		}
		
		$result = $authenticator->isValid($controller,$action);

		if(!is_bool($result))
		{
			throw new \RuntimeException("The method isValid must be return boolean.");
		}

		return $result;
	}
	
	/**
	 * Get a authenticator from application.
	 * @param string $roleName
	 * @return mixed if the authenticator does not exist, 
	 * that will to return false, otherwise, return the authenticator instance.
	 */
	public function getAuthenticator($roleName)
	{	
		$authenticator = ucfirst($roleName).$this->authenticatorSuffix;
		$authFile = $this->getAuthenticatorPath().$authenticator.'.php';
		
		if(file_exists($authFile) and class_exists($authenticator))
		{
			return new $authenticator;
		}
		return false;
	}
}