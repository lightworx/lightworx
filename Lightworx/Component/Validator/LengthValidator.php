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

class LengthValidator extends Validator
{
	public $charset = 'utf-8';
	
	public $min;
	
	public $max;
	
	public $is;
	
	public $tooShort;
	
	public $tooLong;
	
	public $allowEmpty = true;
	
	public function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
		{
			return;
		}
		
		if(!is_string($value))
		{
			$this->addError($object,$attribute,'{attribute} is a invalid string.');
			return;
		}
		
		if($this->charset!==false && function_exists('mb_strlen'))
		{
			$length=mb_strlen($value,$this->charset);
		}else{
			$length=strlen($value);
		}
		
		if($this->min!==null && $length<$this->min)
		{
			$message=$this->tooShort!==null?$this->tooShort:'{attribute} is too short (minimum is {min} characters).';
			$this->addError($object,$attribute,$message,array('{min}'=>$this->min));
		}
		
		if($this->max!==null && $length>$this->max)
		{
			$message=$this->tooLong!==null?$this->tooLong:'{attribute} is too long (maximum is {max} characters).';
			$this->addError($object,$attribute,$message,array('{max}'=>$this->max));
		}
		
		if($this->is!==null && $length!==$this->is)
		{
			$message=$this->message!==null?$this->message:'{attribute} is of the wrong length (should be {length} characters).';
			$this->addError($object,$attribute,$message,array('{length}'=>$this->is));
		}
	}
}