<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Storage;

use Lightworx\Foundation\Object;

abstract class Storage extends Object
{
	
	/**
	 * The method getData returned result will be storing in the self::$persistentData,
	 * during the whole application running.
	 * that means you do not need to fetch data from storage in every time.
	 * @var boolean defaults to false
	 */
	public $enablePersistentData = false;

	public $data;
	
	public static $persistentData = array();
	
	public function __construct()
	{
		$this->initialize();
	}
	
	/**
	 * Initialize object
	 */
	public function initialize(){}
	
	/**
	 * Save data to current storage
	 * @return boolean
	 */
	abstract public function save();
	
	abstract public function getData();

	public function beforeSave()
	{
		return true;
	}

	public function afterSave(){}

	public function beforeRead()
	{
		return true;
	}

	public function afterRead(){}
	
	public function setProperties(array $properties)
	{
		foreach($properties as $property=>$value)
		{
			$this->{$property} = $value;
		}
	}
}