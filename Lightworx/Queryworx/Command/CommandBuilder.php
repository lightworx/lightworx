<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\Command;

use Lightworx\Queryworx\ORM\ActiveRecord;
use Lightworx\Queryworx\Command\DbCriteria;
use Lightworx\Queryworx\Command\DbCommand;
use Lightworx\Queryworx\Command\DbExpression;
use Lightworx\Queryworx\Connection\DbConnection;

class CommandBuilder
{
	const PARAM_PREFIX = ":p";
	
	public $metadata;
	public $connection;
	
	public $criteria;
	public $command;
	
	public function __construct(ActiveRecord $ar)
	{
		$this->metadata = $ar->getMetaData();
		$this->connection = $ar->getConnection();
		$this->command = $this->createDbCommand();
	}
	
	/**
	 * Creates a DbCriteria object and set the property tableName
	 * @param string $scenario
	 */
	public function createDbCriteria($scenario="find",$createNew=false)
	{
		if($createNew===true or !isset($this->criteria[$scenario]) or (!$this->criteria[$scenario] instanceof DbCriteria))
		{
			$this->criteria[$scenario] = new DbCriteria($this->metadata,$scenario);
			$this->criteria[$scenario]->tableName = $this->metadata->tableName;
		}
		return $this->criteria[$scenario];
	}

	/**
	 * Gets a DbCriteria instance with specified scenario, if it is exists.
	 * @param string $scenario
	 */
	public function getDbCriteria($scenario='find')
	{
		if(isset($this->criteria[$scenario]))
		{
			return $this->criteria[$scenario];
		}
	}
	
	/**
	 * Sets a DbCriteria instance and scenario
	 * @param DbCriteria $criteria
	 * @param string $scenario
	 */
	public function setDbCriteria(DbCriteria $criteria, $scenario)
	{
		$this->criteria[$scenario] = $criteria;
	}
	
	/**
	 * Creates a DbCommand instance
	 * @param DbCriteria $criteria
	 * @return DbCommand
	 */
	public function createDbCommand($criteria="")
	{
		return new DbCommand($this->connection,$criteria);
	}
	
