<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\ORM;

use Lightworx\Queryworx\Base\TableModel;
use Lightworx\Queryworx\Schema\DbSchema;
use Lightworx\Queryworx\Command\DbCriteria;
use Lightworx\Queryworx\Command\CommandBuilder;
use Lightworx\Queryworx\ORM\Relations\BelongsToRelation;

/**
 * This class implementation the pattern ActiveRecord
 * @since version 0.1
 * @package Lightworx.Queryworx.ORM
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @version $Id$
 */
class ActiveRecord extends TableModel
{
	const BELONGS_ARRAY = '\Lightworx\Queryworx\ORM\Relations\BelongsArray';
	const BELONGS_TO = '\Lightworx\Queryworx\ORM\Relations\BelongsToRelation';
	const HAS_ONE = '\Lightworx\Queryworx\ORM\Relations\HasOneRelation';
	const HAS_MANY = '\Lightworx\Queryworx\ORM\Relations\HasManyRelation';
	const MANY_MANY = '\Lightworx\Queryworx\ORM\Relations\ManyManyRelation';
	const STAT = '\Lightworx\Queryworx\ORM\Relations\StatRelation';
	
	/**
	 * The attribute of active record
	 * @var array
	 */
	protected $_attributes = array();
	
	/**
	 * Whether is a new record or not.
	 * @var boolean defaults to false
	 */
	private $_isNewRecord = false;
	
	/**
	 * The schema object of the current model
	 * @var array
	 */
	private $_schema = array();
	
	/**
	 * Set specified scopes
	 */
	private $_scopes = array('defaultScope');
	
	/**
	 * Command builder instance
	 */
	private $_commandBuilder;
	
	/**
	 * Store the model object
	 * @var array
	 */
	private static $_models;
	
	/**
	 * The database table name
	 * @var string
	 */
	protected $_tableName;
	
	/**
	 * Sets the data table apply snapshots
	 * @var array
	 */
	private $_snapshot;
	
	/**
	 * Store the model relation object.
	 * @var array
	 */
	private $_relationModels = array();

	/**
	 * The csrf token name
	 * @var string
	 */ 
	public $_tokenName = '__lightworx_token';
	public $_stateName = 'record.hash.token';
	
	/**
	 * Set the scenario for current model.
	 * @param string $scenario
	 */
	public function __construct($scenario="insert")
	{
		if($scenario===null){return;}
		$this->setScenario($scenario);
		$this->setIsNewRecord(true);
		
		$this->init();
	}
	
	/**
	 * Initialize something what you want to do, 
	 * you can override this method in your class.
	 */
	protected function init(){}
	
	public function __isset($name){}
	public function __unset($name){}
	
	public function createRelationModel(array $relation)
	{
		$class = array_shift($relation);
		$model = new $class($this,$relation[0],$relation[1],array_slice($relation,1));
		return $model->instantiate();
	}
	
	/**
	 * Gets a attribute if it is exists.
	 * @param string $name
	 */
	public function __get($name)
	{
		if(isset($this->_attributes[$name]))
		{
			return $this->_attributes[$name];
		}
		
		$relations = $this->relations();
		if(isset($relations[$name]))
		{
			if(!isset($this->_relationModels[$name]))
			{
				$this->_relationModels[$name] = $this->createRelationModel($relations[$name]);
			}
			return $this->_relationModels[$name];
		}
		
		if(isset($this->getMetaData()->columns[$name]))
		{
			return null;
		}
		return parent::__get($name);
	}
	
	/**
	 * PHP magic method, sets a property for current model.
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name,$value)
	{
		if($this->setAttribute($name,$value)===false)
		{
			if(isset($this->getMetadata()->relations[$name]))
			{
				$this->_related[$name]=$value;
			}
			parent::__set($name,$value);
		}
		return parent::__set($name,$value);
	}
	
	/**
	 * This method is responsible a query scope
	 * @param string $method
	 * @param mixed $value
	 */
	public function __call($method,$value)
	{
		$scopes = $this->scopes();
		
		if(isset($scopes[$method]))
		{
			$this->_scopes[] = $method;
			return $this;
		}
		
		return parent::__call($method,$value);
	}

	/**
	 * Creates an ActiveRecord object
	 * @param string $className
	 * @return ActiveRecord
	 */
	public static function model($className=__CLASS__)
	{
		if(isset(self::$_models[$className]))
		{
			return self::$_models[$className];
		}else{
			return self::$_models[$className]=new $className(null);
		}
	}
	
