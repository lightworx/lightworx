<?php

namespace Lightworx\Component\Form;

use Lightworx\Foundation\Plugin;

class FormPlugin extends Plugin
{
	/**
	 * Form instance.
	 */ 
	public $form;

	/**
	 * The plugin name.
	 */
	public $name;

	/**
	 * The plugin options.
	 */
	public $options = array();

	public function register(){}

	public function widget($name,array $settings=array())
	{
		$widget = new $name;
		foreach($settings as $key=>$val)
		{
			$widget->$key = $val;
		}
		$widget->init();
		$widget->run();
	}
}
