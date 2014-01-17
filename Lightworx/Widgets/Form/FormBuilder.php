<?php
/**
 *  This file is part of the Lightworx
 *  @author Stephen Lee <stephen.lee@lightworx.io>
 *  @link https://lightworx.io/
 *  @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 *  @version $Id$
 */

namespace Lightworx\Widgets\Form;

use Lightworx\Foundation\Widget;
use Lightworx\Queryworx\Base\Model;
use Lightworx\Helper\Html;

class FormBuilder extends Widget
{	
	public $model;
	public $formId;
	public $skin = 'default';
	public $submitEvents = array();
	public $enableAjaxForm = false;

	/**
	 * To enable this property protection for the request is safe, when this property is 'true',
	 * the form would to create an code to validate the request of the client.
	 * @var boolean defaults to false
	 */
	public $enableCSRFToken = true;

	/**
	 * Generated form whether support html5 or not.
	 * @var boolean defaults to true, that means support html5
	 */
	public $supportHtml5 = true;
	public $enableClientValidation = false;

	/**
	 * The property enableAutoSave set the form whether enable auto submit or not,
	 * defaults to false, means disable auto submit.
	 * if the form data is not full, that will save to a draft.
	 * @var boolean
	 */
	public $enableAutoSave = false;
	public $submitHandlerNamePrefix = 'submitForm';
	public $ajaxFormOptions = array();
	public $errors = array();
	
	public  $submitEventFunctionName = 'submitEvents';
	
	protected $formAjax;
	protected $formValidator;

	static protected $sequence = 0;
	

	public function getRules()
	{
		$validateRule = new ValidationRule;
		$rules = $this->model->rules();
		if($rules===array())
		{
			$validateRule->setMetadataRules($model->getMetadata()->columns);
		}else{
			$validateRule->setValidatorRules($rules);
		}
		$validateRule->getJqueryValidateRules();
	}
	
	/**
	 * Initialize the widget.
	 */
	public function init()
	{
		$this->formId = ($this->formId===null and is_object($this->model)) ? get_class($this->model) : $this->getId();

		if($this->enableClientValidation===true)
		{
			$this->formValidator = $this->getRender()->getWidget("Lightworx.Widgets.Form.FormValidator");
			$this->formValidator->formId = $this->formId;
			$this->formValidator->enableAjaxForm = $this->enableAjaxForm;
			$this->formValidator->submitHandlerName = $this->getSubmitHandlerName();
			$this->formValidator->init();
			$this->formValidator->run();
		}
		if($this->enableAjaxForm===true)
		{
			$this->formAjax = $this->getRender()->getWidget("Lightworx.Widgets.Form.FormAjax");
			$this->formAjax->formId = $this->formId;
			$this->formAjax->enableClientValidation = $this->enableClientValidation;
			$this->formAjax->submitEventFunctionName = $this->submitEventFunctionName;
			$this->formAjax->submitHandlerName = $this->getSubmitHandlerName();
			$this->formAjax->options = $this->ajaxFormOptions;
			$this->formAjax->init();
			$this->formAjax->run();
		}
	}
	
	/**
	 * Creates a submit handler function name
	 * @return string
	 */
	public function getSubmitHandlerName()
	{
		return $this->submitHandlerNamePrefix.$this->formId;
	}
	
	public function run(){}
	
	/**
	 * Gets a model label
	 * @param string $name
	 * @return string
	 */
	public function label($attribute,array $options=array())
	{
		$attributes = $this->model->attributeLabels();
		isset($attributes[$attribute]) ? $label = $attributes[$attribute] : $label = $attribute;
		
		if(!isset($options['for']))
		{
			$options['for'] = $this->getInputId($attribute);
		}
		return '<label'.$this->getHtmlOptions($options).'>'.$label.'</label>';
	}
	
	/**
	 * Return the attribute tip
	 * @param string $name
	 * @return string
	 */
	public function attributeTip($attribute)
	{
		$tips = $this->model->attributeTips();
		if(isset($tips[$attribute]) and isset($tips[$attribute]['default']))
		{
			return $tips[$attribute]['default'];
		}
	}
	
	/**
	 * Creates a textarea tag
	 */
	public function textArea($attribute,array $options=array(),$multiple=false)
	{
		if(isset($this->model) and $this->model->$attribute!='')
		{
			$options['value'] = $this->model->$attribute;
		}
		
		$defaultAttributes = array('id'=>$this->getInputId($attribute,$multiple),"name"=>$this->getFormItemName($attribute,$multiple));
		$options = array_merge($defaultAttributes,$options);

		$value = '';
		if(isset($options['value']))
		{
			$value = $options['value'];
			unset($options['value']);
		}
		
		return '<textarea'.$this->getHtmlOptions($options).'>'.$value.'</textarea>';
	}
	