	/**
	 * The ORM relationship, that method defined the objects ActiveRecord relations.
	 * You may need to override this method, That should contain the model relations.
	 * @return array
	 */
	public function relations()
	{
		return array();
	}
	
	/**
	 * Return the find scope
	 * @return array
	 */
	public function scopes()
	{
		return array();
	}
	
	/**
	 * Return default scope
	 * @return array
	 */
	public function defaultScope()
	{
		return array();
	}
	
	/**
	 * Return the data process rules, you may need to override this method.
	 * @return array
	 */
	public function rules()
	{
		return array();
	}
	
	/**
	 * Get the table meta data of the current object.
	 * @param string $tableName
	 */
	public function getMetaData($tableName="")
	{
		if($tableName == "")
		{
			$tableName = $this->getTableName();
		}
		return $this->getSchema($tableName);
	}
	
	/**
	 * Get database schema
	 * @return object current schema
	 */
	public function getSchema($tableName)
	{
		if(!isset($this->_schema[$tableName]))
		{
			$this->_schema[$tableName] = $this->getSchemaInstance()->getSchema($tableName);
		}
		return isset($this->_schema[$tableName]) ? $this->_schema[$tableName] : null;
	}
	
	/**
	 * Get current record whether is a new or not.
	 * @return boolean
	 */
	public function getIsNewRecord()
	{
		return $this->_isNewRecord;
	}
	
	/**
	 * Set current record whether is a new or not.
	 * @param boolean $isNewRecord
	 */
	public function setIsNewRecord($isNewRecord)
	{
		$this->_isNewRecord = $isNewRecord;
	}
	
	/**
	 * Returns the list of all attributes name of the model.
	 * This would return all column names of the table associated with this AR class.
	 * @return array list of attribute names.
	 */
	public function attributeLabels()
	{
		return array_keys($this->getMetaData()->columns);
	}
	
	/**
	 * Get database table name, you should be to set a value to the property _tableName in subclass in manually.
	 * @return string the database table name
	 */
	public function getTableName()
	{
		if($this->_tableName===null)
		{
			$this->_tableName = get_class($this);
		}
		return $this->_tableName;
	}
	
	/**
	 * Return the primary key name of the current table.
	 * @return mixed
	 */
	public function getPrimaryKeyName($returnArray=false)
	{
		$primaryKey = $this->getMetaData()->primaryKey;
		if($returnArray)
		{
			return $primaryKey;
		}
		return count($primaryKey)===1 ? current($primaryKey) : $primaryKey;
	}
	
	/**
	 * Gets the value of the primary key
	 * @param boolean $returnArray
	 * @return mixed
	 */
	public function getPrimaryKey($returnArray=false)
	{
		$values = array();
		$primaryKey = $this->getMetaData()->primaryKey;
		
		foreach($primaryKey as $key=>$value)
		{
			$values[$value] = $this->{$value};
		}
		
		if($returnArray)
		{
			return $values;
		}
		return count($values)===1 ? current($values) : $values;
	}
	
	/**
	 * Sets a value for primary key
	 * @param mixed $value
	 */
	public function setPrimaryKey($value)
	{
		$primaryKey = $this->getMetaData()->primaryKey;
		
		if(count($primaryKey)===1)
		{
			$pk = current($primaryKey);
			$this->$pk = $value;
		}else{
			foreach($primaryKey as $pkName)
			{
				$this->{$pkName} = $value[$pkName];
			}
		}
	}
	
	/**
	 * Get a command builder object
	 * @return object
	 */	
	public function getCommandBuilder()
	{
		if($this->_commandBuilder===null)
		{
			$this->_commandBuilder = new CommandBuilder($this);
		}
		return $this->_commandBuilder;
	}
	
	/**
	 * Creates a criteria object
	 */
	public function createCriteria(array $params=array(),$scenario='find')
	{
		$criteria = $this->getCommandBuilder()->createDbCriteria($scenario);
		foreach($params as $property=>$value)
		{
			if(property_exists($criteria,$property))
			{
				$criteria->$property = $value;
			}
		}
		return $criteria;
	}
	
	/**
	 * Get the DbCriteria instance
	 * @param string $scenario
	 */
	public function getCriteria($scenario='find')
	{
		return $this->getCommandBuilder()->createDbCriteria($scenario);
	}
	
	/**
	 * This method is invoked before an AR finder executes a find call.
	 */
	protected function beforeFind()
	{
		$this->eventNotify('model.'.$this->getModelBaseName().'.beforeFind');
		return true;
	}
	
