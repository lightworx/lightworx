<?php

namespace Lightworx\Component\Iterator;

class FileFilterIterator extends \RecursiveFilterIterator
{
	public $filters = array();
	
	public $acceptMethod;
	
	public function accept()
	{
		if($this->acceptMethod!==null and is_callable($this->acceptMethod))
		{
			return call_user_func($this->acceptMethod,$this);
		}
		
		if($this->current()->isDir()===true)
		{
			$extension = basename($this->current()->getPathname());
		}

		if($this->current()->isFile()===true)
		{
			$extension = '.'.$this->current()->getExtension();
		}
		
		if(\Lightworx\Helper\ArrayHelper\iin_array($extension,$this->filters))
		{
			return false;
		}
		return true;
	}
}