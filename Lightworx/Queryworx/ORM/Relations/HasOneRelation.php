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

class HasOneRelation extends BaseActiveRelation
{
	public function instantiate()
	{
		$relation = new $this->className;
		return $relation->findByPk(array($relation->getPrimaryKeyName()=>$this->model->{$this->foreignKey}));
	}
}