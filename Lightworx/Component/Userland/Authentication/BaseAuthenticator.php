<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Userland\Authentication;

use Lightworx\Foundation\Object;

abstract class BaseAuthenticator extends Object
{
	public $user;
	
	/**
	 * The parameter $user, should to provide the method validate, 
	 * and to finish validate user login information.
	 * @param object $user
	 */
	public function __construct()
	{
		$this->user = \Lightworx::getApplication()->user;
	}
	
	public function execute()
	{
		$this->isValid();
	}
	
	protected function authenticateUserIdentity()
	{
		if($this->isValid()===true)
		{
			return true;
		}
		return false;
	}
	
	abstract protected function isValid();
	
	abstract public function logout();
}