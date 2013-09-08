<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Router.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Component\Routing;

class Router
{
	/**
	 * @var array The default routing rules.
	 */
	public $rules = array(
		'{module:[moduleName]}/{controller:\w+}/{action:\w+}'=>'{module}/{controller}/{action}',
		'{module:[moduleName]}/{controller:\w+}'=>'{module}/{controller}',
		'{module:[moduleName]}'=>'{module}',
		'{controller:\w+}/{action:\w+}'=>'{controller}/{action}',
		'{controller:\w+}'=>'{controller}'
	);
	
	/**
	 * @var array
	 */
	public $defaultGetParams = array('controller','action','module');
	
	/**
	 * @var array The request parameter
	 */
	public $properties = array();
	
	public $bindDomains = array();
	
	public function __construct(array $rules = array())
	{
		$route = new Route;
		$this->rules = array_merge($rules,$this->rules);
		$this->initialize();
		
		foreach($this->rules as $rule=>$mapping)
		{
			if($route->isMatched($rule,$mapping,$this->bindDomains))
			{
				$this->properties = is_array($mapping) ? $mapping : array();
				// merge default get params
				if(isset($mapping['params']) and is_array($mapping['params'])){
					$_GET = array_merge($mapping['params'],$_GET);
				}
				break;
			}
		}
		$this->properties = is_array($this->properties) ? array_merge($_GET,$this->properties) : $_GET;
	}
	
	public function initialize()
	{
		if(!isset(\Lightworx::getApplication()->conf['modules']) or \Lightworx::getApplication()->conf['modules']===array())
		{
			foreach($this->rules as $rule=>$mapping)
			{
				if(strpos($rule,'{module:[moduleName]}')===0 or strpos($rule,'{module:[moduleDomain]}')===0)
				{
					unset($this->rules[$rule]);
				}
			}
			return;
		}
		
		$modules = \Lightworx::getApplication()->conf['modules'];
		
		// binding module domain.
		foreach($modules as $moduleName=>$module)
		{
			if(isset($module['bindDomain']))
			{
				$this->bindDomains[$moduleName] =  $module['bindDomain'];
			}
		}
		foreach($this->rules as $rule=>$mapping)
		{
			unset($this->rules[$rule]);
			$rule = str_replace('{module:[moduleName]}','{module:('.implode('|',array_keys($modules)).')}',$rule);
			$this->rules[$rule] = $mapping;
		}
	}
	
	public function __get($name)
	{
		if($name=='action' and !isset($this->properties['action']))
		{
			$this->properties['action']=\Lightworx::getApplication()->defaultAction;
		}
		if(isset($this->properties[$name]))
		{
			return $this->properties[$name];
		}
		return;
	}
	
