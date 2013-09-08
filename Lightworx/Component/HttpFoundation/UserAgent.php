<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Parameter.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */


namespace Lightworx\Component\HttpFoundation;

class UserAgent
{
	/**
	 * The HTTP User agent string.
	 */
	public $uas;
	
	public $filters = array('GoogleChrome','SafariVersion','OperaVersion','Msie','Android');
	
	protected $browsers = array(
		'msie',
		'chrome',
		'safari',
		'firefox',
		'opera',
		'android',
		'netscape',
		'gecko',
		'webkit',
		'konqueror'
	);
	
	protected $operateSystems = array(
		'Windows',
		'Macintosh',
		'iOS',
		'Chrome os',
		'Android',
		'Linux',
		'Unix'
	);
	
	protected $browserEngines = array(
		'Amaya',
		'Gecko',
		'KHTML',
		'Presto',
		'Prince',
		'Trident',
		'WebKit',
		'Inactive',
		'layout',
		'engines',
		'Boxely',
		'Gazelle',
		'GtkHTML',
		'HTMLayout',
		'iCab',
		'Mariner',
		'Tasman',
		'Tkhtml'
	);
	
	
	private $_infos = array();
	
	public $browserName;
	public $browserVersion;
	public $operateSystem;
	public $browserEngine;
	
	public function __construct($uas=null)
	{
		if($uas===null)
		{
			$this->uas = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
		}else{
			$this->uas = strtolower($uas);
		}
		
		$matched = array();
		preg_match_all('#('.implode('|', $this->browsers).')[/ ]+([0-9]+(?:\.[0-9]+)?)#',$this->uas,$matched);
		if(isset($matched[1]) and end($matched[1])!='')
		{
			$this->browserName = end($matched[1]);
		}
		if(isset($matched[2]) and end($matched[2])!='')
		{
			$this->browserVersion = end($matched[2]);
		}
		
		$matched = array();
		preg_match('#'.implode('|', array_map('strtolower',$this->operateSystems)).'#',$this->uas,$matched);
		if(isset($matched[0]))
		{
			$this->operateSystem = $matched[0];
		}
		
		$matched = array();
		preg_match('#'.implode('|', array_map('strtolower',$this->browserEngines)).'#',$this->uas,$matched);
		if(isset($matched[0]))
		{
			$this->browserEngine = $matched[0];
		}
		
		foreach($this->filters as $filter)
		{
			$filterMethod = 'filter'.$filter;
			if(method_exists($this,$filterMethod))
			{
				$this->$filterMethod();
			}
		}
	}
	
	public function __call($method,$value)
	{
		$methods = array('BrowserName','BrowserVersion','BrowserEngine','OperateSystem');
		if(substr($method,0,3)=='get' and in_array(substr($method,3),$methods))
		{
			$method = lcfirst(substr($method,3));
			return $this->$method;
		}
	}
	
	public function attachBrowser($browserName)
	{
		$this->browsers[] = $browserName;
	}
	
	public function attachSystem($systemName)
	{
		$this->operateSystems[] = $systemName;
	}

	protected function filterGoogleChrome()
	{
		if('safari'===$this->browserName and strpos($this->uas,'chrome/'))
		{
			$this->browserName = 'chrome';
			$this->browserVersion = preg_replace('|.+chrome/([0-9]+(?:\.[0-9]+)?).+|', '$1', $this->uas);
		}
	}

	protected function filterSafariVersion()
	{
		if('safari'===$this->browserName and strpos($this->uas,' version/'))
		{
			$this->browserVersion = preg_replace('|.+\sversion/([0-9]+(?:\.[0-9]+)?).+|', '$1', $this->uas);
		}
	}

	protected function filterOperaVersion()
	{
		if('opera'===$this->browserName and strpos($this->uas, ' version/'))
		{
			$this->browserVersion = preg_replace('|.+\sversion/([0-9]+\.[0-9]+)\s*.*|', '$1', $this->uas);
		}
	}

	protected function filterMsie()
	{
		if('msie'===$this->browserName and empty($this->browserEngine))
		{
			$this->browserEngine = 'trident';
		}
	}

	protected function filterAndroid()
	{
	    if('safari'===$this->browserName and strpos($this->uas, 'android '))
		{
			$this->browserName = 'android';
			$this->operateSystem = 'android';
			$this->browserVersion = preg_replace('|.+android ([0-9]+(?:\.[0-9]+)+).+|', '$1', $this->uas);
	    }
	}
}