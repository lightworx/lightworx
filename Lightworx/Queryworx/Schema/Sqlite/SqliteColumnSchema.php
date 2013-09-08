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
use Lightworx\Queryworx\Schema\ColumnSchema;

class SqliteColumnSchema extends ColumnSchema
{
	public $name;
	public $rawName;
	public $limit;
	public $type;
	public $dbType;
	public $allowNull;
	public $isPrimaryKey = false;
	public $isForeignKey = false;
	public $defaultValue;
	
	public function __construct($value,$tableSchema)
	{
		$this->name = $value['name'];
		$this->rawName = $tableSchema->quoteColumnName($value['name']);
		$this->limit = $this->getColumnLimit($value['type']);
		$this->type = $this->getColumnType($value['type']);
		$this->allowNull = strtoupper($value['notnull']) == "1" ? false : true;
		$this->isPrimaryKey = $this->isPrimaryKey($value,$tableSchema);
		$this->defaultValue = $value['dflt_value'];
	}
	
	/**
	 * If a column is a primary key that will be return true, otherwise false.
	 * @param array $column
	 * @param TableSchema $table
	 */
	public function isPrimaryKey(array $column,TableSchema $tableSchema)
	{
		$isPrimaryKey = strtoupper($column['pk']) == "1" ? true : false;
		if($isPrimaryKey)
		{
			$tableSchema->setPrimaryKey($column['name']);
		}
		return $isPrimaryKey;
	}
}