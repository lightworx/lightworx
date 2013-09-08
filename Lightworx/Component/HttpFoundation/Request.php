<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: Request.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Component\HttpFoundation;

use Lightworx\Component\Routing\Router;
use Lightworx\Component\HttpFoundation\Parameter;

class Request
{
	public $resource;
	public $server;
	public $router;
	public $headers;
	public $cookies;
	public $session;
	public $request;
	public $method;
	public $query;
	public $ip;

	/**
	 * when the value to `true` means is a XMLHttpRequest, 
	 * `false` indicate is not a XMLHttpRequest.
	 * @var boolean 
	 */
	public $xmlHttpRequest;
	public $forceSecureConnection = false;
	
	protected $methods = array('GET','POST','HEAD','PUT','DELETE', 'OPTIONS','TRACE');
	
	public function __construct(Router $router)
	{
		$this->enableSecureConnection();
		if(function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc())
		{
			if(isset($_GET))
			{
				$_GET = $this->stripSlashes($_GET);
			}
			if(isset($_POST))
			{
				$_POST = $this->stripSlashes($_POST);
			}
			if(isset($_REQUEST))
			{
				$_REQUEST = $this->stripSlashes($_REQUEST);
			}
			if(isset($_COOKIE))
			{
				$_COOKIE = $this->stripSlashes($_COOKIE);
			}
			if(isset($_SERVER))
			{
				$_SERVER = $this->stripSlashes($_SERVER);
			}
			if(isset($_ENV))
			{
				$_ENV = $this->stripSlashes($_ENV);
			}
		}
		
		$this->setRouter($router);
		
		$this->server  = new Parameter($_SERVER);
		$this->post    = new Parameter($_POST);
		$this->file    = new Parameter($_FILES);
		$this->request = new Parameter($_REQUEST);
		$this->query   = new Parameter($_GET);
		$this->cookies = new Parameter($_COOKIE);
		$this->session = new Parameter(isset($_SESSION) ? $_SESSION : array());
		$this->headers = new Parameter($this->headerHandle());

		$this->ip       = $this->getClientIpAddress();
		$this->resource = $this->getRequestResource();
		$this->method   = $this->getRequestMethod();
		$this->xmlHttpRequest = $this->headers->get('X_REQUESTED_WITH') == 'XMLHttpRequest';
		
	}
	
	public function __call($method,$value)
	{
		$requestMethod = strtolower(substr($method,2));
		if(substr($method,0,2)=='is' and in_array($requestMethod,array('post','get','put','delete')))
		{
			if(strtolower($this->getRequestMethod())==$requestMethod)
			{
				return true;
			}
			return false;
		}
	}
	
	public function redirect($url,$terminate=true,$statusCode=302)
	{
		header("location:".$url,true,$statusCode);
		if($terminate===true)
		{
			exit;
		}
	}
	
	/**
	 * Remove all the slashes
	 * @param mixed $data
	 * @return mixed
	 */
	public function stripSlashes(&$data)
	{
		return is_array($data) ? array_map(array($this,"stripslashes"),$data) : stripslashes($data);
	}
	
	/**
	 * Return the http header information
	 * @return array
	 */
	public function headerHandle()
	{	
		$headers = array();
		if(is_array($this->server->all()))
		{
			foreach($this->server->all() as $key=>$val)
			{
				if('http_' == strtolower(substr($key,0,5)))
				{
					$headers[substr($key,5)] = $val;
				}
			}
		}
		return $headers;
	}


