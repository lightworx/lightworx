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

class UrlValidator extends Validator
{
	public $pattern='/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';

	public $allowEmpty=true;

	public function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
		{
			return;
		}
		if(!$this->validateValue($value))
		{
			$this->addError($object,$attribute,$this->message!==null?$this->message:'{attribute} is not a valid URL.');
		}
	}

	public function validateValue($value)
	{
		return is_string($value) && preg_match($this->pattern,$value);
	}
}