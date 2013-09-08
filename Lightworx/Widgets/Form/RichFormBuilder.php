<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Form;

use Lightworx\Widgets\Form\FormBuilder;


class RichFormBuilder extends FormBuilder
{
	

	public function AutoCompleteTextInput()
	{
		return;
	}

	/**
	 * Create a calendar input
	 * @param string $attribute
	 * @param array $options
	 * @param array $datepickerOptions
	 * @return string
	 */
	public function getDatepicker($attribute,array $options=array(),array $datepickerOptions=array())
	{
		if(!isset($options['id']))
		{
			$options['id'] = $this->getInputId($attribute);
		}
		
		if(!isset($datepickerOptions['selector']))
		{
			$datepickerOptions['selector'] = '#'.$options['id'];
		}
		$render = $this->getRender();
		$datepicker = $render->createWidget("Lightworx.Widgets.Calendar.Datepicker",$datepickerOptions);
		$datepicker->run();
		
		return $this->textInput($attribute,$options);
	}
	
	/**
	 * Generate a CKEditor
	 * @param string $attribute
	 * @param array $options
	 * @param array $editorOptions
	 * @return string
	 */
	public function getCKEditor($attribute,array $options=array(),array $editorOptions=array())
	{
		if(!isset($editorOptions['editorId']))
		{
			$editorOptions['editorId'] = $this->getInputId($attribute);
		}
		
		if($this->enableAjaxForm)
		{
			$this->addSubmitEvent("CKEditor","
			 	for ( instance in CKEDITOR.instances )
			  		CKEDITOR.instances[instance].updateElement();
			");
		}
		
		$this->getRender()->widget("Lightworx.Widgets.Editor.CKEditor",$editorOptions);
		
		if(isset($options['class']))
		{
			if(strpos($options['class'],'ckeditor')===false)
			{
				$options['class'] = " ckeditor";
			}
		}else{
			$options['class'] = "ckeditor";
		}
		return $this->textArea($attribute,$options);
	}
	
	/**
	 * Generate a TinyMCE editor
	 * @param string $attribute
	 * @param array $options
	 * @param array $editorOptions
	 * @return string
	 */
	public function getTinyMCE($attribute,array $options=array(),array $editorOptions=array())
	{
		if(!isset($editorOptions['editorId']))
		{
			$editorOptions['editorId'] = $this->getInputId($attribute);
		}
		$this->getRender()->widget("Lightworx.Widgets.Editor.TinyMCE",$editorOptions);
		return $this->textArea($attribute,$options);
	}
	
	
	public function getWYSIHtml5($attribute,array $options=array(),array $editorOptions=array())
	{
		if(!isset($editorOptions['editorId']))
		{
			$editorOptions['editorId'] = $this->getInputId($attribute);
		}
		$this->getRender()->widget("Lightworx.Widgets.Editor.WYSIHtml5",$editorOptions);
		return $this->textArea($attribute,$options);
	}
}