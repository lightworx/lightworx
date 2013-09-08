<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Schema\Mysql;

use Lightworx\Queryworx\Schema\ColumnSchema;
use Lightworx\Queryworx\Schema\TableSchema;

class MysqlColumnSchema extends ColumnSchema
{
	public $name;
	public $rawName;
	public $limit;
	public $type;
	public $dbType;
	public $allowNull;
	public $key;
	public $isPrimaryKey = false;
	public $isForeignKey = false;
	public $defaultValue;
	public $extra;
	
	public function __construct($value,$tableSchema)
	{
		$this->rawMetadata = $value;
		$this->name = $value['Field'];
		$this->rawName = $tableSchema->quoteColumnName($value['Field']);
		$this->limit = $this->getColumnLimit($value['Type']);
		$this->type = $this->getColumnType($value['Type']);
		$this->allowNull = strtoupper($value['Null']) == "NO" ? false : true;
		$this->key = $value['Key'];
		$this->isPrimaryKey = $this->isPrimaryKey($value,$tableSchema);
		$this->defaultValue = $value['Default'];
		$this->extra = $this->getExtra();
	}
	
	/**
	 * If a column is a primary key that will be return true, otherwise false.
	 * @param array $column
	 * @param TableSchema $table
	 */
	public function isPrimaryKey(array $column,TableSchema $tableSchema)
	{
		$isPrimaryKey = strtoupper($column['Key']) == "PRI" ? true : false;
		if($isPrimaryKey)
		{
			$tableSchema->setPrimaryKey($column['Field']);
		}
		return $isPrimaryKey;
	}

	public function getColumnType($type)
	{
		$columnType = parent::getColumnType($type);
		if($this->dbType=='enum' and isset($this->rawMetadata['Type']))
		{
			$placeholders = array("'"=>'','"'=>'','('=>'',')'=>'','enum'=>'','ENUM'=>'');
			$enums = str_replace(array_keys($placeholders),array_values($placeholders),$this->rawMetadata['Type']);
			$this->extra = array_map('trim',explode(',',$enums));
		}
		return $columnType;
	}

	public function getExtra()
	{
		if($this->extra!==null)
		{
			return $this->extra;
		}
		return $this->rawMetadata['Extra'];
	}
}