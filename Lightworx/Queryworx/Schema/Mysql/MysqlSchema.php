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

use Lightworx\Queryworx\Schema\DbSchema;

class MysqlSchema extends DbSchema
{
	public function init()
	{
		$this->databaseName = $this->getDatabaseName();
	}
	
	/**
	 * Get the current database name from DSN
	 * @return string
	 */
	public function getDatabaseName()
	{
		$matches = array();
		if(isset($this->connection->dsn))
		{
			preg_match('/dbname=([^;]+);/',$this->connection->dsn,$matches);
		}
		return isset($matches[1]) ? $matches[1] : false;
	}
}