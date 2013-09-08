<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: BaseHandler.php 29 2011-10-04 05:22:03Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

use Lightworx\Exception\FileNotFoundException;
use Lightworx\Exception\HttpException;
use Lightworx\Component\Renderer\Renderer;
use Lightworx\Component\HttpFoundation\Header;

class BaseHandler extends Object
{
	public $layout;
	public $layoutPath;
	public $header;
	public $viewPath;
	public $viewFile;
	public $theme;
	
	protected $view;
	protected $status;
	
	private $_renderer;
	
	/**
	 * Set the header instance and set the view path.
	 */
	public function initialize()
	{
		$this->setHeader(Header::getInstance());
		$this->setViewPath(LIGHTWORX_PATH.'Resource/Views/');
	}
	
	/**
	*  Send header information to browser
	*/
	public function sendHeader()
	{
		if(!headers_sent() and isset($this->header->status[$this->status]))
		{
			header('HTTP/1.0 '.$this->header->status[$this->status]);
		}
	}
	
	/**
	*  render the error view
	*/
	public function render($name,$data=null,$return=false)
	{
		@ob_end_clean();
		$request = \Lightworx::getApplication()->request;
		if(is_object($request) and $request->isXMLHttpRequest() and isset($data['exception']))
		{
			$exception = $data['exception'];
			if(get_class($exception)!=='Lightworx\Exception\HttpException')
			{
				$message = array($data['exception']->getMessage());
				header(HttpException::$headerErrorName.':'.json_encode($message));
			}
		}

		$renderer = $this->getRenderer();
		if($renderer===false or !($this->_renderer instanceof Renderer))
		{
			return false;
		}
		
		if(is_array($name))
		{
			foreach($name as $key=>$val)
			{
				$this->_renderer->render($this,str_replace('.','/',$val),$data,$return);
			}
			return;
		}
		$this->_renderer->render($this,$name,$data,$return);
	}
	
	/**
	 * Get Renderer instance
	 * @return object
	 */
	protected function getRenderer()
	{
		$this->_renderer = new Renderer;
		
		if(property_exists(\Lightworx::getApplication(),"errorViewPath"))
		{
			$appViewPath = \Lightworx::getApplication()->errorViewPath;
		}
		
		if(isset($appViewPath) and is_dir($appViewPath))
		{
			$this->_renderer->setViewPath($appViewPath);
		}else{
			$this->_renderer->setViewPath($this->getViewPath());
		}
		
		if(is_object($this->_renderer))
		{
			return $this->_renderer;
		}
		return false;
	}
	
	/**
	*  highlight the code, the code copy from the symfony 2.0
	*/
	public function fileExcerpt($file, $line, $range=3)
    {
        if (is_readable($file)) {
            $code = highlight_file($file, true);
            // remove main code/span tags
            $code = preg_replace('#^<code.*?>\s*<span.*?>(.*)</span>\s*</code>#s', '\\1', $code);
            $content = preg_split('#<br />#', $code);

            $lines = array();
            for ($i = max($line - $range, 1), $max = min($line + $range, count($content)); $i <= $max; $i++) {
                $lines[] = '<li'.($i == $line ? ' class="selected"' : '').'><code>'.self::fixCodeMarkup($content[$i - 1]).'</code></li>';
            }
            return '<ol start="'.max($line - $range, 1).'">'.implode("\n", $lines).'</ol>';
        }
    }

	/**
	*  setting the code markup, the code copy from the symfony 2.0
	*/
    protected static function fixCodeMarkup($line)
    {
        // </span> ending tag from previous line
        $opening = strpos($line, '<span');
        $closing = strpos($line, '</span>');
        if (false !== $closing && (false === $opening || $closing < $opening)) {
            $line = substr_replace($line, '', $closing, 7);
        }

        // missing </span> tag at the end of line
        $opening = strpos($line, '<span');
        $closing = strpos($line, '</span>');
        if (false !== $opening && (false === $closing || $closing > $opening)) {
            $line .= '</span>';
        }

        return $line;
    }
}