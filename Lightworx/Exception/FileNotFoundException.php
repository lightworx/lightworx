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

class FileNotFoundException extends \Exception
{
	public function __construct($message)
	{
		\Lightworx::getApplication()->eventNotify('exception.fileNotFoundException',$this);
		$this->message = "File not found:".$message;
	}
}