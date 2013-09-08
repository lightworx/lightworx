<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Translation;

use Lightworx\Exception\FileNotFoundException;

class Translator
{
	public $object;
	public $defaultMessagePath;
	public $messagePath;
	public $appMessagePath;
	
	public function __construct($object)
	{
		if(!is_object($object))
		{
			throw new \RuntimeException("The parameter \$object must be an object.");
		}
		
		$this->object = $object;
		
		if(property_exists($object,'language') and $object->language!==null){
			$this->language = $object->language;
		}else{
			$this->language = \Lightworx::getApplication()->language;
		}
		
		// intialize the framework message path
		$this->defaultMessagePath = LIGHTWORX_PATH.'Resource/Messages/';
		
		// reassign the framework message path, if the app specified the custom message path.
		if(\Lightworx::getApplication()->lightworxMessagePath!==null)
		{
			$this->messagePath = \Lightworx::getApplication()->lightworxMessagePath;
		}
		$this->appMessagePath = APP_PATH.\Lightworx::getApplication()->messagePath;
		$this->setLocale($this->language);
	}
	
	/**
	 * @see http://cn2.php.net/manual/en/function.gettext.php
	 */
	public function setLocale($language)
	{
		putenv('LC_ALL='.$language);
		setlocale(LC_ALL,$language);
	}
	
	/**
	 * Return the specified message.
	 * @param string $string
	 * @return string
	 */
	public function __($string,array $placeholders=array())
	{
		\Lightworx::getApplication()->eventNotify('translate.'.$string,$this);
		
		$message = $this->getMessage();
		
		if(isset($message[$string]))
		{
			$localMessage = $message[$string];
		}else{
			$localMessage = $string;
		}
		
		foreach($placeholders as $placeholder=>$value)
		{
			$localMessage = str_replace($placeholder,$value,$localMessage);
		}
		return $localMessage;
	}
	
	/**
	 * Finding and include the message file,
	 * if the specified message file does not exist,
	 * that will include the default message file.
	 * @return array
	 */
	public function getMessage()
	{
		$classMessageFileName = $this->getMessageFileName();
		
		$appMessageFile = $this->appMessagePath.$this->language.$classMessageFileName;
		if($this->appMessagePath!==null and file_exists($appMessageFile))
		{
			return include($appMessageFile);
		}
		
		$messageFile = $this->messagePath.$this->language.$classMessageFileName;
		if($this->messagePath!==null and file_exists($messageFile))
		{
			return include($messageFile);
		}
		
		$defaultMessageFile = $this->defaultMessagePath.$this->language.$classMessageFileName;
		if(file_exists($defaultMessageFile))
		{
			return include($defaultMessageFile);
		}
	}
	
	/**
	 * Return the message file name.
	 * @return string
	 */
	public function getMessageFileName()
	{
		return "/".str_replace("\\","/",get_class($this->object)).'.php';
	}
}