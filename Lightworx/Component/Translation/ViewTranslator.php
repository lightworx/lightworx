<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Translation;

class ViewTranslator extends Translator
{
	public $viewMessageFile;
	public $viewMessagePath;
	
	public function __construct($viewMessageFile,$language='')
	{
		$defaultLanguage = \Lightworx::getApplication()->language;
		$this->language = $language!='' ? $language : $defaultLanguage;
		$this->viewMessagePath = APP_PATH.\Lightworx::getApplication()->viewMessagePath;
		$this->setMessageFile($viewMessageFile);
		$this->setLocale($this->language);
	}
	
	public function getMessage()
	{
		$message = $this->viewMessagePath.$this->language.'/'.$this->viewMessageFile;
		if(file_exists($message))
		{
			return include($message);
		}
		return array();
	}

	public function setMessageFile($filename)
	{
		$this->viewMessageFile = $filename;
	}
		
	public function getMessageFile()
	{
		return $this->viewMessageFile;
	}
}