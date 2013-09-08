<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: ExceptionHandler.php 29 2011-10-04 05:22:03Z Stephen.Lee $
 */

namespace Lightworx\Foundation;

use Lightworx\Exception\HttpException;
use Lightworx\Component\Logging\Logger;

class ExceptionHandler extends BaseHandler
{
	public $layout = 'Exception';
	
	protected $exception;
	
	public function __construct(\Exception $exception)
	{
		$this->exception = $exception;
		$this->status = ($exception instanceof HttpException) ?  $exception->getCode() : 500;
		$this->initialize();
		$this->sendHeader();
		
		foreach($exception->getTrace() as $key=>$trace)
		{
			if(!array_key_exists('file',$trace) or !array_key_exists('line',$trace))
			{
				$traces = array();
			}else{
				$traces[$key] = $trace;
				$traces[$key]['code'] = $this->fileExcerpt($trace['file'],$trace['line']);
			}
		}
		$logContents = "[".date('H:i:s d-M-Y(e)')."] ".$exception->getMessage().' file:'.$exception->getFile().' line:'.$exception->getLine()."\n";
		if(isset(\Lightworx::getApplication()->components['Lightworx.Component.Logging.Logger']))
		{
			$this->getComponent("Lightworx.Component.Logging.Logger")->writeLogs(0,$logContents,Logger::TYPE_EXCEPTION);
		}
		$this->render($this->getView(),array('exception'=>$exception,'traces'=>$traces));
	}
	
	public function getView()
	{
		$exception = explode("\\",get_class($this->exception));
		$exceptionType = end($exception);
		$path = $this->getViewPath().$exceptionType."/";
		
		if(!is_dir($path))
		{
			return "Default/Unknow";
		}
		
		$viewFile = $this->status;
		
		if(!is_file($path.$viewFile.".php"))
		{
			$viewFile = "Unknow";
		}
		return $exceptionType."/".$viewFile;
	}
}
