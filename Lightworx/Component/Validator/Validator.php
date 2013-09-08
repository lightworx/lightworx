<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Validator.php 29 2011-10-04 05:22:03Z Stephen.Lee $
 */

namespace Lightworx\Component\Validator;

use Lightworx\Component\Translation\Translator;

abstract class Validator
{
	/**
	 * When the validation is invalid, 
	 * that will be return the error message.
	 * @var string
	 */
	public $message;
	
	/**
	 * Whether allow the attribute is empty or not.
	 * @var boolean
	 */
	public $allowEmpty = false;
	
	abstract public function validateAttribute($object,$attribute);
	
	/**
	 * Adding an error message to specify object
	 * @param object $object
	 * @param string $attribute
	 * @param string message
	 * @param array $placeholders
	 */
	public function addError($object,$attribute,$message,array $placeholders=array())
	{
		$labels = $object->attributeLabels();
		if(isset($labels[$attribute]))
		{
			$placeholders['{attribute}'] = $labels[$attribute];
		}else{
			$placeholders['{attribute}'] = $attribute;
		}
		if(is_array($message))
		{
			$messages = array();
			foreach($message as $msg)
			{
				$messages[] = $this->getTranslator()->__($msg,$placeholders);
			}
			$message = implode("\n",$messages);
		}else{
			$message = $this->getTranslator()->__($message,$placeholders);
		}
		$object->addError($message,$placeholders,$attribute);
	}
	
	/**
	 * The attribute is empty.
	 * @param mixed $value the value of the attribute
	 * @param boolean $trim
	 * @return boolean
	 */
	protected function isEmpty($value,$trim=false)
	{
		return $value===null || $value===array() || $value==='' || $trim && is_scalar($value) && trim($value)==='';
	}
	
	/**
	 * Return the message translator
	 * @return Translator
	 */
	protected function getTranslator()
	{
		return new Translator($this);
	}
}