	/**
	 * check the request path is root
	 * @return boolean
	 */
	public function isRequestRoot()
	{
		$path = str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['REQUEST_URI']);
		if($path=='' or $path=='/' or strpos($path,'/?')===0)
		{
			return true;
		}
		return false;
	}
	
	public function stripEmptyParam(array $params)
	{
		foreach($params as $requestName=>$value)
		{
			if($params[$requestName]=='')
			{
				unset($params[$requestName]);
			}
		}
		return $params;
	}
	
	public function getAbsolutePath()
	{
		$url = '';
		if(isset($_SERVER['SCRIPT_NAME']))
		{
			$url = dirname($_SERVER['SCRIPT_NAME']);
		}
		
		if(isset($_SERVER['SCRIPT_NAME']) and isset($_SERVER['REQUEST_URI']) and strpos($_SERVER['REQUEST_URI'],$_SERVER['SCRIPT_NAME'])===0)
		{
			$url = $_SERVER['SCRIPT_NAME'];
		}
		
		if(isset($_SERVER['PATH_INFO']))
		{
			$url = $_SERVER['SCRIPT_NAME'];
		}
		
		return $url;
	}
	
	/**
	 * Creates an url string
	 * @param string $route
	 * @param array $params
	 * @param boolean $absoluteUrl defaults to true, use a absolute url, like the / root path
	 * @param boolean $mergeParam defaults to false, This param will merge query parameters.
	 * @param boolean $stripEmptyParam defaults to false, remove the empty param
	 * @return string
	 */
	public function createUrl($rule,array $params=array(),$absoluteUrl=true,$mergeParam=false,$stripEmptyParam=false)
	{
		// begin binding domain
		if(isset($_GET['module']) and !in_array($_SERVER['HTTP_HOST'],$this->bindDomains))
		{
			$rule = str_replace('{module:[moduleName]}',$_GET['module'],$rule);
		}
		
		if(in_array($_SERVER['HTTP_HOST'],$this->bindDomains))
		{
			$rule = str_replace('{module:[moduleName]}','',$rule);
		}
		// end binding domain

		if(strpos($rule,'{module:[moduleName]}')!==false)
		{
			$rule = str_replace('{module:[moduleName]}','',$rule);
		}
		
		preg_match_all('~{(\w+):?(.*?)?}~',$rule,$matched);
		
		if($mergeParam===true)
		{
			$params = $this->stripDefaultGetParams(array_merge($_GET,$params));
		}
		
		if($stripEmptyParam===true)
		{
			$params = $this->stripEmptyParam($params);
		}
		
		$params = array_map('urlencode',$params);
		
		if(isset($matched[0]) and count($matched[0])<=count($params))
		{
			$url = '';
			if($absoluteUrl===true)
			{
				$url .= $this->getAbsolutePath();
			}

			$replaceRule = trim($rule,'/');
			foreach($matched[0] as $key=>$value)
			{
				$param="";
				if(isset($params[$matched[1][$key]]))
				{
					$param = $params[$matched[1][$key]];
					unset($params[$matched[1][$key]]);
				}
				$replaceRule = str_replace($value,$param,$replaceRule);
			}
			
			$queryString = '';
			
			if(is_array($params) and count($params)>0)
			{
				if(strpos($replaceRule,'?')!==false)
				{
					$queryString = "&".http_build_query($params);
				}else{
					$queryString = '?'.http_build_query($params);
				}
			}
			
			if($url[strlen($url)-1]!='/')
			{
				$url .= '/';
			}
			return $url.$replaceRule.$this->getUrlSuffix($rule).$queryString;
		}
		return $this->createNormalUrl($rule,$params,$absoluteUrl);
	}
	
	/**
	 * Strip defaults param
	 * @param array $params
	 */
	public function stripDefaultGetParams(array $params)
	{
		foreach($this->defaultGetParams as $name)
		{
			if(isset($params[$name]))
			{
				unset($params[$name]);
			}
		}
		return $params;
	}
	
	/**
	 * Creates an normal url request string, 
	 * that does not contains any rewrite rules.
	 * @param unknown_type $rule
	 * @param array $params
	 */
	public function createNormalUrl($rule,array $params=array(),$absoluteUrl=true)
	{
		$url = $absoluteUrl===true ? '/' : "";
		$rule = trim($rule,'/');
		$params = array_map('urlencode',$params);
		$queryString = count($params)>0 ? "?".http_build_query($params) : "";
		
		if(!isset($this->rules[$rule]))
		{
			return $url.$rule.$queryString;
		}
		
		if(is_array($this->rules[$rule]) and isset($this->rules[$rule]['controller']))
		{
			$action = isset($this->rules[$rule]['action']) ? '/'.$this->rules[$rule]['action'] : '';
			return $url.$this->rules[$rule]['controller'].$action.$queryString;
		}
		
		if(is_string($this->rules[$rule]))
		{
			return $url.$this->rules[$rule].$queryString;
		}
		return $url;
	}
	
	/**
	 * Creates an absolute url
	 * @param string $rule
	 * @param array $params
	 * @param string $domain
	 * @param string $schema defaults to null
	 * @return string
	 */
	public function createAbsoluteUrl($rule,array $params=array(),$domain=null,$schema=null)
	{
		if($domain===null)
		{
			$domain = $_SERVER['HTTP_HOST'];
		}
		if($schema===null)
		{
			$schema = $this->getHttpSchema();
		}
		return $schema.$domain.$this->createUrl($rule,$params);
	}
	
	/**
	 * Get the request schema, if the request is an secure connection, 
	 * that will be return 'https://', otherwise return 'http://'
	 * @return string
	 */
	public function getHttpSchema()
	{
		return \Lightworx::getApplication()->request->isSecureConnection() ? 'https://' : 'http://';
	}
	
	/**
	 * Return the URL suffix, define the URL suffix should be like the following:
	 * <pre>
	 * array(
	 * '/post/read/{id:\d+}.html'=>array(
	 *                                    'controller'=>'post',
	 *                                    'action'=>'read',
	 *                                    'urlSuffix'=>'.html',
	 * 	                                  'caseSensitive'=>true));
	 * </pre>
	 * @param string $rule
	 */
	public function getUrlSuffix($rule)
	{
		if(!isset($this->rules[$rule]) or !is_array($this->rules[$rule]) or !isset($this->rules[$rule]['urlSuffix']))
		{
			return ;
		}
		return $this->rules[$rule]['urlSuffix'];
	}
}