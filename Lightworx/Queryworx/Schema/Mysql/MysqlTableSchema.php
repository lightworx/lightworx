<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Schema\Mysql;

use Lightworx\Queryworx\Schema\TableSchema;
use Lightworx\Queryworx\Schema\Mysql\MysqlColumnSchema;

class MysqlTableSchema extends TableSchema
{
	/**
	 * Return the spcifying database table struct
	 * @param array $metadata
	 */
	public function getTableSchema($metadata)
	{
		$this->getForeignKey($metadata['Create Table']);
		$this->getTableInformation($metadata['Create Table']);
	}
	
	/**
	 * Get data table information
	 * @param string $createTable
	 */
	public function getTableInformation($createTable)
	{
		$tableInfo = array();
		preg_match('/ENGINE=(\w+)\sAUTO_INCREMENT=(\d+)\sDEFAULT CHARSET=(\w+)\sCOLLATE=(\w+)/i',$createTable,$tableInfo);
		if(count($tableInfo)>=5)
		{
			$this->engine = $tableInfo[1];
			$this->autoIncrement = $tableInfo[2];
			$this->charset = $tableInfo[3];
			$this->collate = $tableInfo[4];
		}
	}
	
	/**
	 * Get data table foreign keys
	 * @param string $createTable
	 */
	public function getForeignKey($createTable)
	{
		$matches=array();
		$regexp = '/FOREIGN KEY \(`(.+?)`\) REFERENCES `(.+?)` \(`(.+?)`\) /mi';
		preg_match_all($regexp,$createTable,$matches,PREG_SET_ORDER);
	
		foreach($matches as $key=>$value)
		{
			$this->columns[$value[1]]->isForeignKey = true;
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
	{
		return $this->getCommand("SHOW CREATE TABLE ".$this->quoteTableName($tableName))->queryRow();
	}
	
	/**
	 * Get columns metadata for specifying database table
	 * @param string $tableName
	 * @return array
	 */
	public function getColumnMetadata($tableName)
	{
		return $this->getCommand("SHOW COLUMNS FROM ".$this->quoteColumnName($tableName))->queryAll();
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
			$this->columns[$value['Field']] = new MysqlColumnSchema($value,$this);
		}
	}
}