	public function enableSecureConnection()
	{
		if($this->forceSecureConnection===true and $this->isSecureConnection()===false)
		{
			$this->redirect('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		}
	}
	
	/**
	 * Return the request resource, defaults to 'text/html'
	 * @return string
	 */
	public function getRequestResource()
	{
		return $this->headers->get('LIGHTWORX_RESOURCE')===null ? 'text/html' : $this->headers->get('LIGHTWORX_RESOURCE');
	}
	
	/**
	 * Return the request csrf token, defaults to return null
	 * @return string
	 */
	public function getCsrfToken()
	{
		return $this->headers->get('CSRF_TOKEN')===null ? null : $this->headers->get('CSRF_TOKEN');
	}
	
	/**
	 * Get the browser language.
	 * @return string
	 */
	public function getLanguage()
	{
		$languages = array();
		$acceptLanguage = $this->headers->get('ACCEPT_LANGUAGE');
		if(strpos($acceptLanguage,',')!==false)
		{
			$languages = explode(',',$acceptLanguage);
		}
		return $languages[0];
	}
	
	public function getCharset()
	{
		return $this->headers->get('ACCEPT_CHARSET');
	}
	
	public function getEncode()
	{
		return $this->headers->get('ACCEPT_ENCODING');
	}
	
	/**
	 * Returns the cookie with the specified name
	 * @param string $name
	 * @return string
	 */
	public function getCookie($name)
	{
		return $this->cookies->get($name);
	}
	
	/**
	 * Get all the cookies.
	 * @return array
	 */
	public function getCookies()
	{
		return $this->cookies->all();
	}
	
	/**
	 * Returns the user session with the specified name.
	 * @param string $name
	 * @return string
	 */
	public function getSession($name)
	{
		return $this->session->get($name);
	}
	
	/**
	 * Get all the sessions
	 * @return array
	 */
	public function getSessions()
	{
		return $this->session->all();
	}
	
	/**
	 * Return the browser request method
	 * @return string
	 */
	public function getRequestMethod()
	{
		$method = $this->server->get('REQUEST_METHOD');
		if(in_array(strtoupper($method),$this->methods))
		{
			return $method;
		}
		return ;
	}
	
	public function setRequestMethod($method)
	{
		$this->server->set('REQUEST_METHOD',$method);
	}
	
	/**
	 * Get all the header information.
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers->all();
	}
	
	/**
	 * Set header information, if the header item was exists, that will be replaced.
	 * @return array
	 */
	public function setHeaders($header)
	{
		return array_replace($this->headers,$header);
	}
	
	/**
	 * Get the browser whether has a file to uploading.
	 * @return boolean
	 */
	public function hasFileUpload()
	{
		return stripos($this->server->get('CONTENT_TYPE'),'multipart/form-data')!==false;
	}
	
	/**
	 * Get whether a XMLHttpRequest or not.
	 * @return boolean
	 */
	public function isXMLHttpRequest()
	{
		return $this->xmlHttpRequest;
	}
	
	/**
	 * Alias of the isXMLHttpRequest
	 * @return boolean
	 */
	public function isAjaxRequest()
	{
		return $this->isXMLHttpRequest();
	}
	
	public function setXmlHttpRequest($flag)
	{
		$this->xmlHttpRequest = (boolean)$flag;
	}
	
	/**
	 * Get the client ip address
	 * @return string
	 */
	public function getClientIpAddress()
	{
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
		{
			$onlineip = getenv('HTTP_CLIENT_IP');
		}
		
		if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
		{
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		}
		
		if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
		{
			$onlineip = getenv('REMOTE_ADDR');
		}
		
		if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
		{
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		return $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	}
	
	/**
	 * Sets a router object
	 * @param router $router
	 */
	public function setRouter($router)
	{
		$this->router = $router;
	}
	
	/**
	 * Return Router object.
	 * @return Router
	 */
	public function getRouter()
	{
		return $this->router;
	}
	
    public function getScheme()
    {
		return $this->isSecureConnection() ? 'https' : 'http';
    }
	
	public function isSecureConnection()
	{
		return ($this->server->get('SERVER_PORT') == '443') ? true : false;
	}
	
	public function getSrciptName()
	{
		return $this->server->get('SCRIPT_NAME');
	}

    public function getPort()
    {
        return $this->server->get('SERVER_PORT');
    }

	public function getRequestTime()
	{
		return $this->server->get('REQUEST_TIME');
	}
	
	public function getBrowserCompressType()
	{
		if(isset($_SERVER["HTTP_ACCEPT_ENCODING"]))
		{
			$HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"];
			
			if(strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false)
			{
				return 'x-gzip';
			}
			
		    if(strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false)
			{
				return 'gzip';
			}
		}
		return false;
	}

	
	public function getHostName()
	{
		return $this->server->get('HTTP_HOST');
	}
	
	public function getRequestURI()
	{
		return $this->server->get('REQUEST_URI');
	}
	
	public function getHttpReferer()
	{
		return $this->server->get('HTTP_REFERER');
	}
}