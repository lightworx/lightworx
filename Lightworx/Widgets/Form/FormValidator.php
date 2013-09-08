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

class FormValidator extends Widget
{
	public $formId;
	public $message;
	public $rules;
	public $enableAjaxForm;
	public $submitHandlerName;
	
	public function run(){}
	
	public function init()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/Validator/jqueryValidate/',
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		self::publishResourcePackage('jqueryValidate',$config);
		
		$this->attachPackageScriptFile("jqueryValidate","jquery.validate.min.js");
		$this->getLocalizationMessage($config);
		$this->addJqueryCode('$("#'.$this->formId.'").validate({
							'.$this->formSubmitHandler().'
		});');
	}
	
	/**
	 * Set the client form submit handler, 
	 * when the property enableAjaxForm value is true.
	 */
	protected function formSubmitHandler()
	{
		if($this->enableAjaxForm===true)
		{
			return 'submitHandler:function(){'.$this->submitHandlerName.'();return false;},';
		}
	}
	
	/**
	 * Loading the localization messages file,
	 * that dependency a jQuery validation plugin.
	 * @param array $config
	 */
	public function getLocalizationMessage(array $config)
	{
		if($this->message!==null and isset($config['source']))
		{
			$messageFile = $config['source'].'localization/messages_'.$this->message.'.js';
			if(file_exists($messageFile))
			{
				$this->attachPackageScriptFile("jqueryValidate",'localization/messages_'.$this->message.'.js');
			}
			throw new \RuntimeException("The localization message file:".$config['source']." does not exist.");
		}
	}
}