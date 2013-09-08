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

class CliErrorHandler extends BaseHandler
{	
	public function __construct($level=null,$message='',$file=false,$line=false,array $text=array())
	{
		$this->initialize();
		if($level!==null and (error_reporting() & $level))
		{
			$errorInfo = $message.' Error File: '.$file.' (Line: '.$line.')['.date("H:i:s d-M-Y",time()).']'."\n";
			
			if(isset(\Lightworx::getApplication()->components['Lightworx.Component.Logging.Logger']))
			{
				$this->getComponent("Lightworx.Component.Logging.Logger")->writeLogs($level,$log,Logger::TYPE_ERROR);
			}
			echo $errorInfo;
		}
	}
	
	public function lastError()
	{
		$error = error_get_last();
		if($error!==null)
		{
			echo $error['message'].' Error File: '.$error['file'].' (Line: '.$error['line'].')['.date("H:i:s d-M-Y",time()).']'."\n";
		}
	}
}
