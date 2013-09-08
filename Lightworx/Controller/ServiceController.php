<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Controller;

use Lightworx\Controller\Controller;
use Lightworx\Exception\HttpException;
use Lightworx\HttpFoundation\Request;
use Lightworx\Component\Encryption\CryptString;
use Lightworx\Component\Encryption\XorEncrypt;

class ServiceController extends Controller
{	
	public $params;
	public $requestMethod;
	public $responseFormat;
	public $httpData = array();
	public $modelAttributes = array();
	
	
	protected $csrfTokenRequired = true;
	protected $_model;
	
	public function __construct()
	{
		$this->requestMethod = strtolower(\Lightworx::getApplication()->request->getRequestMethod());
		$this->params = \Lightworx::getApplication()->SRFLRequestParams;
		$this->initialize();
	}
	
	public function initialize(){}
	
	public function __call($method,$value)
	{
		$result = $insert = false;
		if($this->requestMethod=='post')
		{
			$insert = true;
		}
		
		if(substr($method,0,6)!='action')
		{
			return;
		}

		$modelName = substr($method,6);
		$model = $this->loadModel($modelName,$insert);
		$beforeProcess = $this->beforeProcess($model);
		
		if($model!==null and $beforeProcess===true)
		{
			$result = $this->process($model);
		}
		$this->afterProcess($result);

		return $result;
	}
	
	/**
	 * Process the request
	 */ 
	public function process($model)
	{
		$modelName = get_class($model);
		if($this->requestMethod=='get')
		{
			return $model;
		}
		
		if($this->csrfTokenRequired===true and \Lightworx::getApplication()->user->validateCsrfToken===false)
		{
			$message = array($this->__('invalid CSRF token.'));
			throw new \Lightworx\Exception\HttpException(500,json_encode($message));
		}
		
		$method = 'process'.ucfirst($this->requestMethod);


		$result = false;
		if(method_exists($this,$method))
		{
			$result = $this->{$method}($model,$modelName);
		}
		
		if($result===false)
		{
			throw new \Lightworx\Exception\HttpException(500,json_encode($model->getErrors()));
		}
		return $result;
	}

	/**
	 * Process the post request
	 * @param ActiveRecord $model
	 * @param string $modelName
	 * @return boolean
	 */
	protected function processPost($model,$modelName)
	{
		$result = false;
		if(isset($this->httpData[$modelName]))
		{
			$model->attributes = $this->httpData[$modelName];
			$result = $model->save();
		}
		return $result;
	}
	
	/**
	 * Process the put request
	 * @param ActiveRecord $model
	 * @param string $modelName
	 * @return boolean
	 */
	protected function processPut($model,$modelName)
	{
		$result = false;
		if(isset($this->httpData[$modelName]))
		{
			$model->attributes = $this->httpData[$modelName];
			$result = $model->save();
		}
		return $result;
	}

	/**
	 * Process the delete request
	 * @param ActiveRecord $model
	 * @param string $modelName
	 * @return boolean
	 */
	protected function processDelete($model,$modelName)
	{
		return $model->delete();
	}
	
	public function beforeProcess($model)
	{
		$requestMethod = strtoupper($this->requestMethod);
		parse_str(file_get_contents('php://input'), $this->httpData);

		if(in_array($requestMethod,array('post','put')) and $this->httpData===array())
		{
			$message = array($this->__('The submit data cannot be empty.'));
			throw new \Lightworx\Exception\HttpException(500,json_encode($message));
		}
		
		// $this->httpData = \Lightworx::getApplication()->request->stripSlashes($this->httpData); // strip slashes
		$requestName = isset($this->params['requestName']) ? $this->params['requestName'] : '';
		
		if(isset($this->httpData[$requestName]))
		{
			$this->modelAttributes = \Lightworx::getApplication()->user->decryptData($this->httpData[$requestName]);
		}
		return true;
	}
	
	public function afterProcess($result=true)
	{
		$success = isset($this->params['success']) ? $this->params['success'] : '';
		$fail = isset($this->params['fail']) ? $this->params['fail'] : '';
		
		if($result===true and isset($this->httpData[$success]))
		{
			echo $this->httpData[$success];
			exit;
		}
		
		if($result===false and isset($this->httpData[$fail]))
		{
			echo $this->httpData[$fail];
			exit;
		}
	}
	
	/**
	 * Get the response format, like the xml, json, etc.
	 */
	public function getResponseFormat(){}
	
	protected function loadModel($name,$insert=false)
	{
		if($this->_model===null and $insert===false)
		{
			$pk = $name::model()->getPrimaryKeyName();
			if(isset($_GET[$pk]) and !empty($_GET[$pk]))
			{
				$this->_model=$name::model()->findByPk(array($pk=>$_GET[$pk]));
				$this->_model->setIsNewRecord(false);
				
				if($this->_model===null)
				{
					throw new HttpException(404,"The WebService cannot be found: ".$_GET[$pk]);
				}
			}else{
				throw new HttpException(403,"The request parameter is invalid");
			}
		}
		
		if($insert===true)
		{
			return new $name();
		}
		return $this->_model;
	}
}