	/**
	 * Creates a open fieldset tag
	 * @param array $options
	 * @return string
	 */
	public function beginFieldset(array $options=array())
	{
		return '<fieldset'.$this->getHtmlOptions($options).'>';
	}
	
	/**
	 * Close the fieldset tag
	 * @return string
	 */
	public function endFieldset()
	{
		return '</fieldset>';
	}
	
	/**
	 * Creates a legend tag
	 * @param string $text
	 * @param array $options
	 * @return string
	 */
	public function legend($text,array $options=array())
	{
		return $this->tag("legend",$text,$options);
	}
	
	/**
	 * Return the form id
	 * @return string
	 */
	public function getFormId()
	{
		if($this->formId!==null)
		{
			return $this->formId;
		}
		return $this->getId();
	}
	
	/**
	 * Set the form id.
	 * @param integer $formId
	 */
	public function setFormId($formId)
	{
		$this->formId = $formId;
	}
	
	/**
	 * Get the input id
	 * @param string $id
	 * @return string
	 */
	public function getInputId($id,$multiple=false)
	{
		$seq = $multiple === true ? self::$sequence++ : '';
		if($this->getFormId()!==null)
		{
			return (string)$this->getFormId().'_'.$id.$seq;
		}
		return $id.$seq;
	}
	
	/**
	 * Return the input name, if the $this->model is not null,
	 * that will return the form id contained the input id. 
	 * @param string $name
	 * @return string
	 */
	protected function getFormItemName($attribute,$multiple=false)
	{
		if($this->model!==null)
		{
			if($multiple===true)
			{
				return $this->getFormId().'['.$attribute.'][]';
			}
			return $this->getFormId().'['.$attribute.']';
		}

		return $attribute;
	}
	
	/**
	 * Creates a input, if the $this->model is not null, 
	 * that will create an active input, otherwise, that will create a normal input.
	 * @param string $name the input property name
	 * @param string $type the input type
	 * @param array $options other related properties
	 * @return string
	 */
	public function input($attribute,$type='text', array $options=array(), $multiple=false)
	{
		if($this->model instanceof Model)
		{
			$modelOptions = array(
				'id' => $this->getInputId($attribute),
				'value' => $this->model->$attribute,
			);
			$options = array_merge($options,$modelOptions);
		}
		return '<input type="'.$type.'" name="'.$this->getFormItemName($attribute,$multiple).'"'.$this->getHtmlOptions($options).' />';
	}
	
	/**
	 * Creates a text input
	 * @param string $name the input name
	 * @param array $options other related properties
	 * @return string
	 */
	public function textInput($attribute,array $options=array(),$multiple=false)
	{
		return $this->input($attribute,'text',$options,$multiple);
	}
	
	/**
	 * Creates a password input
	 * @param string $name the input name
	 * @param array $options other related properties
	 * @return string
	 */
	public function passwordInput($attribute,array $options=array(),$multiple=false)
	{
		return $this->input($attribute,'password',$options,$multiple);
	}
	
	/**
	 * Creates a hidden input
	 * @param string $name the input name
	 * @param array $options other related properties
	 * @return string
	 */
	public function hiddenInput($attribute,array $options=array(),$multiple=false)
	{
		return $this->input($attribute,'hidden',$options,$multiple);
	}
	
	/**
	 * Creates an radio input
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	public function radioInput($attribute,array $options=array(),$multiple=false)
	{
		return $this->input($attribute,'radio',$options,$multiple);
	}
	
	/**
	 * Creates a checkbox input
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	public function checkboxInput($attribute,array $options=array(),$multiple=true)
	{
		return $this->input($attribute,'checkbox',$options,$multiple);
	}

	/**
	 * Creates a file input
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	public function fileInput($attribute,array $options=array(),$multiple=false)
	{
		if($multiple===true or (isset($options['multiple']) and $options['multiple']=='multiple'))
		{
			$options['multiple'] = 'multiple';
			$multiple = true;
		}
		return $this->input($attribute,'file',$options,$multiple);
	}
	
	/**
	 * Creates a submit button
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	public function submitButton($attribute,array $options=array())
	{
		return $this->input($attribute,'submit',$options);
	}
	
	/**
	 * Gerenate an form id prefix.
	 * @return string
	 */
	protected function _getFormIdPrefix()
	{
		return $this->getFormId().'_';
	}

