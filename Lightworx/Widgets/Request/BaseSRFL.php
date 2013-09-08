<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Request;

use Lightworx\Foundation\Widget;
use Lightworx\Helper\Html;

class BaseSRFL extends Widget
{
	public $model;
	public $data = array();
	public $bindObject = '[srfl]';
	public $event = 'click';
	public $tokenName = 'csrf-token';
	public $attachTokenToBindObject = false;
	public $tagOptions = array();
	
	/**
	 * The request schema, if the http request need secure connection, 
	 * should to set to 'https://' defaults to null, means using the current schema.
	 * @var string defaults to null
	 */
	public $requestSchema = null;
	public $linkContent;
	public $linkOptions = array();
	public $linkParameters = array();
	public $createLink = false;
	public $requestDomain = null;
	
	/**
	 * Default request parameters. it will be initialized with method $this->init().
	 */
	public $requestParams;
	public $encryptMethod = 'encryptData';
	
	/**
	 * HTTP request methods
	 */
	protected $methods = array('get','post','put','delete');
	
	public function init()
	{
		$this->loadSRFL();
		$this->requestParams = $this->getApp()->SRFLRequestParams;
		$this->initRequestUrl();
		$this->initRequestMethod();
		
		if(!in_array(strtolower($this->tagOptions['request-method']),$this->methods))
		{
			throw new \RuntimeException("The HTTP request method not allowed.");
		}
		$this->addJqueryCode('$.fn.srfl.create({});');
	}
	
	public function run()
	{
		if($this->createLink)
		{
			echo Html::createLink($link,$this->linkContent,$this->linkOptions);
		}
	}
	
	public function loadSRFL()
	{
		$config = array(
			'source'=>LIGHTWORX_PATH.'Vendors/jQuery/',
			'filter'=>array('.svn','.txt','.DS_Store','.md')
		);
		self::publishResourcePackage('jQuery',$config);
		$this->attachPackageScriptFile("jQuery",'jquery.srfl.min.js');
	}
	
	public function initRequestMethod()
	{
		if(!isset($this->tagOptions['request-method']))
		{
			$this->tagOptions['request-method'] = 'put';
			if($this->model->getIsNewRecord()===true)
			{
				$this->tagOptions['request-method'] = 'post';
			}
		}
	}
	
	public function initRequestUrl()
	{
		if(!isset($this->tagOptions['request-url']))
		{
			$requestURI = $this->getRequestURI();
			$this->tagOptions['request-url'] = $this->getApp()->getRouter()->createAbsoluteUrl(
				$requestURI,
				$this->getRequestParams(),
				$this->requestDomain,
				$this->requestSchema
			);
		}
	}
	
	public function getRequestURI()
	{
		$requestURI = $this->getApp()->request->getRequestURI();
		if($this->model!==null and $this->getApp()->serviceControllerName!==null)
		{
			$model = lcfirst(is_object($this->model) ? get_class($this->model) : $this->model);
			$requestURI = $this->getApp()->serviceControllerName.'/'.$model;
		}
		return $requestURI;
	}
	
	public function getRequestParams()
	{
		$params = array();
		if($this->model->getIsNewRecord()===false)
		{
			$params = array_merge($this->linkParameters,array($this->model->primaryKeyName=>$this->model->primaryKey));
		}
		return $params;
	}
	
	// public function getRequestData()
	// {	
	// 	$requestName = $this->requestParams['requestName'];
	// 	$data = array_merge(json_decode($this->ajaxOptions['data'],true),$this->data);
	// 	$requestData = array($requestName=>$this->encryptData($data));
	// 	$predefinedQueryString = http_build_query(array_merge($requestData,$this->getAdditionalData()));
	// 	return $predefinedQueryString;
	// }
	
	public function encryptData($data)
	{
		if(is_callable($this->encryptMethod))
		{
			$encryptData = $this->encryptMethod($data);
		}else{
			$encryptData = $this->getApp()->user->encryptData($data);
		}
		return $encryptData;
	}
}