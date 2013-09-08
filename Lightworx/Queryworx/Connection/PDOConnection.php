<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Connection;


class PDOConnection extends DbConnection
{
	/**
	 * The dsn means Data source name, specifying the database connection parameters.
	 * @var string
	 */
	public $dsn;
	
	/**
	 * The user name used to connect to the database.
	 * @var string
	 */
	public $username;
	
	/**
	 * The password used to connect to the database.
	 * @var string
	 */
	public $password;
	
	/**
	 * Specific connection option of the driver
	 * @var array
	 */
	public $options = array();
	
	/**
	 * The Connection is active
	 * @var boolean
	 */
	public $active = false;
	
	/**
	 * Statement of PDO
	 * @var object PDOStatement
	 */
	private $PDO=null;
	
	/**
	 * The data table prefix
	 * @var string
	 */
	public $tablePrefix;
	
	/**
	 * Checking the PDO extension is exist or not.
	 * @throws \RuntimeException
	 */
	public function __construct()
	{
		if(!class_exists('PDO'))
		{
			throw new \RuntimeException("The PDO extension does not exist.");
		}
	}

	/**
	 * Get available drivers of PDO
	 * @return array
	 */
	public function getAvailableDrivers()
	{
		return \PDO::getAvailableDrivers();
	}
	
	/**
	 * Returns a PDO object
	 * @return object
	 */
	public function getPDOInstance()
	{
		if(!$this->PDO instanceof \PDO)
		{
			$this->createConnection();
		}
		return $this->PDO;
	}
	
	/**
	 * Return the PDO driver name
	 * @return string
	 */
	public function getDriverName()
	{
		$driverName = current(explode(':',$this->dsn));
		
		if($driverName!="")
		{
			return ucfirst($driverName);
		}
		
		if($this->active===false)
		{
			$this->createConnection();
		}
		return $this->getPDOInstance()->getAttribute(\PDO::ATTR_DRIVER_NAME);
	}
	
	/**
	 * Creates a PDO connection
	 * @return PDO Statement
	 * @throws PDOException
	 */
	public function createConnection()
	{
		try
		{
			$this->PDO = new \PDO($this->dsn,$this->username,$this->password,$this->options);
			$this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->active = true;
		}catch(\PDOException $e){
			throw new \PDOException($e->getMessage());
		}
		return $this->PDO;
	}
	
	/**
	 * Closes a PDO connection
	 */
	public function closeConnection()
	{
		$this->active = false;
		$this->PDO = null;
	}
	
	/**
	 * Return the PDO data type
	 * @param string $type
	 */
	public function getPdoType($type)
	{
		static $map=array
		(
			'boolean'=>\PDO::PARAM_BOOL,
			'integer'=>\PDO::PARAM_INT,
			'string'=>\PDO::PARAM_STR,
			'NULL'=>\PDO::PARAM_NULL,
		);
		return isset($map[$type]) ? $map[$type] : \PDO::PARAM_STR;
	}
	
	/**
	 * Returns the ID of the last inserted row or sequence value,
	 * from the PDO instance.
	 * @return string
	 */
	public function getLastInsertId($sequenceName='')
	{
		return $this->PDO->lastInsertId($sequenceName);
	}
	
	/**
	 * Quote a string in the PDO inside method quote,
	 * copy from Yii
	 * @param mixed $str
	 */
	public function quoteValue($str)
	{
		if(is_int($str) || is_float($str))
		{
			return $str;
		}
		
		if($this->PDO!==null and ($value=$this->PDO->quote($str))!==false)
		{
			return $value;
		}else{
			return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
		}
	}
}