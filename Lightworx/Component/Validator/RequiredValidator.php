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

class RequiredValidator extends Validator
{
	public $requiredValue;

	public $strict=false;
	
	public function validateAttribute($object,$attribute)
	{
		$value = $object->$attribute;
		if($this->requiredValue!==null)
		{
			if(!$this->strict && $value!=$this->requiredValue || $this->strict && $value!==$this->requiredValue)
			{
				$message = $this->message!==null ? $this->message : "{attribute} must be {value}";
				$this->addError($object,$attribute,$message,array("{value}"=>$this->requiredValue));
				return;
			}
		}
		if($this->isEmpty($value,true))
		{
			$message = $this->message!==null ? $this->message : "{attribute} cannot be blank";
			$this->addError($object,$attribute,$message);
		}
	}
}