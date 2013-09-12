<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

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