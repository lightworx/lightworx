<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Upload;

use Lightworx\Foundation\Widget;

class BaseUploader extends Widget
{
	public $config;
	
	public function init()
	{
		if(!isset($this->config['uploadNum']) or !isset($this->config['formName']))
		{
			throw new \RuntimeException("Uploading configure invalid, that must contain the parameter 'uploadNum' and 'formName'");
		}
	}
	
	public function run()
	{
		for($i=0;$i<$this->config['uploadNum'];$i++)
		{
			echo '<input type="file" name="'.$this->config['formName'].'[]" />';
		}
	}
}