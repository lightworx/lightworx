<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\File;

class FileInfo
{
	/**
	 * Return the sha1 hash of a given file.
	 * @param string $file the filename
	 * @return string Returns a string on success, FALSE otherwise.
	 */
	public static function getFileSha1($file,$rawOutput=false)
	{
		return sha1_file($file,$rawOutput);
	}
	
	/**
	 * Return the md5 hash of a given file.
	 * @param string $file the filename
	 * @return string Returns a string on success, FALSE otherwise.
	 */
	public static function getFileMd5($file,$rawOutput=false)
	{
		return md5_file($file,$rawOutput);
	}
	
	/**
	 * The method convert from bytes to integer size
	 * @param integer $bytes
	 * @param integer $precision
	 * @return string
	 */
	public static function bytesToSize($bytes, $precision = 2)
	{
    	$unit = array('B','KB','MB');
    	return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision).' '.$unit[$i];
	}

	/**
	 * The method convert from size to bytes
	 * @param string $size the size of data, for example: 5MB
	 * @return integer
	 */
	public static function sizeToBytes($size)
	{
		$units = array('B'=>1,'K'=>1024,'M'=>1048576,'G'=>1073741824);
		$unit = strtoupper(substr(str_ireplace('b','',$size),-1));
		if(isset($units[$unit]))
		{
			return (int)$size*$units[$unit];
		}
		return (int)$size;
	}
}