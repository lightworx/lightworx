<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Validator;

use Lightworx\Queryworx\ORM\ActiveRecord;

class ExistValidator extends Validator
{
	public $validateExist = true;
	public $object;
	
	public function validateAttribute($object,$attribute)
	{
		$attributeValue = $object->$attribute;
		if($object instanceof ActiveRecord)
		{
			$result = $object->exists($attribute.'=?',array($attributeValue));
			if($result!==$this->validateExist)
			{
				$message = $this->message!==null ? $this->message : "{value} does not exist.";
				$this->addError($object,$attribute,$message);
			}
		}
	}
}