<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Routing;

class Route
{
	/**
	 * Whether the rule is matched with current request URI or not.
	 * @param string $rule the routing rule
	 * @param mixed $mapping the mapping rules, the data type can be a string or an array.
	 * @param mixed $requestURI the requestURI can be custom, defaults to null
	 * @return boolean
	 */
	public function isMatched($rule,$mapping,array $bindDomains = array(),$requestURI=null)
	{
		$matched = $params = array();
		if($requestURI===null)
		{
			$requestURI = $this->getRequestURI();
		}
		
		// binding domains
		$domain = '';
		if(in_array($_SERVER['HTTP_HOST'],$bindDomains))
		{
			$domain = $_SERVER['HTTP_HOST'];
			$bindDomains = array_flip($bindDomains);
			$_GET['module'] = $bindDomains[$domain];
			$rule = preg_replace('~{module:(.*?)}(\/.*?)~','',$rule);
		}
				
		$matchUrlSuffix = $urlSuffix = '';
		if(is_array($mapping) and isset($mapping['matchUrlSuffix']) and $mapping['matchUrlSuffix']===true and isset($mapping['urlSuffix']))
		{
			$urlSuffix = $mapping['urlSuffix'];
			$matchUrlSuffix = '$';
		}
		
		$url =  preg_replace('~{(\w+):?(.*?)?}~','($2)',$rule).$urlSuffix;

		$caseSensitive = "";
		if(is_array($mapping) and isset($mapping['caseSensitive']) and $mapping['caseSensitive']===true)
		{
			$caseSensitive = "i";
		}
		
		$url = str_replace(array('((','))'),array('(',')'),$url);
		preg_match('~^'.$url.$matchUrlSuffix.'~'.$caseSensitive, parse_url($requestURI, PHP_URL_PATH), $matched);

		if(is_array($matched) and count($matched))
		{
			preg_match_all('~{(\w+):?(.*?)?}~',$rule,$params);
			array_shift($matched);
			if(isset($params[1]) and is_array($params[1]))
			{
				$this->setRequestParameters($params[1],$matched);
			}
			return true;
		}
		return false;
	}
	
	public function getRequestURI()
	{
		if(isset($_SERVER['PATH_INFO']))
		{
			$requestURI = $_SERVER['PATH_INFO'];
		}else{
			$requestURI = substr($_SERVER['REQUEST_URI'],strlen(dirname($_SERVER['SCRIPT_NAME'])));
			$requestURI = str_replace($_SERVER['SCRIPT_NAME'],'',$requestURI);
		}
		if(isset($requestURI[0]) and $requestURI[0]=='/')
		{
			$requestURI = substr($requestURI,1);
		}
		return $requestURI;
	}
	
	public function setRequestParameters(array $params,array $matched)
	{
		foreach($params as $key=>$val)
		{
			if(!isset($_GET[$val]))
			{
				$_GET[$val] = isset($matched[$key]) ? $matched[$key] : '';
			}
		}
	}
}