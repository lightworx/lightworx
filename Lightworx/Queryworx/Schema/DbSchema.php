<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Schema;

use Lightworx\Queryworx\Connection\PDOConnection;

abstract class DbSchema
{
	/**
	 * PDO Connection instance
	 * @var PDOConnection object
	 */
	protected $connection;
	
	/**
	 * Cache the schema data in this property
	 * @var array
	 */
	public $schema;
	
	/**
	 * database name
	 */
	public $databaseName;
	
	abstract public function getDatabaseName();
	
	/**
	 * When instance that class, must to provides a PDOConnection object.
	 * @param PDOConnection $conn
	 */
	public function __construct(PDOConnection $conn)
	{
		$this->connection = $conn;
		$this->init();
	}
	
	/**
	 * Initialize schema class
	 */
	public function init(){}
	
	/**
	 * returns a PDO object
	 * @return PDO object
	 */
	protected function getPDOInstance()
	{
		return $this->connection->getPDOInstance();
	}
	
	/**
	 * Quotes a string for use a query
	 * @param string
	 * @return string
	 * @see http://www.php.net/manual/en/pdo.quote.php
	 */
	public function quote($string)
	{
		return $this->getPDOInstance()->quote($string);
	}
	
	/**
	 * Get table schema and column schema
	 * @param string $tableName
	 */
	public function getSchema($tableName)
	{
		$id = $this->getDatabaseName().'.'.$tableName;
		
		if(!isset($this->schema[$id]))
		{
			$driverName = ucfirst(strtolower($this->connection->getDriverName()));

			$tableSchema = "Lightworx\\Queryworx\\Schema\\".$driverName."\\".$driverName."TableSchema";
		
			$table = new $tableSchema($this->connection,$this->getDatabaseName(),$tableName);
			
			$this->schema[$id] = $table->getSchema();
		}
		return $this->schema[$id];
	}
	
	/**
	 * This method using for get the schema via specifying driver name.
	 * @param string $driverName
	 * @return string schema class name
	 */
	public static function getDbSchemaClassName($driverName)
	{
		return "Lightworx\\Queryworx\\Schema\\".$driverName."\\".$driverName."Schema";
	}
}