	/**
	 * This method is invoked after each record is instantiated by a find method.
	 */
	protected function afterFind()
	{
		$this->eventNotify('model.'.$this->getModelBaseName().'.afterFind');
	}
	
	/**
	 * Finds one record by specified condition.
	 * @param string $condition
	 * @param array $params
	 */
	public function find($condition='',array $params=array())
	{
		$command = $this->getCommandBuilder()->createFindCommand($condition,$params);
		return $this->query($command);
	}
	
	/**
	 * Finds a record by primary key
	 * @param mixed $pk primary key of the table
	 * @param string $condition
	 * @param array $params
	 */
	public function findByPk($pk,$condition='',array $params=array())
	{
		$command = $this->getCommandBuilder()->createFindByPkCommand($pk,$condition,$params);
		return $this->query($command);
	}
	
	/**
	 * Find all records by specified condition
	 * @param string $condition
	 * @param array $params
	 */
	public function findAll($condition="", array $params=array())
	{
		$command = $this->getCommandBuilder()->createFindCommand($condition,$params);
		return $this->query($command,true);
	}
	
	/**
	 * Finds one record by specified attributes and condition
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 */
	public function findByAttributes(array $attributes,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createFindByColumns($attributes,$condition,$params);
		return $this->query($command);
	}
	
	/**
	 * Find all records by specified attributes and condition
	 * @param array $attributes
	 * @param string $condition
	 * @param array $params
	 */
	public function findAllByAttributes(array $attributes,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createFindByColumns($attributes,$condition,$params);
		return $this->query($command,true);
	}
	
	/**
	 * Find all records by specified primary key with condition.
	 * @param mixed $pk
	 * @param string $condition
	 * @param array $params
	 */
	public function findAllByPk($pk,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createFindByPkCommand($pk,$condition,$params);
		return $this->query($command,true);
	}

	/**
	 * Finds one record by an SQL command
	 * @param string $sql
	 * @param array $params
	 * @param array $all find all records, defaults to false
	 * @return object ActiveRecord
	 */
	public function findBySql($sql,array $params=array())
	{
		$command = $this->getCommandBuilder()->createSQLCommand($sql,$params);
		return $this->query($command);
	}
	
	/**
	 * Find all records by an SQL command
	 * @param string $sql
	 * @param array $params
	 */
	public function findAllBySql($sql,array $params=array())
	{
		$command = $this->getCommandBuilder()->createSQLCommand($sql,$params);
		return $this->query($command,true);
	}
	
	/**
	 * Use scopes in the find command
	 * @return ActiveRecord
	 */
	public function applyScopes()
	{
		$scopes = array_merge($this->scopes(),array('defaultScope'=>$this->defaultScope()));
		foreach($this->_scopes as $scopeName)
		{
			if(isset($scopes[$scopeName]))
			{
				$this->getCommandBuilder()->createDbCriteria()->setPlaceholders($scopes[$scopeName]);
			}
		}
	}
	
	/**
	 * Save one or more attribute to database  
	 * and immediately update the attribute of current object
	 * for an example $this->saveAttributes(array('field1'=>'1','field2'=>'2'));
	 * @param array $attributes
	 * @return boolean
	 */
	public function saveAttributes(array $attributes){}
	
	/**
	 * Count number of table rows in specified condition
	 * @param string $condition
	 * @param array $params
	 */
	public function count($condition="",array $params=array(),array $criteriaParams=array())
	{
		$command = $this->getCommandBuilder()->createCountCommand($condition,$params,$criteriaParams);
		return $command->queryScalar();
	}
	
	/**
	 * Count records by attributes and condition
	 * @param array $attribute
	 * @param string $condition
	 * @param array $params
	 */
	public function countByAttributes(array $attributes,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createCountByAttributesCommand($attributes,$condition,$params);
		return $command->queryScalar();
	}
	
	/**
	 * Count records by SQL command
	 * @param string $sql
	 * @param array $params
	 */
	public function countBySql($sql,array $params=array())
	{
		$command = $this->getCommandBuilder()->createSQLCommand($sql,$params);
		return $command->queryScalar();
	}
	
	/**
	 * check the record is exists or not.
	 * @param string $condition
	 * @param array $params
	 * @return boolean
	 */
	public function exists($condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createExistsCommand($condition,$params);
		return $command->queryRow()!==false ? true : false;
	}
	
	/**
	 * Check the record is exists or not via SQL.
	 * @param string $sql
	 * @param array $params
	 */
	public function existsBySql($sql,array $params=array())
	{
		$command = $this->getCommandBuilder()->createSQLCommand($sql,$params);
		return $command->queryRow()!==false ? true : false;
	}
	
