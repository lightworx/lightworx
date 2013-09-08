<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: Renderer.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Component\Renderer;

use Lightworx\Foundation\Object;
use Lightworx\Foundation\Kernel;
use Lightworx\Controller\Controller;
use Lightworx\Foundation\Application;
use Lightworx\Foundation\Module;
use Lightworx\Component\Translation\ViewTranslator;
use Lightworx\Component\HttpFoundation\AssetManager;

class Renderer extends BaseRenderer
{
	/**
	 * The view extension name, defaults to '.php'
	 * @var string
	 */
	public $viewExtension = '.php';
	
	public function __construct()
	{
		if(!(\Lightworx::getApplication() instanceof Application))
		{
			return ;
		}
		
		if(property_exists(\Lightworx::getApplication(),'viewPath'))
		{
			$this->setViewPath(Kernel::getApplicationPath().\Lightworx::getApplication()->viewPath);
		}
		
		if(property_exists(\Lightworx::getApplication(),'themePath'))
		{
			$this->setThemePath(Kernel::getApplicationPath().\Lightworx::getApplication()->themePath);
		}
	}
	
	/**
	 * adding css code to page
	 * @param string $code
	 * @param mixed $flag
	 */
	public function addCssCode($code,$flag=null)
	{
		AssetManager::addCssCode($code,$flag);
	}
	
	/**
	 * adding script code to page
	 * @param string $code
	 * @param mixed $flag
	 */
	public function addScriptCode($code,$flag=null)
	{
		AssetManager::addScriptCode($code,$flag);
	}
	
	/**
	 * adding script code to page
	 * @param string $code
	 * @param boolean $enableReadyFunc whether use the jquery ready function or not,
	 *                defaults to true.
	 * @param mixed If specified $flag, when repeatedly create the same object, the code will only be loaded once.
	 *              defaults to null
	 */
	public function addJqueryCode($code,$enableReadyFunc=true,$flag=null)
	{
		if($enableReadyFunc===true)
		{
			AssetManager::addJqueryCode($code,$flag);
		}else{
			$this->addScriptCode($code,$flag);
		}
	}
	
	public function render($controller,$name,$data=null,$return=false)
	{
		if(!property_exists($controller,'layout') or !property_exists($controller,'layoutPath'))
		{
			throw new \RuntimeException("Undefined property: layout or layoutPath");
		}
		
		if(!is_object($this->getController()) and ($controller instanceof Controller))
		{
			$this->setController($controller);
		}
		
		$content = $output  = '';
		
		if($controller->layout===false or $controller->layout===null)
		{
			$output = $this->renderPartial($name,$data);
		}
		
		if($controller->layout!==false and $controller->layout!=$name)
		{
			$content = $this->renderPartial($name,$data);
		}
		
		if($controller->layout!==false or $controller->layout!="")
		{
			$output = $this->renderPartial($controller->layoutPath.$controller->layout,$content);
		}

		if($return===true)
		{
			return $output;
		}
		echo $output;
	}
	
	/**
	 * Renders a partial of the view file
	 * @param string $name
	 * @param mixed $data
	 */
	public function renderPartial($name,$data=null)
	{
		\Lightworx::getApplication()->eventNotify('render.'.$this->ensureEventName($name),$this);
		$viewFile = $this->getViewFile($name);
		$this->setViewMessageFile($name);
		return $this->resolveView($viewFile,$data);
	}

	public function ensureEventName($name)
	{
		return str_replace(array("/","\\"), '.', $name);
	}
	
	/**
	 * Gets a view file, if the current controller specified the property theme,
	 * that will trying to find the theme file and return, if it is exists.
	 * @param string $viewName the file name of the view and contained a part of the path
	 * @param string $extension the extension name of the view file
	 * @throws \RuntimeException
	 */
	public function getViewFile($viewName)
	{
		if(\Lightworx::getApplication()->theme!==null)
		{
			$viewFile = $this->getThemePath().\Lightworx::getApplication()->theme.'/'.$viewName.$this->viewExtension;
			if(file_exists($viewFile))
			{
				return $viewFile;
			}
		}
		
		if(is_object($this->getController()) and $this->getController()->theme!==null)
		{
			$viewFile = $this->getThemePath().$this->getController()->theme.'/'.$viewName.$this->viewExtension;
			if(file_exists($viewFile))
			{
				return $viewFile;
			}
		}
		
		$viewFile = $this->getViewPath().$viewName.$this->viewExtension;
		
		if(!file_exists($viewFile))
		{
			throw new \RuntimeException("view file ".$viewFile." not found.");
		}
		return $viewFile;
	}
	
	/**
	 * Resolve view file, if the param $data is an array, 
	 * that will extracting out key as a variable from array data.
	 * @param string $viewFile
	 * @param mixed $data
	 */
	public function resolveView($viewFile,$data=null)
	{
		if(is_array($data))
		{
			extract($data,EXTR_PREFIX_SAME,'data');
		}else{
			$content = $data;
		}
		
		if(file_exists($viewFile))
		{
			ob_start();
			require $viewFile;
			ob_implicit_flush();
			return ob_get_clean();
		}
		return false;
	}
	
	/**
	 * redirect a new URL
	 * @param string $url
	 * @param boolean $terminate defaults to true
	 * @param integer $statusCode defaults to 302
	 */
	public static function redirect($url,$terminate=true,$statusCode=302)
	{
		header("location:".$url,true,$statusCode);
		if($terminate===true)
		{
			exit;
		}
	}
}