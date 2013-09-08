<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Userland;

use Lightworx\Queryworx\Base\Model;
use Lightworx\Component\Encryption\CryptString;
use Lightworx\Component\Encryption\XorEncrypt;

class ModelAuthenticator
{
	public $model;
	public $stateName = 'model.hash.token';

	public function __construct(Model $model)
	{
		$this->model = $model;
	}

	public function validate($sign)
	{
		if($this->getHashToken()==$sign)
		{
			return true;
		}
		return false;
	}

	public function getHashToken()
	{
		$pk = $this->model->getPrimaryKeyName();
		$salt = \Lightworx::getApplication()->user->getSalt();
		$state = \Lightworx::getApplication()->getState($this->stateName);

		return md5($pk.$salt.$state);
	}
}