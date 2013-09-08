<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Schema;

use Lightworx\Queryworx\Command\DbCommand;
use Lightworx\Queryworx\Connection\DbConnection;

abstract class TableSchema
{
	public $tableName;
	public $databaseName;
	public $primaryKey = array();
	
	public $metadata = array();
	public $connection;
	public $columns;
	
	static public $storage = 'Lightworx.Component.Storage.FileStorage';
	
	static private $metadataStorageInstance = null;
	
	abstract public function getTableMetadata($tableName);
	
	abstract public function getColumnMetadata($tableName);
	
	public function __construct(Dbconnection $conn,$databaseName,$tableName)
	{
		$this->connection = $conn;
		$this->databaseName = $databaseName;
		$this->tableName = $conn->tablePrefix.$tableName;
	}
	
	/**
	 * Get the DbCommand and executing an SQL
	 * @param string $sql
	 */
	public function getCommand($sql)
	{
		return new DbCommand($this->connection,$sql);
	}
	
	/**
	 * Sets a primary key
	 * @param string $pk
	 */
	public function setPrimaryKey($pk)
	{
		$this->primaryKey[] = $pk;
	}
	
	/**
	 * Return the primary key of the database table
	 * @return mixed
	 */
	public function getPrimaryKey()
	{
		if(count($this->primaryKey)===1)
		{
			return $this->primaryKey[0];
		}
		return $this->primaryKey;
	}
	
	/**
	 * Get metadata and setting object schema.
	 */
	public function getSchema()
	{
		$id = $this->databaseName.'.'.$this->tableName;
		if($this->getMetadata($this->tableName)===false or !isset($this->metadata))
		{
			$metadata[$this->tableName]['columns'] = $this->getColumnMetadata($this->tableName);
			$this->setMetadata($metadata);
		}
		$this->getColumnSchema($this->metadata[$this->tableName]['columns']);
		return $this;
	}
	
	/**
	 * Get metadata cache when running on model production
	 * @return boolean
	 */
	public function getMetadata($tableName)
	{
		if(strtolower(RUNNING_MODE)=="production" and ($contents=$this->getMetadataStorage()->getData())!="")
		{
			$this->metadata = unserialize($contents);
			if(isset($this->metadata[$tableName]))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Set metadata cache to storage, if the $id is exist, 
	 * that will be updated in $id, if not exist then insert a new $id
	 * @param string $id
	 */
	public function setMetadata(array $metadata)
	{
		$this->metadata = array_merge($this->metadata,$metadata);
		
		if(strtolower(RUNNING_MODE)=="production")
		{
			$this->getMetadataStorage()->data = serialize($this->metadata);
			$this->getMetadataStorage()->save();
		}
	}
	
	/**
	 * Get a file storage instance for storing matedata
	 */
	public function getMetadataStorage()
	{
		if(self::$metadataStorageInstance===null)
		{
			$config =array(
				"storagePath"=>\Lightworx::getApplication()->getRuntimePath().'database/'.$this->connection->getDriverName().'/',
				"storageFileName"=>$this->databaseName.".metadata",
				"fileFlag"=>FILE_USE_INCLUDE_PATH,
				'mode'=>'w+b'
			);
		
			$storage = \Lightworx::getApplication()->getComponent(self::$storage);
			$storage->setProperties($config);
			self::$metadataStorageInstance = $storage;
		}
		
		return self::$metadataStorageInstance;
	}
	
	/**
	 * Return the current table is composite primary key
	 * @return boolean
	 */
	public function isCompositePrimaryKey()
	{
		return count($this->primaryKey)>1 ? true : false;
	}
}