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

use Lightworx\Queryworx\Connection\DbConnection;

class DbCommand
{
	/**
	 * @var DbConnection the database connection
	 */
	protected $_connection;
	
	/**
	 * Default fetch data mode
	 * @var array
	 */
	protected $_fetchMode = array(\PDO::FETCH_ASSOC);

	/**
	 * @var PDO instance
	 */
	protected $statement;
	
	/**
	 * @var string SQL statement
	 */
	protected $sql;
	
	/**
	 * Parameter list.
	 * @var array
	 */
	private $_paramLog = array();
	
	public function __construct(DbConnection $connection,$sql='')
	{
		$this->setConnection($connection);
		if($sql!='')
		{
			$this->sql = $sql;
		}
	}
	
	/**
	 * Return an SQL statement
	 * @return string
	 */
	public function getSql()
	{
		return $this->sql;
	}
	
	/**
	 * Sets an SQL statement
	 * @param string $sql
	 */
	public function setSql($sql)
	{
		$this->sql = $sql;
	}

	/**
	 * Returns a DbConnection instance
	 * @return object
	 */
	public function getConnection()
	{
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
	 * Binding a param to the SQL statement
	 * @param unknown_type $param
	 * @param unknown_type $variable
	 * @param unknown_type $type
	 * @param unknown_type $length
	 * @param unknown_type $driverOptions
	 */
	public function bindParam($param,&$variable,$type=null,$length=null,$driverOptions=null)
	{
		$this->prepare();
		if($dataType===null)
			$this->statement->bindParam($name,$value,$this->_connection->getPdoType(gettype($value)));
		else if($length===null)
			$this->statement->bindParam($name,$value,$dataType);
		else if($driverOptions===null)
			$this->statement->bindParam($name,$value,$dataType,$length);
		else
			$this->statement->bindParam($name,$value,$dataType,$length,$driverOptions);
		return $this;
	}
	
	/**
	 * Binding a value to the SQL statement
	 * @param unknown_type $name
	 * @param unknown_type $value
	 * @param unknown_type $dataType
	 */
	public function bindValue($name, $value, $dataType=null)
	{
		$this->prepare();
		if($dataType===null)
		{
			$this->statement->bindValue($name,$value,$this->_connection->getPdoType(gettype($value)));
			$this->_paramLog[$name]=$value;
		}else{
			$this->statement->bindValue($name,$value,$dataType);
			$this->_paramLog[$name]=$value;
		}
		return $this;
	}
	
	/**
	 * Binding one or more value to the SQL statement
	 * @param array $values
	 */
	public function bindValues($values)
	{
		$this->prepare();
		foreach($values as $name=>$value)
		{
			$this->statement->bindValue($name,$value,$this->_connection->getPdoType(gettype($value)));
			$this->_paramLog[$name]=$value;
		}
		return $this;
	}
	
	/**
	 * Prepare SQL statement
	 */
	public function prepare()
	{
		try{
			if($this->statement===null)
			{
				$this->statement = $this->getConnection()->getPDOInstance()->prepare($this->sql);
			}
		}catch(\Exception $e){
			throw new \RuntimeException($e->getMessage());
		}
	}
	
	/**
	 * Executing an SQL statement
	 * @param array $params
	 */
	public function execute(array $params=array())
	{
		\Lightworx::trace('sql',$this->getSql(),$this->_paramLog);
		
		try{
			$this->prepare();
			if($params===array())
			{
				$this->statement->execute();
			}else{
				$this->statement->execute($params);
			}
			return $this->statement->rowCount();
		}
		catch(\Exception $e)
		{
			throw new \Exception($e->getMessage());
		}
	}
	
	/**
	 * Perform a query
	 * @param array $params
	 */
	public function query(array $params=array())
	{
		return $this->queryInternal('fetchAll',0,$params);
	}
	
	/**
	 * Perform a query 
	 * @param array $params
	 */
	public function queryScalar(array $params=array())
	{
		$result=$this->queryInternal('fetchColumn',0,$params);
		if(is_resource($result) && get_resource_type($result)==='stream')
		{
			return stream_get_contents($result);
		}else{
			return $result;
		}
	}
	
	/**
	 * Perform a query, fetch mode by column
	 * @param array $params
	 */
	public function queryColumn(array $params=array())
	{
		$this->queryInternal('fetchAll',\PDO::FETCH_COLUMN,$params);
	}
	
	/**
	 * Perform a query, when $fetchAssociative is true, 
	 * then fetch mode is FETCH_ASSOC, otherwise is FETCH_NUM
	 * @param boolean $fetchAssociative default value is true
	 * @param array $params default value is an empty array
	 */
	public function queryRow($fetchAssociative=true,array $params=array())
	{
		return $this->queryInternal('fetch',$fetchAssociative ? $this->_fetchMode : \PDO::FETCH_NUM, $params);
	}
	
	/**
	 * Perform a query, when $fetchAssociative is true, 
	 * then fetch mode is FETCH_ASSOC, otherwise is FETCH_NUM
	 * this method will return all data
	 * @param boolean $fetchAssociative
	 * @param array $params
	 */
	public function queryAll($fetchAssociative=true,array $params=array())
	{
		return $this->queryInternal('fetchAll',$fetchAssociative ? $this->_fetchMode : \PDO::FETCH_NUM, $params);
	}
	
	/**
	 * Perform a query via the parameter $sql
	 * @param string $sql
	 * @param predefined class constant $fetchMethod 
	 *               {@link fetchMethod} http://www.php.net/manual/en/pdo.constants.php 
	 *               reference all of the constants FETCH_*
	 * @param boolean $fetchAssociative
	 * @param array $params
	 */
	public function queryBySql($sql,$fetchMethod,$fetchAssociative=true,$params=array())
	{
		$this->sql = $sql;
		\Lightworx::trace('sql',$this->getSql(),$this->_paramLog);
		return $this->queryInternal($fetchMethod,$fetchAssociative ? $this->_fetchMode : \PDO::FETCH_NUM, $params);
	}
	
	/**
	 * Perform a query, that is a private method, 
	 * that provide a query service for other query method
	 * @param string $method
	 * @param class predefined constant $mode
	 * @param array $params
	 * @throws \RuntimeException
	 */
	private function queryInternal($method,$mode,$params)
	{
		if($this->getConnection()->active===false)
		{
			$this->getConnection()->createConnection();
		}
		
		\Lightworx::trace('sql',$this->sql,$this->_paramLog);
		
		try{
			$this->prepare();
			if($params===array())
			{
				$this->statement->execute();
			}else{
				$this->statement->execute($params);
			}
			$result=call_user_func_array(array($this->statement, $method), (array)$mode);
			$this->statement->closeCursor();
			return $result;
		}catch(\Exception $e){
			throw new \RuntimeException($e->getMessage());
		}
	}
	
	/**
	 * Return the ID of the last inserted row or sequence value
	 * @return string
	 */
	public function getLastInsertId($sequenceName='')
	{
		return $this->_connection->getLastInsertId($sequenceName);
	}
	
	public function beginTranslation(){}

	public function endTranslation(){}


	public function __destruct()
	{
		$this->_connection->closeConnection();
	}
}