	/**
	 * Creates a checkbox list
	 * @param string $attribute
	 * @param array $items
	 * @param string $template
	 * @param string $separator.
	 * @param callback $format use for formatting the value of the attribute
	 * @return string
	 */
	public function checkboxList($attribute,array $items=array(),$template="{checkbox}{label}", $separator=' ', $format=null)
	{
		$values = $checkboxItems = $labelOptions = array();

		if(is_array($this->model->{$attribute}))
		{
			$values = $this->model->{$attribute};
		}

		if($this->model!==null and !is_array($this->model->{$attribute}) and $format!==null and is_callable($format))
		{
			$values = call_user_func($format, $this->model->{$attribute});
		}

		foreach($items as $name=>$item)
		{
			if(!isset($item['value']) or (isset($item['value']) and $item['value']==''))
			{
				continue;
			}

			if(!isset($item['id']))
			{
				$item['id'] = $labelOptions['for'] = $this->_getFormIdPrefix().$attribute.'_'.$name;
			}else{
				$labelOptions['for'] = $item['id'];
			}

			if($values!==array() and is_array($item))
			{
				unset($item['checked']);
				if(in_array($item['value'],$values))
				{
					$item['checked'] = 'checked';
				}
			}
			
			$label = $item['value'];
			if(isset($item['label']))
			{
				$label = $item['label'];
				unset($item['label']);
			}

			$placeholders = array(
				'{checkbox}'=>$this->checkboxInput($attribute,$item),
				'{label}'=>$this->label($label,$labelOptions)
			);

			$checkboxItems[] = str_replace(array_keys($placeholders), array_values($placeholders), $template);
		}
		return implode($separator,$checkboxItems);
	}
	
	/**
	 * Create a radio label
	 * @param string $attribute
	 * @param string $label
	 * @param array $options defaults to an empty array
	 * @return string
	 */
	public function radioLabel($attribute,$label,array $options=array(),$formItem='')
	{
		return '<label'.$this->getHtmlOptions($options).'>'.$formItem.$label.'</label>';
	}

	/**
	 * Generate a radio list
	 * @param string $attribute
	 * @param array $options
	 * @param string $template
	 */
	public function radioList($attribute, array $options=array(), $template = '{radio}', $separator=' ')
	{
		$radioItems = $labelOptions = array();
		foreach($options as $name=>$item)
		{
			if(!isset($item['value']) or (isset($item['value']) and $item['value']==''))
			{
				continue;
			}
			if(!isset($item['id']))
			{
				$item['id'] = $labelOptions['for'] = $this->_getFormIdPrefix().$attribute.'_'.$name;
			}else{
				$labelOptions['for'] = $item['id'];
			}
			
			if($this->model!==null and trim($this->model->{$attribute})!='')
			{
				if(isset($item['checked']))
				{
					unset($item['checked']);
				}

				if($this->model->{$attribute}==$item['value'])
				{
					$item['checked'] = 'checked';
				}
			}
			
			$radioLabel = $item['value'];
			if(isset($item['label']))
			{
				$radioLabel = $item['label'];
				unset($item['label']);
			}

			$placeholders = array(
					'{radio}'=>$this->radioLabel($attribute,$radioLabel,$labelOptions,$this->radioInput($attribute,$item)),
			);

			$radioItems[] = str_replace(array_keys($placeholders),array_values($placeholders),$template);
		}
		return implode($separator,$radioItems);
	}
	
	/**
	 * Creates a drop down list on the web page
	 */
	public function dropDownList($attribute,array $items=array(),array $options=array())
	{
		$options['name'] = $this->getFormItemName($attribute);
		$selected = '';
		if($this->model->{$attribute}!==null)
		{
			$selected = $this->model->{$attribute};
		}
		$optionTags = $this->parseDownListOptions($items,$selected);
		return $this->tag('select',$optionTags,$options);
	}
	
	/**
	 * Parse the down list  items
	 */
	protected function parseDownListOptions(array $items=array(),$selected='')
	{
		$options = array();
		foreach($items as $text=>$option)
		{
			if($selected!='' and isset($option['value']) and $option['value']==$selected)
			{
				$option['selected'] = "selected";
			}else{
				if($this->model->getIsNewRecord()===false)
				{
					unset($option['selected']);
				}
			}
			$options[] = $this->tag('option',$option['label'],$option);
		}
		return implode($options);
	}
	
