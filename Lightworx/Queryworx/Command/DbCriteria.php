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

use Lightworx\Queryworx\Schema\TableSchema;

class DbCriteria
{
	/**
	 * SQL command template, contained find, insert, update and delete.
	 * @var array
	 */
	private $_templates = array(
						"find"=>"SELECT {:distinct:} {:fields:} {:count:} FROM {:tableName:} {:tableAlias:} {:join:} {:condition:} {:groupBy:} {:having:} {:orderBy:} {:limit:} {:offset:}",
						"insert"=>"INSERT INTO {:tableName:}({:fields:})VALUES({:values:})",
						"update"=>"UPDATE {:tableName:} SET {:keyValuePair:} {:join:} {:condition:} {:orderBy:} {:limit:} {:offset:}",
						"delete"=>"DELETE FROM {:tableName:} {:join:} {:condition:} {:groupBy:} {:having:} {:orderBy:} {:limit:} {:offset:}",
	);
	
	/**
	 * Specified the command scenario, 
	 * that should be one of the property $_templates
	 * @var string
	 */
	private $_scenario;
	
	/**
	 * Database schema instance
	 * @var DbSchema
	 */
	private $_schema;
	
	/**
	 * Store the value of the SQL placeholder. 
	 * @var array
	 */
	public $placeholders = array();
	
	/**
	 * The alias name the database table, default value is 't'
	 * @var string
	 */
	public $tableAlias='t';
	
	/**
	 * Creates an SQL command
	 * @param string $command
	 */
	public function __construct(TableSchema $schema,$scenario = "find")
	{
		if(!isset($this->_templates[$scenario]))
		{
			throw new \RuntimeException("The ".$scenario." scenario have not defined.");
		}
		$this->_schema = $schema;
		$this->_scenario = $scenario;
		$this->initPlaceholders($scenario);
	}
	
	/**
	 * Initialize the SQL command placeholder, 
	 * that will gets all of the placeholder from SQL template. 
	 * @param string $scenario
	 */
	public function initPlaceholders($scenario)
	{
		$matches = array();
		$regexp = '/{\:(\w+)\:}/';
		preg_match_all($regexp,$this->_templates[$scenario],$matches);
		
		if(!isset($matches[1]))
		{
			return;
		}
		
		foreach($matches[1] as $placeholder)
		{
			$this->placeholders[$placeholder] = null;
		}
	}
	
	/**
	 * Get specified property of the SQL placeholder, 
	 * if it is exists, that will be return the value of the $name.
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if(isset($this->placeholders[$name]))
		{
			return $this->placeholders[$name];
		}
		return null;
	}
	
	/**
	 * Assign a value to specified placeholder.
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name,$value)
	{
		$method = "set".ucfirst($name);
		if(method_exists($this,$method))
		{
			$this->{$method}($value);
		}else{
			$this->placeholders[$name] = $value;
		}
	}
	
	/**
	 * Returns a command template by current scenario
	 */
	public function getCommandTemplate()
	{
		return isset($this->_templates[$this->_scenario]) ? $this->_templates[$this->_scenario] : false;
	}
	
	/**
	 * Set properties for SQL placeholders
	 * @param array $properties
	 */
	public function setPlaceholders(array $properties)
	{
		foreach($properties as $property=>$value)
		{
			$method = "set".ucfirst($property);
			if(method_exists($this,$method))
			{
				$this->{$method}($value);
			}else{
				$this->placeholders[$property] = $value;
			}
		}
	}
	
	/**
	 * Gets all of the SQL placeholder.
	 * @return array
	 */
	public function getPlaceholders()
	{
		return $this->placeholders;
	}
	
	/**
	 * Set table name for property distinct
	 * @param string $distinct
	 */
	public function setDistinct($distinct)
	{
		if($this->placeholders['distinct']===null)
		{
			$this->placeholders['distinct'] = $distinct;
		}
	}
	
	/**
	 * Set table name for property tableName
	 * @param string $count
	 */
	private function setCount($count)
	{
		if($this->placeholders['count']===null)
		{
			$this->placeholders['count'] = $count;
		}
	}
	
