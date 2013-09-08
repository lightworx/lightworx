<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Queryworx\ORM\Relations;

use Lightworx\Queryworx\Base\Model;

class BaseActiveRelation extends Model
{
	public $model;
	public $className;
	public $foreignKey;
	
	public function __construct($model, $className, $foreignKey, array $options=array())
	{
		$this->model      = $model;
		$this->className  = $className;
		$this->foreignKey = $foreignKey;
		
		foreach($options as $name=>$value)
		{
			$this->$name = $value;
		}
	}
}