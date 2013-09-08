<?php

namespace Lightworx\Queryworx\Command;


class DbExpression
{
	public $expression;
	
	public $params=array();
	
	public function __construct($expression,array $params=array())
	{
		$this->expression = $expression;
		$this->params = $params;
	}
	
	public function __toString()
	{
		return $this->expression;
	}
}