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

use Lightworx\Foundation\Object;

class Logger extends Object
{
	const TYPE_ERROR = 1;
	const TYPE_EXCEPTION = 2;
	const TYPE_TRACE = 3;
	
	/**
	 * The $stacks is a log container, that invoke logs for other objects.
	 */ 
	static public $stacks = array();

	/**
	 * Set the type for which one type you want to logging, 
	 * you can specifying one or more type in an array.
	 * @var array
	 */
	public $type = array();
	
	/**
	 * Specifying the logging levels
	 * @var array
	 */
	public $levels = array();
	
	/**
	 * Specifying the component stroage name in configuration 
	 * @var string
	 */
	public $storage;
	
	/**
	 * storage instance
	 * @var Storage
	 */
	static private $storageInstance;
	
	/**
	 * The parameters of storage
	 */
	public $storageParams = array();
	
	/**
	 * The logging time format
	 * @var string
	 * @see http://www.php.net/manual/en/function.date.php
	 */
	public $timeFormat = 'M j, Y, H:i:s';
	
	/**
	 * Writing a log to the storage
	 * @param string $log
	 * @return boolean
	 */
	public function writeLogs($level,$log,$type=1)
	{
		if(!in_array($type,$this->type))
		{
			return;
		}
		
		$this->getStorage()->data = $log;
		if((in_array($level,$this->levels) or $level===0) and $this->getStorage()->save())
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Return the specifying the storage instance
	 * @return object
	 */
	public function getStorage()
	{
		if(self::$storageInstance===null)
		{
			self::$storageInstance = \Lightworx::getApplication()->getComponent($this->storage);
			self::$storageInstance->setProperties($this->storageParams);
		}
		return self::$storageInstance;
	}
}