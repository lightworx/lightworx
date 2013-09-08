<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Logging;

use Lightworx\Foundation\Event;
use Lightworx\Widgets\Logging\Profiler as WidgetProfiler;

class Profiler extends Logger
{
	public $injectEvent = 'afterController.*';
	public $widgetProfiler;
	
	public function __construct()
	{
		$this->widgetProfiler = new WidgetProfiler;
		$this->widgetProfiler->init();
		Event::attach($this->injectEvent,array($this,'showProfiling'));
	}

	public function showProfiling()
	{
		$this->widgetProfiler->traceContainer = parent::$stacks;
		$this->widgetProfiler->runningTime = $this->getRunningTime();
		$this->widgetProfiler->run();
	}

	public function getRunningTime()
	{
		if(defined('LIGHTWORX_START_TIME')===false)
		{
			throw new \RuntimeException("Undefined the constant LIGHTWORX_START_TIME");
		}
		return microtime(true)-LIGHTWORX_START_TIME.'s';
	}
}