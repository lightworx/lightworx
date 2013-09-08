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

class ValidatorBuilder
{
	/**
	 * Get all the validators
	 * @return array
	 */
	public function getValidators($object,array $rules)
	{
		foreach($rules as $rule)
		{
			if(!is_array($rule) or !isset($rule[0]))
			{
				continue;
			}
			if(is_string($rule[0]))
			{
				$attributes = array_map("trim",explode(",",$rule[0]));
			}
			if(!isset($rule[1]))
			{
				throw new \RuntimeException("The validator name is invalid.");
			}
			$validator = $rule[1];
			$params = array_splice($rule,2);
			foreach($attributes as $attribute)
			{
				$this->createValidator($object,$attribute,$validator,$params);
			}
		}
	}
	
	public function createValidator($object,$attribute,$validatorName,array $params)
	{
		$validatorExist = true;
		$validator = "Lightworx\\Component\\Validator\\".ucfirst($validatorName)."Validator";

		if(!class_exists($validator))
		{
			$validator = ucfirst($validatorName)."Validator";
			$validatorExist = false;
		}
		
		if($validatorExist===false and !class_exists($validator))
		{
			throw new \InvalidArgumentException("cannot found the validator:".$validator);
		}

		$instance = new $validator;
		foreach($params as $property=>$value)
		{
			$instance->$property = $value;
		}
		call_user_func_array(array($instance,'validateAttribute'),array($object,$attribute));
	}
}