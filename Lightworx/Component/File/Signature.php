<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\File;

use Lightworx\Component\Iterator\FileFilterIterator;

class Signature
{
	static public $hashAlgorithm = 'md5_file';

	static public function sign($file)
	{
		$func = self::$hashAlgorithm;
		if(function_exists($func))
		{
			return $func($file);
		}
		throw new \RuntimeException('The function:'.$func.' cannot found.');
	}

	static public function signFiles($path,$filter = array())
	{
		$dirIterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
		$filterIterator = new FileFilterIterator($dirIterator);
		$filterIterator->filters = $filter;
		
		$iterator = new \RecursiveIteratorIterator($filterIterator, \RecursiveIteratorIterator::SELF_FIRST);

		$hashContainer = array();

		foreach($iterator as $item)
		{
			if($item->isFile())
			{
				$file = $item->getPath().DS.$item->getFilename();
				$hashContainer[$file] = self::sign($item);
			}
		}
		return $hashContainer;
	}
}