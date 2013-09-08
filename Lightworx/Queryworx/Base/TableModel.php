<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Base;

use Lightworx\Queryworx\Connection\DbConnection;
use Lightworx\Queryworx\Connection\PDOConnection;
use Lightworx\Queryworx\Schema\DbSchema;

class TableModel extends Model
{
	/**
	 * Sets a sql command.
	 * @var string
	 */
	protected $sql;
	
	/**
	 * Database connection instance
	 * @var object
	 */
	private $_connection;
	
	/**
	 * Columns object
	 */
	protected $_columns;
	
	/**
	 * The data operation scenario.
	 */
	private $_scenario;
	
	protected $connectionParams;
	
	/**
	 * The method responsible for for creating a database connection
	 */
	public function initializeConnection()
	{
		if($this->connectionParams===null and property_exists(\Lightworx::getApplication(),"data"))
		{
			$this->connectionParams = \Lightworx::getApplication()->data;
		}
		
		$connection = $this->getConnector();
		
		foreach($this->connectionParams as $property=>$value)
		{
			$connection->{$property} = $value;
		}
		
		$this->setConnection($connection);
	}
	
	/**
	 * Returns a DbConnection instance
	 * @return object
	 */
	public function getConnection()
	{
		if($this->_connection===null)
		{
			$this->initializeConnection();
		}
		return $this->_connection;
	}
	
	/**
	 * Sets a connectio to property _connection
	 * @param DbConnection $connection
	 */
	public function setConnection(DbConnection $connection)
	{
		$this->_connection = $connection;
	}
	
	/**
	 * Instances a connector, and same time that will unset
	 * the connector of property connectionParams
	 * @return DbConnector return the connector instance
	 * @throws RuntimeException when cannot found the connector, 
	 *                                               that will be throw an exception.
	 */
	public function getConnector()
	{
		if(isset($this->connectionParams['connector']))
		{
			$connector = "\\Lightworx\\Queryworx\\Connection\\".$this->connectionParams['connector'];
			unset($this->connectionParams['connector']);
			return new $connector;
		}
		throw new \RuntimeException("The connector have no setting");
	}
	
	/**
	 * Get current model scenario
	 */
	public function getScenario()
	{
		return $this->_scenario;
	}
	
	/**
	 * Set current model scenario
	 */
	public function setScenario($scenario)
	{
		$this->_scenario = $scenario;
	}
	
	/**
	 * Return database driver name
	 * @return string
	 */
	public function getDriverName()
	{
		return $this->getConnection()->getDriverName();
	}
	
	/**
	 * Returns a database schema instance.
	 * @return DbSchema
	 */
	public function getSchemaInstance()
	{
		$schemaName = DbSchema::getDbSchemaClassName($this->getDriverName());
		return new $schemaName($this->getConnection());
	}
	
	/**
	 * Return current connection database name
	 * @return string
	 */
	public function getDatabaseName()
	{
		return $this->getMetaData()->getDatabaseName();
	}
}