<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Logging;

class Trace extends Logger
{
	public function getTraces()
	{
		$appTrace='';
		$traces=debug_backtrace();
		foreach($traces as $trace)
		{
			if(isset($trace['file'],$trace['line']) and strpos($trace['file'],LIGHTWORX_PATH)!==0)
			{
				$appTrace.="\nTrace file: ".$trace['file'].' (line: '.$trace['line'].')';
			}
		}
		return $appTrace;
	}


	public function trace($type,$message,$others,$time)
	{
		if(!is_array($this->type) or !in_array($type,$this->type))
			return;
		
		$trace['type'] = $type;
		$trace['message'] = $message.$this->getTraces();
		$trace['traceInfo'] = '[Trace type:'.$type.' - time: '.date($this->timeFormat,$time).']';
		$trace['others'] = $others;

		if(is_array($others))
		{
			$trace['others'] = 'Array:'.print_r($others,true);
		}
		
		parent::$stacks[] = $trace;
		$this->getStorage()->data = implode("\n",$trace)."\n\n\n";

		if($this->getStorage()->save())
		{
			return true;
		}
		return false;
	}
}