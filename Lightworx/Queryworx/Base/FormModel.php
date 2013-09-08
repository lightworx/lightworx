<?php

namespace Lightworx\Queryworx\Base;

class FormModel extends Model
{
	/**
	 * Validation the submit data
	 * if the submit data using the XMLRequest method and validate failure,
	 * that will be return a error message, using the JSON format.
	 * otherwise, that will display the number '1', that means validate is successfully.
	 */
	public function validateForm(array $attributes)
	{
		$this->attributes = $attributes;
		$this->validate();
	}
}