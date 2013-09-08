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

class BooleanValidator extends Validator
{
	public $trueValue='1';

	public $falseValue='0';

	public $strict=false;

	public $allowEmpty=true;

	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if(!$this->strict && $value!=$this->trueValue && $value!=$this->falseValue
			|| $this->strict && $value!==$this->trueValue && $value!==$this->falseValue)
		{
			$message=$this->message!==null?$this->message:'{attribute} must be either {true} or {false}.';
			$this->addError($object,$attribute,$message,array("{attribute}"=>$attribute,'{true}'=>$this->trueValue, '{false}'=>$this->falseValue));
		}
	}
}