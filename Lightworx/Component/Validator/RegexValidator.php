<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: RegexValidator.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Component\Validator;

class RegexValidator extends Validator
{
	public $pattern;

	public $allowEmpty=true;

	public function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
		{
			return;
		}
		if(!preg_match($this->pattern,$value))
		{
			$message=$this->message!==null?$this->message:'{attribute} is invalid.';
			$this->addError($object,$attribute,$message);
		}
	}
}