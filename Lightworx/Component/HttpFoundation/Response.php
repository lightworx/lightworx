<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id: Response.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

namespace Lightworx\Component\HttpFoundation;

use Lightworx\Foundation\Object;
use Lightworx\Component\Renderer;
use Lightworx\Component\HttpFoundation\Header;
use Lightworx\Component\HttpFoundation\Request;

class Response extends Object
{
	/**
	 * Instance of class \Lihgtworx\Component\HttpFoundation\Header
	 * @var object
	 */
	public $header;

	/**
	 * The response header options.
	 * @var array
	 */
	public $headers = array();
	
	/**
	 * Setting whether compressing contents.
	 * @var boolean
	 */
	public $gzip = true;

	public function __construct()
	{
		$this->setHeader(new Header);
	}
	
	/**
	 * Outputing the contents to browser and send the header information.
	 * @param string $content
	 */
	public function output($content)
	{
		$content = $this->compressContent($content);
		$this->sendHeader();
		echo $content;
		exit(0);
	}
	
	/**
	 * Compressing the contents and setting the content encoding.
	 * if the contents length less than $compressBuffer, that will do not to compress.
	 * @param string $content
	 * @param integer $compressBuffer
	 * @return string
	 */
	public function compressContent($content,$compressBuffer = 2048)
	{
		$compressType = \Lightworx::getApplication()->getRequest()->getBrowserCompressType();
		if($this->gzip and $compressType!==false and strlen($content) > $compressBuffer)
		{	
			$this->header['Content-Encoding'] = $compressType;
			$content = gzencode($content);
		}else{
			ob_end_flush();
		}
		return $content;
	}
	
	/** 
	 * Send header information to browser. 
	 * if the server was already sent header information to the browser, 
	 * that will return false, and do nothing.
	 */
	public function sendHeader()
	{
		if(headers_sent())
		{
			return false;
		}

		$headers = array_merge($this->header->getAll(),$this->headers);
		
		foreach($headers as $key=>$value)
		{
			header($key.':'.$value);
		}
	}
	
	public function sendFile(){}
}