	/**
	 * Create find command by specified condition
	 * @param string $condition
	 * @param array $params
	 */
	public function createFindCommand($condition,array $params=array())
	{
		$criteria = $this->createDbCriteria();
		$criteria->fields = '*';
		$criteria->condition = $condition;
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$params);
		return $this->command;
	}
	
	/**
	 * Create find command by condition and primary key
	 * @param mixed $pk
	 * @param string $condition
	 * @param array $params
	 */
	public function createFindByPkCommand($pk,$condition="",array $params=array())
	{
		$pkCondition = $this->getPrimaryKeyCondition($pk);
		$criteria = $this->createDbCriteria();
		$criteria->condition = $pkCondition.($condition=="" ? "" : " AND ".$condition);
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$params);
		return $this->command;
	}
	
	/**
	 * Creates an SQL find command by specified columns and condition
	 * @param array $columns
	 * @param string $condition
	 * @param array $params
	 */
	public function createFindByColumns(array $columns,$condition="",array $params=array())
	{
		$fields = array();
		$criteria = $this->createDbCriteria();
		foreach($columns as $columnName=>$column)
		{
			if(isset($this->metadata->columns[$column]))
			{
				$fields[] = $criteria->tableAlias.'.'.$this->metadata->columns[$column]->rawName;
			}
		}
		$criteria->fields = implode(", ",$fields);
		$criteria->condition = $condition;
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$params);
		return $this->command;
	}
	
	/**
	 * Creates an SQL command
	 * @param unknown_type $sql
	 * @param array $params
	 */
	public function createSQLCommand($sql,array $params=array())
	{
		$this->command->setSql($sql);
		$this->bindValues($this->command,$params);
		return $this->command;
	}
	
	/**
	 * Creates a count command by specified condition
	 * @param string $condition
	 * @param array $params
	 */
	public function createCountCommand($condition="",array $params=array())
	{
		$criteria = $this->createDbCriteria();
		$criteria->fields = "count(*)";
		$condition!="" ? $criteria->condition = $condition : "";
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$params);
		return $this->command;
	}
	
	/**
	 * Creates a count command by attributes
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 */
	public function createCountByAttributesCommand(array $attributes,$condition="",array $params=array())
	{
		$i=0;
		$values = $placeholders = $fields = array();
		foreach($attributes as $column=>$value)
		{
			if(isset($this->metadata->columns[$column]))
			{
				$fields[] = $this->metadata->columns[$column]->rawName .'='.self::PARAM_PREFIX.$i;
				$values[self::PARAM_PREFIX.$i] = $value;
				$i++; 
			}
		}
		
		foreach($params as $column=>$value)
		{
			$placeholders[] = self::PARAM_PREFIX.$i;
			$values[self::PARAM_PREFIX.$i] = $value;
			$i++;
		}
	
		
		if($this->isQuestionMark($params))
		{
			if(substr_count($condition,"?")!==count($placeholders))
			{
				throw new \RuntimeException("The number of parameter should be equal with the number of the question mark.");
			}
			
			$condition = vsprintf(str_replace("?","%s",$condition),$placeholders);
		}
		
		$condition!="" ? $condition = " AND ".$condition : "";
		$criteria = $this->createDbCriteria();
		$criteria->fields= "count(*)";
		$criteria->condition = implode(" AND ",$fields).$condition;
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$values);
		return $this->command;
	}
	
	/**
	 * Get the record whether is exists or not.
	 * @param string $condition
	 * @param array $params
	 */
	public function createExistsCommand($condition="",array $params=array())
	{
		return $this->createFindCommand($condition,$params);
	}
	
	/**
	 * Create insert command
	 * @param array $data
	 * @return object DbCommand
	 */
	public function createInsertCommand(array $data)
	{
		$criteria = $this->createDbCriteria("insert");
		$i = 0;
		$fields = $values = $placeholders = array();
		foreach($this->metadata->columns as $columnName=>$column)
		{
			if(!isset($data[$columnName]))
			{
				if($column->allowNull or $column->defaultValue!==null or in_array($columnName,$this->metadata->primaryKey))
				{
					continue;
				}else{
					throw new \RuntimeException("The column ".$columnName." cannot be empty.");
				}
			}
			
			$fields[] =  $column->rawName;
			$placeholders[]=self::PARAM_PREFIX.$i;
			$values[self::PARAM_PREFIX.$i] = $column->typecase($column,$data[$columnName]);
			$i++;
		}
		$criteria->fields = implode(", ",$fields);
		$criteria->values = implode(", ",$placeholders);
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$values);
		return $this->command;
	}
	
	/**
	 * Creates a update command
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 */
	public function createUpdateCommand(array $attributes,$condition="",array $params=array())
	{
		$i=0;
		$fields = $placeholders = $values = array();
		
		foreach($attributes as $column=>$value)
		{
			if(isset($this->metadata->columns[$column]))
			{
				$dbColumn = $this->metadata->columns[$column];
				$fields[] = $dbColumn->rawName .'='.self::PARAM_PREFIX.$i;
				$values[self::PARAM_PREFIX.$i] = $dbColumn->typecase($dbColumn,$value);
				$i++;
			}
		}
		
		foreach($params as $key=>$value)
		{
			$placeholders[] = self::PARAM_PREFIX.$i;
			$values[self::PARAM_PREFIX.$i] = $value;
			$i++;
		}
		
		if($this->isQuestionMark($params))
		{
			if(substr_count($condition,"?")!==count($placeholders))
			{
				throw new \RuntimeException("The number of parameter should be equal with the number of question mark.");
			}
			
			$condition = vsprintf(str_replace("?","%s",$condition),$placeholders);
		}
		
		$criteria = $this->createDbCriteria("update");
		$criteria->keyValuePair = implode(",",$fields);
		$criteria->condition = $condition;
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$values);
		return $this->command;
	}
	
	/**
	 * Creates an update SQL command
	 * @param mixed $pk
	 * @param array $attributes
	 */
	public function createUpdateByPkCommand($pk, array $attributes=array(), $condition="", array $params=array())
	{
		$i = 0;
		$values = $placeholders = $keyValuePair = array();
		$pkCondition = $this->getPrimaryKeyCondition($pk);
		
		foreach($attributes as $attribute=>$value)
		{
			if(isset($this->metadata->columns[$attribute]))
			{
				$keyValuePair[] = $this->metadata->columns[$attribute]->rawName." = ".self::PARAM_PREFIX.$i;
				$values[self::PARAM_PREFIX.$i] = $value;
				$i++;
			}
		}
		
		foreach($params as $key=>$value)
		{
			$placeholders[] = self::PARAM_PREFIX.$i;
			$values[self::PARAM_PREFIX.$i] = $value;
			$i++;
		}
		
		if($this->isQuestionMark($params))
		{
			if(substr_count($condition,"?")!==count($placeholders))
			{
				throw new \RuntimeException("The number of parameter should be equal with the number of the question mark.");
			}
			
			$condition = vsprintf(str_replace("?","%s",$condition),$placeholders);
		}
		
		$criteria = $this->createDbCriteria("update");
		$criteria->keyValuePair = implode(", ",$keyValuePair);
		$criteria->condition = $pkCondition.($condition!="" ? " AND ".$condition : $condition);
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$values);
		return $this->command;
	}

	public function createUpdateCounterCommand(array $counters,$condition='',array $params=array())
	{
		$i = 0;
		$values = $placeholders = $keyValuePair = array();
		
		foreach($counters as $attribute=>$value)
		{
			if(!isset($this->metadata->columns[$attribute]))
				continue;
			
			$value = (int)$value;
			$rawName = $this->metadata->columns[$attribute]->rawName;
			if($value>0)
			{
				$keyValuePair[] = "{$rawName} = {$rawName}+".($value);
			}else{
				$keyValuePair[] = "{$rawName} = {$rawName}-".(-$value);
			}
		}
		
		foreach($params as $key=>$value)
		{
			$placeholders[] = self::PARAM_PREFIX.$i;
			$values[self::PARAM_PREFIX.$i] = $value;
			$i++;
		}
		
		if($this->isQuestionMark($params))
		{
			if(substr_count($condition,"?")!==count($placeholders))
			{
				throw new \RuntimeException("The number of parameter should be equal with the number of the question mark.");
			}
			
			$condition = vsprintf(str_replace("?","%s",$condition),$placeholders);
		}
		
		$pk = $this->metadata->primaryKey;
		$pkCondition = (count($pk) > 1) ? (implode(" IS NULL AND ",$pk)." IS NULL") : ($pk[0]." IS NULL");

		$criteria = $this->createDbCriteria("update");
		$criteria->keyValuePair = implode(", ",$keyValuePair);
		$criteria->condition = ($condition!="" ? $condition : $pkCondition);
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$values);
		return $this->command;
	}
	
	/**
	 * Creates a delete SQL command by primary key and condition.
	 * @param array $pk
	 * @param string $condition
	 * @param array $params
	 */
	public function createDeleteByPkCommand($pk,$condition="",array $params=array())
	{
		$criteria = $this->createDbCriteria("delete");
		$pkCondition = $this->getPrimaryKeyCondition($pk);
		$condition = ($condition!="") ? " AND ".$condition : $condition;
		$criteria->condition = $pkCondition.$condition;
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$params);
		return $this->command;
	}
	
	/**
	 * Creates a delete SQL command by specified condition.
	 * @param string $condition
	 * @param array $params
	 */
	public function createDeleteCommand($condition="",array $params=array())
	{
		$criteria = $this->createDbCriteria("delete");
		$criteria->condition = $condition;
		$this->command->setSql($criteria);
		$this->bindValues($this->command,$params);
		return $this->command;
	}
	
	/**
	 * Returns the primary key condition by the $pk
	 * @param mixed $pk
	 * @return string
	 */
	public function getPrimaryKeyCondition($pk)
	{
		if($this->metadata->isCompositePrimaryKey() and !is_array($pk))
		{
			throw new \RuntimeException("The parameter pk should be an array.");
		}
		
		$pkCondition = $singlePk = array();
		
		if(!is_array($pk) and $this->metadata->isCompositePrimaryKey()===false)
		{
			$singlePk[current($this->metadata->primaryKey)] = $pk;
			$pk = $singlePk;
		}
		
		foreach($pk as $columnName=>$value)
		{
			if(in_array($columnName,$this->metadata->primaryKey) and isset($this->metadata->columns[$columnName]))
			{
				$column = $this->metadata->columns[$columnName];
				$value = $column->typecase($column,$value);
				$pkCondition[] = $this->metadata->columns[$columnName]->rawName." = ".$this->connection->quoteValue($value);
			}else{
				throw new \RuntimeException("The primary key ".$columnName." does not exists.");
			}
		}
		return implode(" AND ",$pkCondition);
	}
	
	/**
	 * Binding values to object DbCommand
	 * @param object $command
	 * @param array $values
	 */
	public function bindValues($command, $values)
	{
		if(!is_array($values) or ($n=count($values))===0)
		{
			return;
		}
		
		if(isset($values[0]))
		{
			for($i=0;$i<$n;++$i)
			{
				$command->bindValue($i+1,$values[$i]);
			}
		}else{
			foreach($values as $name=>$value)
			{
				$command->bindValue($name[0]!==':' ? ':'.$name : $name,$value);
			}
		}
	}
	
	/**
	 * Get the placeholders whether is question mark or not.
	 * @param array $values
	 * @return boolean
	 */
	public function isQuestionMark(array $values)
	{
		if(isset($values[0]))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Get the placeholders whether is string mark or not.
	 * @param array $values
	 * @return boolean
	 */
	public function isStringMark(array $values)
	{
		if(isset($values[0]))
		{
			return false;
		}
		return true;
	}
}