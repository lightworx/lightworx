<?php

namespace Lightworx\Widgets\DataList;

class DatabaseReader extends Widget
{
	public $template = "{databaseList}\n{tableList}";
	public $databaseHandler;
	
	public $dbListCommand = "SHOW DATABASES";
	
	public $tableListCommand = "SHOW TABLES FROM";
	public $fieldListCommand = "SHOW COLUMNS FROM";
	
	public function init(){}
	public function run(){}
}