<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Foundation;

use Lightworx\Component\Logging\Logger;

class ErrorHandler extends BaseHandler
{
	public $status = 500;
	public $layout = 'Error';
	public $view = 'Errors/Unknow';
	
	public function __construct($level=null,$message='',$file=false,$line=false,array $text=array())
	{
		$this->initialize();
		if($level!==null and (error_reporting() & $level))
		{
			$this->sendHeader();
			$code = $this->fileExcerpt($file,$line);
			
			$error = array('error'=>$level,'message'=>$message,'file'=>$file,'line'=>$line,'code'=>$code,'text'=>$text);

			$log = "[".date("H:i:s d-M-Y",time())."] ".$message." in file:".$file." line:".$line."\n";

			if(isset(\Lightworx::getApplication()->components['Lightworx.Component.Logging.Logger']))
			{
				$this->getComponent("Lightworx.Component.Logging.Logger")->writeLogs($level,$log,Logger::TYPE_ERROR);
			}
			$this->render($this->getView(),$error);
		}
	}

	public function lastError()
	{
		$error = error_get_last();
		if($error!==null)
		{
			$this->sendHeader();
			$error['code'] = $this->fileExcerpt($error['file'],$error['line']);
			$this->render($this->getView(),$error);
		}
	}
}