	/**
	 * Set table name for property tableName
	 * @param string $tableName
	 */
	private function setTableName($tableName)
	{
		if(isset($this->tableAlias))
		{
			$this->placeholders['tableName'] = $tableName." ";
		}else{
			$this->placeholders['tableName'] = $tableName;
		}
	}
	
	/**
	 * Sets a value for property condition.
	 * @param string $condition
	 */
	private function setCondition($condition)
	{
		if($this->placeholders['condition']===null)
		{
			$this->placeholders['condition'] = " WHERE ".$condition;
		}else{
			$this->placeholders['condition'] = $this->placeholders['condition'].' AND '.$condition;
		}
	}
	
	/**
	 * Sets a value for property orderBy.
	 * @param string $orderBy
	 */
	private function setOrderBy($orderBy)
	{
		if($this->placeholders['orderBy']===null)
		{
			$this->placeholders['orderBy'] = " ORDER BY ".$orderBy;
		}else{
			$this->placeholders['orderBy'] = $this->placeholders['orderBy'].','.$orderBy;
		}
	}
	
	/**
	 * Sets a value for property limit.
	 * @param string $limit
	 */
	private function setLimit($limit)
	{
		if($this->placeholders['limit']===null)
		{
			$this->placeholders['limit'] = " LIMIT ".$limit;
		}
	}
	
	/**
	 * Sets a value for property offset.
	 * @param string $limit
	 */
	private function setOffset($offset)
	{
		if($this->placeholders['offset']===null)
		{
			$this->placeholders['offset'] = " OFFSET ".$offset;
		}
	}
	
	/**
	 * Sets a value for property groupBy.
	 * @param string $groupBy
	 */
	private function setGroupBy($groupBy)
	{
		if($this->placeholders['groupBy']===null)
		{
			$this->placeholders['groupBy'] = " GROUP BY ".$groupBy;
		}else{
			$this->placeholders['groupBy'] = $this->placeholders['groupBy'].','.$groupBy;
		}
	}
	
	/**
	 * Sets a value for property having.
	 * @param unknown_type $having
	 */
	private function setHaving($having="")
	{
		if($this->placeholders['having']!="")
		{
			$this->placeholders['having'] = " HAVING ".$having;
		}
	}

	/**
	 * Sets a value for property join.
	 * @param string $join
	 */
	private function setJoin($join="")
	{
		if($this->placeholders['join']!="")
		{
			$this->placeholders['join'] = " ".$join." ";
		} 
	}
	
	/**
	 * Set the property fields
	 * @param string $fields
	 */
	private function setFields($fields)
	{
		if($this->placeholders['fields']=="" or trim($this->placeholders['fields']) == "*")
		{
			$this->placeholders['fields'] = $fields;
		}
	}
	
	/**
	 * Initialize the table field, if the property fields have no set, 
	 * that will assign a default value for property fields.
	 */
	private function initFields()
	{
		if(!isset($this->placeholders['fields']) or trim($this->placeholders['fields']) == "*")
		{
			$columns = array();
			foreach($this->_schema->columns as $key=>$column)
			{
				$columns[] = isset($this->tableAlias) ? ($this->_schema->quoteTableName($this->tableAlias).'.').$column->rawName :$column->rawName;
			}
			return implode(", ",$columns);
		}
	}
	
	/**
	 * If the count is not null, that will append a comma at begin of the property count .
	 */
	private function initCount()
	{
		if($this->placeholders['count']!==null and trim($this->placeholders['fields'])!="")
		{
			$this->placeholders['count'] = ','.$this->placeholders['count'];
		}
	}
	
	/**
	 * If the distinct is not null, that will append a comma at end of the property count .
	 */
	private function initDistinct()
	{
		if($this->placeholders['distinct']!==null)
		{
			$this->placeholders['distinct'] = $this->placeholders['distinct'].',';
		}
	}
	
	/**
	 * Return an SQL command by specified scenario
	 * @return string
	 */
	public function __toString()
	{
		$sql = $this->getCommandTemplate();
		foreach($this->placeholders as $property=>$value)
		{
			$method = "init".ucfirst($property);
			if(method_exists($this,$method))
			{
				$this->{$property} = $this->{$method}();
			}
			$sql = str_replace("{:".$property.":}",$this->{$property},$sql);
		}
		return $sql;
	}
}