	/**
	 * Creates a custom tag
	 */
	public function tag($name,$text,array $options=array(),$closeTag=true)
	{
		$tag = '<'.$name.$this->getHtmlOptions($options);
		if($closeTag===true)
		{
			$tag .= '>'.$text.'</'.$name.'>';
		}else{
			$tag .= ' />';
		}
		return $tag;
	}
	
	/**
	 * Creates an reset button
	 * @param string $name
	 * @param array $options
	 * @return string
	 */
	public function resetButton($attribute, array $options=array())
	{
		return $this->input($attribute,'reset',$options);
	}
	
	/**
	 * Generate a button
	 * @param string $attribute
	 * @param array $options
	 * @return string
	 */
	public function button($attribute, array $options=array())
	{
		if($this->model instanceof Model)
		{
			$defaultAttribute = array(
				'id' => $this->getInputId($attribute),
				'value' => $this->model->$attribute,
			);
			$options = array_merge($options,$defaultAttribute);
		}
		return Html::tag('button',isset($options['value']) ? $options['value'] : $attribute,$options);
	}
	
	/**
	 * Create the form open tag
	 * @param string $action
	 * @param string $method
	 * @param array $options
	 * @return string
	 */
	public function beginForm($action,$method="post",array $options=array())
	{
		if(isset($options['id']))
		{
			$this->setFormId($options['id']);
		}else{
			$options['id'] = $this->getId();
		}

		$tokenTag = '';
		if($this->enableCSRFToken===true)
		{
			$token = \Lightworx::getApplication()->getCsrfToken();
			$tokenTag = '<input type="hidden" name="'.\Lightworx::getApplication()->csrfTokenName.'" value="'.$token.'" />';
		}
		return '<form action="'.$action.'" method="'.$method.'"'.$this->getHtmlOptions($options).'>'.$tokenTag;
	}
	
	/**
	 * Create the form close tag
	 */
	public function endForm()
	{
		return '</form>';
	}
	
	/**
	 * Add submit event for client,
	 * the added events will be perform before submitting.
	 * @param string $script script code
	 */
	public function addSubmitEvent($eventName,$script)
	{
		$this->submitEvents[$eventName] = $script;
	}

	/**
	 * This method will get the error message of the specified attribute.
	 * @param string $attribute
	 * @param string $template
	 * @return string
	 */
	public function getErrorMessage($attribute,$template='{message}')
	{
		$errors = $this->model->getErrors();
		if(isset($errors[$attribute]))
		{
			return str_replace('{message}',$errors[$attribute],$template);
		}
	}
	
	/**
	 * The method errorSummary is the alias of the method errorMessage.
	 */
	public function errorSummary($object=null)
	{
		return $this->errorMessage($object);
	}

	/**
	 * Display the error message.
	 * @param \Lightworx\Queryworx\Base\Model $object
	 * @return string
	 */
	public function errorMessage($object=null)
	{
		if($object!==null and is_array($object->getErrors()))
		{
			$this->addErrors($object->getErrors());
		}

		$this->addErrors($this->model->getErrors());
		if($this->errors!==array())
		{
			$errorMessage = array();
			foreach($this->errors as $error)
			{
				$errorMessage[] = '<li>'.$error.'</li>';
			}
			return '<div class="error">'.implode("",$errorMessage).'</div>';
		}
	}
	
	public function addErrors($errors)
	{
		if(!is_array($errors))
		{
			return ;
		}
		foreach($errors as $key=>$error)
		{
			$this->errors[$key] = $error;
		}
	}

	public function selectTree($attribute,array $items=array(),array $option=array(),$suojin=' ')
	{
		return $this->dropDownList($attribute,$items,$options);
	}

	public function radioTree($attribute,array $items=array(),$template='{radio}{label}',$separator=' ',$suojin=' ')
	{
 		return $this->radioList($attribute, $options, $template, $separator);
	}

	public function checkboxTree($attribute,array $items=array(),$template="{checkbox}{label}", $separator=' ')
	{
		return $this->checkboxList($attribute,$items,$template, $separator);
	}
	
	public function __destruct()
	{
		if($this->enableAutoSave===true)
		{
			$autoSaveScript = '';
			$this->addScriptCode($autoSaveScript);
		}

		$scriptCode = 'function '.$this->submitEventFunctionName.'(){'.implode("\n",$this->submitEvents).'};';
		$this->addScriptCode($scriptCode);
	}
}