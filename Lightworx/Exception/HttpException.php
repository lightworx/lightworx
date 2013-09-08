<?php

namespace Lightworx\Exception;

class HttpException extends \Exception
{
	public $statusCode;
	static public $headerErrorName = "lightworx-http-error";
	
	public function __construct($status,$message=null,$code=0)
	{
		\Lightworx::getApplication()->eventNotify('exception.httpException',$this);
		\Lightworx::getApplication()->eventNotify('exception.httpException.'.$status,$this);
		$this->statusCode = $status;
		parent::__construct($message,$status);
		header(self::$headerErrorName.":".$message);
	}
}