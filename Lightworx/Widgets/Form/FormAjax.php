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

/**
 * The widget FormAjax dependency the jQuery plugin jQuery.Form to submit the form.
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @since 0.1
 * @version $Id$
 */

class FormAjax extends Widget
{
	public $formId;
	public $options = array();
	public $submitHandlerName;
	public $enableClientValidation;
	public $submitEventFunctionName;
	
	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/jQuery/',
		);
		self::publishResourcePackage('jquery',$config);		
		$this->attachPackageScriptFile("jquery","jquery.form.js");
	}
	
	/**
	 * Running widget FormAjax
	 */
	public function run()
	{
		if($this->enableClientValidation)
		{
			$this->addScriptCode($this->getFormSubmitFunction());
		}else{
			$this->addJqueryCode($this->getFormSubmitCode());
		}
	}
	
	/**
	 * Return the form submit code
	 * @return string
	 */
	public function getFormSubmitCode()
	{
		return 'var options = {'.$this->getFormAjaxOptions().'};
				$("#'.$this->formId.'").submit(function(){
					$(this).ajaxSubmit(options);
					return false;
				});';
	}
	
	/**
	 * Creates a submit function
	 * @param string $name
	 */
	protected function getFormSubmitFunction()
	{
		return 'function '.$this->submitHandlerName.'(){
				'.$this->getSubmitEventFunctionName().'
				var options = {'.$this->getFormAjaxOptions().'};
				$("#'.$this->formId.'").ajaxSubmit(options);
		}';
	}
	
	protected function getFormAjaxOptions()
	{
		$properties = array();
		foreach($this->options as $property=>$value)
		{
			$properties[] = $property.':'.$value;
		}
		return implode(",\n",$properties);
	}
	
	public function getSubmitEventFunctionName()
	{
		return $this->submitEventFunctionName.'();';
	}
}