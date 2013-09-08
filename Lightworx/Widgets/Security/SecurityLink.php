<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Security;

use Lightworx\Foundation\Widget;
use Lightworx\Helper\Html;
use Lightworx\Component\Encryption\CryptString;
use Lightworx\Component\Encryption\XorEncrypt;

class SecurityLink extends Widget
{
	public $enableAjax = true;
	public $encrypt;
	public $link;
	public $requestFieldName = '_security';
	public $data = array();
	public $linkContent;
	public $linkOptions = array();
	public $model;
	protected $methods = array('get','post','put','delete');
	
	public $linkParameters = array();
	public $requestDomain = null;
	
	/**
	 * This function is invoked before creates a HTTP request.
	 * @var string
	 */
	public $beforeFunction = 'function(){}';
	
	/**
	 * When the service request completed, the completeFunction will be call.
	 * @var string
	 */
	public $successFunction = 'function(){}';
	
	/**
	 * @see the jquery function ajax option 'dataType'
	 */
	public $requestDataType = 'html';
	
	public $additionalData = array('_success'=>'1','_fail'=>'0');
	
	/**
	 * The property method defined the request method, 
	 * the method should be one of $this->methods, defaults to post.
	 * @var string
	 */
	public $method = 'post';
	
	public function init()
	{
		if(!in_array($this->method,$this->methods))
		{
			throw new \RuntimeException("The logout method should be one of ".implode(",",$this->methods));
		}
		
		$this->addJqueryCode('$("'.$this->getId(true).'").live("click",function(){
			$.ajax({
					beforeSend:'.$this->beforeFunction.',
		  			type: "'.$this->method.'",
		  			url: $(this).attr("href"),
		  			dataType: "'.$this->requestDataType.'",
					data:{'.$this->requestFieldName.':"'.$this->encryptData().'"'.$this->getAdditionalData().'},
					success:'.$this->successFunction.'
			});
			return false;
		});');
	}
	
	public function getAdditionalData()
	{
		$data = array();
		foreach($this->additionalData as $attribute=>$value)
		{
			$data[] = $attribute.':"'.$value.'"';
		}
		
		if($data!==array())
		{
			return ','.implode(", \n",$data);
		}
		return ;
	}
	
	public function run()
	{
		$app = $this->getApp();
		if($this->link===null)
		{
			if($this->model!==null and $app->serviceControllerName!==null)
			{
				$modelName = lcfirst(get_class($this->model));
				$serviceControllerName = $app->serviceControllerName;
				$this->link = $app->getRouter()->createAbsoluteUrl($serviceControllerName.'/'.$modelName,
																	$this->linkParameters,
																	$this->requestDomain
				);
			}else{
				$this->link = $app->getRouter()->createAbsoluteUrl($app->request->getRequestURI(),
																	$this->linkParameters,
																	$this->requestDomain
				);
			}
		}
		$this->linkOptions['id'] = $this->getId();
		echo Html::createLink($this->link,$this->linkContent,$this->linkOptions);
	}
	
	public function encryptData()
	{
		return base64_encode(XorEncrypt::encrypt(serialize($this->data),CryptString::getState()));
	}
}