	/**
	 * Perform a query command from database.
	 * @param unknown_type $command
	 * @param boolean $all fetching one or all record
	 */
	public function query($command,$all=false)
	{
		if($this->beforeFind())
		{
			$this->applyScopes();
			return $all===true ? $this->populateRecords($command->queryAll()) : $this->populateRecord($command->queryRow());
		}
		return null;
	}
	
	/**
	 * This method execute at before saving a record.
	 */
	protected function beforeSave()
	{
		$this->eventNotify('model.'.$this->getModelBaseName().'.beforeSave');
		return true;
	}
	
	/**
	 * When executed the method save or insert or update, 
	 * this method will be call.
	 */
	protected function afterSave()
	{
		$this->eventNotify('model.'.$this->getModelBaseName().'.afterSave');
	}
	
	/**
	 * Insert a new row to database
	 * @param mixed $attributes
	 * @return boolean
	 */
	public function insert($attributes=null)
	{
		if(!$this->getIsNewRecord())
		{
			throw new \RuntimeException("cannot using method insert to update a data row");
		}

		if($this->beforeSave())
		{
			$command = $this->getCommandBuilder()->createInsertCommand($this->getAttributes($attributes));
			if($command->execute())
			{
				foreach($this->getPrimaryKey(true) as $pk=>$val)
				{
					if($this->$pk===null)
					{
						$this->_attributes[$pk] = $command->getLastInsertId($pk);
						break;
					}
				}
				$this->afterSave();
				$this->setIsNewRecord(false);
				$this->setScenario('update');
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Update the current row data.
	 * @param mixed $attributes
	 * @return boolean
	 */
	public function update($attributes=null)
	{
		if($this->getIsNewRecord())
		{
			throw new \RuntimeException("Cannot using method update to insert a data row");
		}
		
		if($this->beforeSave())
		{
			$result = $this->updateByPk($this->getPrimaryKey(),$this->getAttributes($attributes));
			$this->afterSave();
			return true;
		}
		return false;
	}
	
	/**
	 * Update all records by specified condition
	 * @param array $attribute
	 * @param string $condition
	 * @param array $params
	 */
	public function updateAll(array $attributes,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createUpdateCommand($attributes,$condition,$params);
		return $command->execute();
	}
	
	/**
	 * Updates a record by primark key
	 * @param mixed $pk
	 * @param mixed $attributes
	 * @param string $condition
	 * @param array $params
	 */
	public function updateByPk($pk,array $attributes,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createUpdateByPkCommand($pk,$attributes,$condition,$params);
		return $command->execute();
	}
	
	/**
	 * Update data table by specified SQL command and parameters
	 * @param string $sql
	 * @param array $params
	 * @return boolean
	 */
	public function updateBySql($sql,array $params=array())
	{
		$command = $this->getCommandBuilder()->createSQLCommand($sql,$params);
		return $command->execute();
	}

	/**
	 * Update record count field
	 * @param array $values
	 * @param string $condition
	 * @param array $params
	 */
	public function updateCounters(array $counters,$condition='',array $params=array())
	{
		$command = $this->getCommandBuilder()->createUpdateCounterCommand($counters,$condition,$params);
		return $command->execute();
	}
	
	/**
	 * Saves the current record.
	 */
	public function save($runValidation=true,$attributes=null)
	{
		if($this->getScenario()=='insert')
		{
			if($runValidation===true and $this->validate($attributes)===true)
			{
				return $this->insert($attributes);
			}
		}
		
		if($this->getScenario()=='update')
		{
			if($runValidation===true and $this->validate($attributes)===true)
			{
				return $this->update($attributes);
			}
		}
		return false;
	}
	
	/**
	 * Delete an row by primary key.
	 * @return boolean
	 */
	public function delete()
	{
		$command = $this->getCommandBuilder()->createDeleteByPkCommand($this->getPrimaryKey(true));
		if($this->beforeDelete() and $command->execute())
		{
			$this->afterDelete();
			return true;
		}
		return false;
	}
	
	/**
	 * Delete an row by primary key.
	 * @param mixed $pk
	 * @param string $condition
	 * @param array $params
	 * @return boolean
	 */
	public function deleteByPk($pk,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createDeleteByPkCommand($pk,$condition,$params);
		return $command->execute();
	}
	
	/**
	 * Delete all records by specified condition, 
	 * Notice: if the parameter $condition is empty, that will delete all records from table.
	 * @param string $condition
	 * @param array $params
	 */
	public function deleteAll($condition="",array $params=array())
	{
		$command = $this->getCommandBuiler()->createDeleteCommand($condition,$params);
		return $command->execute();
	}
	
	/**
	 * Delete all records by attributes and condition
	 * @param array $attributes
	 * @param unknown_type $condition
	 * @param array $params
	 */
	public function deleteAllByAttribute(array $attributes,$condition="",array $params=array())
	{
		$command = $this->getCommandBuilder()->createDeleteByAttributesCommand($attributes,$condition,$params);
		return $command->execute();
	}
	
	/**
	 * When execute the method delete a delete command, first perform this method.
	 * you may need override this method, and that should be return a boolean.
	 */
	protected function beforeDelete()
	{
		$this->eventNotify('model.'.$this->getModelBaseName().'.beforeDelete');
		return true;
	}
	
	/**
	 * When finished the delete command in method delete, then will be call this method.
	 * you may need override this method.
	 */
	protected function afterDelete()
	{
		$this->eventNotify('model.'.$this->getModelBaseName().'.afterDelete');
	}

	/**
	 * Creates an active record with the given attributes.
	 */
	public function populateRecord($attributes,$callAfterFind=true)
	{
		if($attributes!==false)
		{
			$record=$this->instantiate($attributes);
			$record->setScenario('update');
			$record->init();
			$md=$record->getMetaData();
			foreach($attributes as $name=>$value)
			{
				$record->$name=$value; // assignment for attributes of the sub query
				// if(property_exists($record,$name))
				// {
				// 	$record->{$name} = $value;
				// }
				// if(isset($md->columns[$name]))
				// {
				// 	$record->_attributes[$name] = $value;
				// }
			}
			
			$record->pk=$record->getPrimaryKey();
			$record = $this->createRecordToken($record); // create token for current record.

			if($callAfterFind)
				$record->afterFind();
			return $record;
		}
		return null;
	}

	protected function createRecordToken($record)
	{
		$pk = $record->{$record->getPrimaryKeyName()};
		$state = \Lightworx::getApplication()->getState($this->_stateName);
		$record->{$this->_tokenName} = md5($pk.$state);
		return $record;
	}

	/**
	 * Creates a list of active records based on the input data.
	 */
	public function populateRecords($data,$callAfterFind=true,$index=null)
	{
		$records=array();
		foreach($data as $attributes)
		{
			if(($record=$this->populateRecord($attributes,$callAfterFind))!==null)
			{
				if($index===null)
					$records[]=$record;
				else
					$records[$record->$index]=$record;
			}
		}
		return $records;
	}
	
	/**
	 * Checks whether this AR has the named attribute
	 * @param string $name
	 * @return boolean
	 */
	public function hasAttribute($name)
	{
		return isset($this->getMetaData()->columns[$name]);
	}

	/**
	 * Returns the named attribute value.
	 * @param string $name
	 * @return mixed
	 */
	protected function getAttribute($name)
	{
		if(property_exists($this,$name))
		{
			return $this->{$name};
		}
		
		if(isset($this->_attributes[$name]))
		{
			return $this->_attributes[$name];
		}
	}

	/**
	 * Sets the named attribute value.
	 * @param string $name
	 * @param mixed $value
	 * @return boolean
	 */
	public function setAttribute($name,$value)
	{
		if(property_exists($this,$name))
		{
			$this->{$name} = $value;
		}

		if(!is_array($value))
		{
			$this->_attributes[$name] = $value;
		}else{
			return false;
		}
		return true;
	}
	
	/**
	 * Returns all column attribute values.
	 * @param mixed $names
	 * @return array
	 */
	public function getAttributes($names=true)
	{
		$metadata = $this->getMetaData();
		if($names===true)
		{
			return array_keys($metadata->columns);
		}

		if(is_array($names))
		{
			$this->setAttributes($names);
		}
		return $this->_attributes;
	}

	/**
	 * Set attributes
	 * @param array $attributes
	 */
	public function setAttributes(array $attributes)
	{
		foreach($attributes as $name=>$value)
		{
			$this->setAttribute($name,$value);
		}
	}
	
	/**
	 * Creates an active record instance.
	 * @return ActiveRecord
	 */
	protected function instantiate($attributes)
	{
		$class=get_class($this);
		$model=new $class(null);
		return $model;
	}


	public function eventNotify($event)
	{
		\Lightworx::getApplication()->eventNotify($event,$this);
	}

	public function getModelBaseName()
	{
		$modelExtension = \Lightworx::getApplication()->modelExtension;
		return str_replace($modelExtension,'',get_class($this));
	}
}