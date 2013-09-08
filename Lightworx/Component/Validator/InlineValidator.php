<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Validator;

class InlineValidator extends Validator
{
	public $method;

	public $params;
	
	public function validateAttribute($object,$attribute)
	{
		$method=$this->method;
		if($object->$method($attribute,$this->params)!==true)
		{
			$message = 'The {attribute} validation failure.';
			$message = $this->message!="" ? $this->message : $message;
			$this->addError($object,$attribute,$message);
		}
	}
}