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

use Lightworx\Queryworx\Schema\DbSchema;

class SqliteSchema extends DbSchema
{
	public function getDatabaseName()
	{
		if(isset($this->connection->dsn))
		{
			return basename($this->connection->dsn);
		}
		return;
	}
}