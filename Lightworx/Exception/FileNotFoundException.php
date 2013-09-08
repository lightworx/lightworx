<?php

namespace Lightworx\Exception;

class FileNotFoundException extends \Exception
{
	public function __construct($message)
	{
		\Lightworx::getApplication()->eventNotify('exception.fileNotFoundException',$this);
		$this->message = "File not found:".$message;
	}
}