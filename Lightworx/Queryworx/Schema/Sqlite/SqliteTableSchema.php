<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Schema\Sqlite;

use Lightworx\Queryworx\Schema\TableSchema;
use Lightworx\Queryworx\Schema\Sqlite\SqliteColumnSchema;

class SqliteTableSchema extends TableSchema
{
	/**
	 * Return the spcifying database table struct
	 * @param array $metadata
	 */
	public function getTableSchema()
	{
		$this->getForeignKey();
		$this->getTableInformation();
	}
	
	/**
	 * Get data table information
	 * @param string $createTable
	 */
	public function getTableInformation()
	{
	}
	
	/**
	 * Get data table foreign keys
	 * @param string $createTable
	 */
	public function getForeignKey()
	{
		$rows = $this->getCommand("PRAGMA foreign_key_list(".$this->quoteColumnName($this->tableName).")")->queryAll();
		foreach($rows as $key=>$value)
		{
			$this->columns[$value['from']]->isForeignKey = true;
		}
	}
	
	/**
	 * Get the meta data of database table
	 * @param array column metadata
	 * @return object
	 */
	public function getColumnSchema($metadata)
	{
		$this->getColumnObjects($metadata);
		return $this;
	}
	
	/**
	 * Returns an the Created data table SQL command
	 * @param string $tableName
	 * @return string
	 */
	public function getTableMetadata($tableName)
	{}
	
	/**
	 * Get columns metadata for specifying database table
	 * @param string $tableName
	 * @return array
	 */
	public function getColumnMetadata($tableName)
	{
		return $this->getCommand("PRAGMA table_info(".$this->quoteTableName($tableName).")")->queryAll();
	}
	
	/**
	 * Quotes a string
	 * @param string
	 * @return string
	 */
	public function quoteTableName($string)
	{
		return "`".$string."`";
	}
	
	/**
	 * Quotes a string
	 * @param string
	 * @return string
	 */
	public function quoteColumnName($string)
	{
		return "`".$string."`";
	}
	
	/**
	 * Get columns
	 * @param array $columns
	 * @param string $tableName
	 */
	public function getColumnObjects(array $columns)
	{
		foreach($columns as $column=>$value)
		{
			$this->columns[$value['name']] = new SqliteColumnSchema($value,$this);
		}
	}
}