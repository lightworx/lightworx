<?php
/**
 *  This file is part of the Lightworx
 *  @author Stephen Lee <stephen.lee@lightworx.io>
 *  @link https://lightworx.io/
 *  @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 *  @version $Id$
 */

class ValidationRule
{
	public $rules;
	public $attributes;
	public $validator = array(
		"required"=>"required",
		"length"=>"length",
		"url"=>"url",
		"email"=>"email",
		"number"=>"number",
		"date"=>"date",
		"compare"=>"equalTo",
	);
	
	public function setMetadataRules(array $rules)
	{
		
	}
	
	public function setValidatorRules(array $rules)
	{
		$attributes = array();
		foreach($rules as $item)
		{
			if(!isset($item[0]))
			{
				continue;
			}
			if(is_string($item[0]))
			{
				$item[0] = trim(explode(",",$item[0]));
			}
			
			foreach($item[0] as $attribute)
			{
				$attributes[$attribute]['validators'][] = $item[1];
				isset($item[2]) ? $attributes[$attribute]['rule'][] = $item[2] : "";
			}
		}
	}
	
	public function getValidators($object,array $rules)
	{
		foreach($rules as $rule)
		{
			if(!isset($rule[0]))
			{
				continue;
			}
			if(is_string($rule[0]))
			{
				$rule[0] = array_map("trim",explode(",",$rule[0]));
			}
			$params = array_splice($rule,1);
			foreach($rule[0] as $attribute)
			{
				$this->createClientValidator($object,$attribute,$params);
			}
		}
	}
	
	public function createClientValidator()
	{
		
	}
	
	/**
	 * Create validation rules for an attribute
	 */
	public function createAttributeValidationRules($object,$rules)
	{
		return '$("'.$object.'").rules("add",{
			required:true,
			minlength:1,
			messages{
				required:"Required input",
				minlength:jQuery.format("Please, at least {0} characters are necessary.")
			}
		});';
	}
	
	/**
	 * Return the validation rules for the jQuery plugin jQuery.validate
	 * @return string The method must return a JSON string
	 */
	public function getJqueryValidateRules()
	{
		
	}
	
	public function addEmailRules($attribute,$rules="email:true")
	{
		$this->rules[$attribute][] = $rules;
	}
	
	public function url($attribute,$rules="url:true")
	{
		$this->rules[$attribute][] = $rules;
	}
	
	public function addRequiredRule($attribute,$rules="required:true")
	{
		$this->rules[$attribute][] = $rules;
	}
	
	public function remote($attribute,$rules)
	{
		$this->rules[$attribute][] = $rules;
	}
	
	public function minlength()
	{
		
	}
	public function maxlength()
	{
		
	}
	public function rangelength()
	{
		
	}
	public function min()
	{
		
	}
	public function max()
	{
		
	}
	public function range()
	{
		
	}
	public function date()
	{
		
	}
	public function number()
	{
		
	}
	public function digits()
	{
		
	}
	public function creditcard()
	{
		
	}
	public function accept()
	{
		
	}
	public function equalTo()
	{
		
	}
}