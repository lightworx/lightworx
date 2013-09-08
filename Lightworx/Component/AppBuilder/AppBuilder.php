<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\AppBuilder;

use Lightworx\Component\File\Signature;

class AppBuilder
{
	public $params = array();

	public function __construct($argv)
	{
		if(!is_array($argv))
		{
			return false;
		}
		$this->initParams($argv);

		$method = $this->getArgvs($argv,array(1,2));
		if(method_exists($this,$method))
		{
			call_user_func_array(array($this,$method),array_slice($argv,3));
		}
	}

	/**
	 * Get the argvs from command line interface.
	 * @param array $argv
	 * @param array $argvs
	 * @return string
	 */
	protected function getArgvs(array $argv, array $argvs)
	{
		$params = array();
		foreach($argvs as $val)
		{
			if(isset($argv[$val]) and trim($argv[$val])!='')
			{
				$params[] = $argv[$val];
			}
		}
		return lcfirst(implode("",array_map('ucfirst',$params)));
	}

	/**
	 * Initialize the additional parameters.
	 * The additional parameters are starting both midlines, just like the following:
	 * @example command param1 param2 --another-additional-param paramValue
	 * @param array $argv
	 * @return string
	 */
	protected function initParams(array $argv)
	{
		foreach($argv as $key=>$val)
		{
			if(substr($val,0,2)=='--')
			{
				$this->params[substr($val,2)] = $argv[$key+1];
			}
		}
		return $this->params;
	}

	/**
	 * Create an application
	 * this method provides a generate functional for generating the app skeleton.
	 * @param string $name the application name
	 * @param string $path the application generated path.
	 */
	public function createApp($name,$path='')
	{
		if($path=='')
		{
			$path = isset($_SERVER['PWD']) ? $_SERVER['PWD'].'/'.$name : './'.$name;
		}else{
			$path = $path.'/'.$name;
		}

		if(file_exists($path))
		{
			echo "\nThe project ".$name." already exists.\n";
			return false;
		}

		if(mkdir($path,0777,true))
		{
			$templates = $this->initAppFiles();
			
			foreach($templates['paths'] as $subPath)
			{
				mkdir($path.'/'.$subPath,0777,true);
				echo "Create directory: ".$path.'/'.$subPath."\n";
			}

			foreach($templates['files'] as $file=>$code)
			{
				file_put_contents($path.'/'.$file, $code);
				echo "Generate file: ".$path.'/'.$file."\n";
			}
			
			if(file_exists($path.'/tools/app') and chmod($path.'/tools/app',0700))
			{
				echo "set executable permission to $path/tools/app\n";
			}
			echo "\n\n\nCreate Application ".$name." completed.\n\n\n";
		}
	}

	/**
	 * Create a module for an application.
	 * this method like the `createApp`, but the difference with the `createApp` 
	 * is that generate some directories and some files for a module required.
	 * @param string $name The name of the module.
	 * @param string $path The module generated destination path, defaults to an empty string, 
	 *                      that mean the module will be generated in the current directory.
	 */
	public function createModule($name,$path='')
	{
		if($path=='')
		{
			$path = isset($_SERVER['PWD']) ? $_SERVER['PWD'].'/'.$name : './'.$name;
		}else{
			$path = $path.'/'.$name;
		}

		if(file_exists($path))
		{
			echo "\nThe module ".$name." already exists.\n";
			return false;
		}

		if(mkdir($path,0777,true))
		{
			$templates = $this->initModuleFiles();
			
			foreach($templates['paths'] as $subPath)
			{
				mkdir($path.'/'.$subPath,0777,true);
				echo "create directory".$path.'/'.$subPath."\n";
			}

			foreach($templates['files'] as $file=>$code)
			{
				file_put_contents($path.'/'.$file, $code);
				echo "Generate file: ".$path.'/'.$file."\n";
			}

			echo "\n\n\nCreate Module ".$name." completed.\n\n\n";
		}
	}

	/**
	 * Initialize the files and directories skeleton template for an application required.
	 * @return array
	 */
	public function initAppFiles()
	{
		return include(__DIR__.'/initAppTemplate.php');
	}

	/**
	 * Initialize the files and directories skeleton template for a module required.
	 * @return array
	 */
	public function initModuleFiles()
	{
		return include(__DIR__.'/initModuleTemplate.php');
	}

	/**
	 * Creates a scaffold object for generating a model scaffold.
	 * The `scaffold` should be based on a model.
	 * @param string $name The model name
	 * @param string $path Defaults to a '.' Dot, that means generation to the current directory, 
	 *                     in commonly should be a root of the application directory or a module directory.
	 */
	public function createScaffold($name,$path='.')
	{
		new Scaffold($name,$path);
	}

	/**
	 * Validate the signature files. This method to compare the both signature files.
	 * If they are not matched, Indicates someone file or more files were changed.
	 * Both signature content must be unserializable, from a string to an array.
	 * @param string $sign1 the first signature file name
	 * @param string $sign1 the second signature file name
	 */
	public function validateSign($sign1,$sign2)
	{
		$signFile1 = $_SERVER['PWD'].DS.$sign1;
		$signFile2 = $_SERVER['PWD'].DS.$sign2;
		if(file_exists($signFile1) and file_exists($signFile2))
		{
			$sign1Data = file_get_contents($signFile1);
			$sign2Data = file_get_contents($signFile2);
		}
		$sign1Hash = unserialize($sign1Data);
		$sign2Hash = unserialize($sign2Data);
		if(!is_array($sign1Hash) or !is_array($sign2Hash))
		{
			throw new \RuntimeException("trying to convert hash data to array failed.");
		}
		$diff = array_diff($sign1Hash, $sign2Hash);
		if($diff!==array())
		{
			print_r($diff);
		}
	}

	/**
	 * Generate signature for all under the current directory files, 
	 * containing all the sub directories.
	 * @param string $path 
	 * @param string $signFileSuffix the signature file suffix, defaults to '.sign'
	 */
	public function generateSign($path='.',$signFileSuffix='.sign')
	{
		$path = $this->ensurePath($path);
		
		$filter = isset($this->params['skip']) ? array_map('trim',explode(',',$this->params['skip'])) : array();
		$sign = serialize(Signature::signFiles($path,$filter));
		$signFile = $path.'sign-'.date('Y_m_d_H_i_s',time()).$signFileSuffix;
		file_put_contents($signFile,$sign);

		echo "The sign file generated: ".$signFile."\n";
	}

	/**
	 * Startup an internal of the PHP web server
	 * @param string $host Defaults to '127.0.0.1:8000'
	 * @param string $path Defaults to a '.' dot
	 */ 
	public function startServer($host='127.0.0.1:8000',$path='.')
	{
		if(phpversion()>=5.4)
		{
			echo `/usr/local/php5/bin/php -S $host -t $path`;
		}else{
			echo "\n\nThe PHP version must be higher than 5.4.0\n\n";
		}
	}

	public function ensurePath($path)
	{
		if(isset($path[0]) and $path[0]==DS)
		{
			return $path;
		}
		if($path=='.' and isset($_SERVER['PWD']))
		{
			return $_SERVER['PWD'].DS;
		}
		return $_SERVER['PWD'].DS.$path;
	}
}