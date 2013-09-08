<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Validator;

class RangeValidator extends Validator
{
	public $range;
	
	public $strict = false;
	
	public $allowEmpty = true;

	public $caseSensitive = true;

	public $matchInRange = true;

	public function validateAttribute($object,$attribute)
	{
		$value = $object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
		{
			return;
		}

		$function = 'in_array';
		if($this->caseSensitive===false)
		{
			$function = "\Lightworx\Helper\ArrayHelper\iin_array";
		}
		
		if(is_array($this->range) && $function($value,$this->range,$this->strict)!==$this->matchInRange)
		{
			$message = $this->message!==null?$this->message:'{attribute} is not in the list.';
			$this->addError($object,$attribute,$message);
		}
	}
}