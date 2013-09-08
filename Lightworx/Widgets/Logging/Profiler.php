<?php

namespace Lightworx\Widgets\Logging;

use Lightworx\Foundation\Widget;

class Profiler extends Widget
{
	public $traceContainer=array();
	public $profilerClassStyle = 'lightworx-widgets-logging-profiler';
	public $runningTime;

	public function init()
	{
		// $this->addCssCode('.'.$this->profilerClassStyle.'{
		// 	display:none;
		// }');
	}

	public function run()
	{
		if(strtolower(RUNNING_MODE)!='production')
		{
			$items = '';
			foreach($this->traceContainer as $item)
			{
				$items .= '<tr>
					<td>'.$item['type'].'</td>
					<td>'.$item['traceInfo'].'</td>
					<td>'.$item['message'].'</td>
					<td>'.$item['others'].'</td>
				</tr>';
			}
			$template = $this->getProfilerTemplate();
			echo str_replace('{items}',$items,$template);
		}
	}

	public function getProfilerTemplate()
	{
		return '<table border="1" class="'.$this->profilerClassStyle.'">
			<thead>
				<tr>
					<th colspan="4">App Profiling, Total running time:'.$this->getRunningTime().'</th>
				</tr>
				<tr>
					<th>type</th>
					<th>time</th>
					<th>message</th>
					<th>others</th>
				</tr>
			</thead>
			<tbody>
			{items}
			</tbody>
		</table>';
	}

	public function getRunningTime()
	{
		return $this->runningTime;
	}
}