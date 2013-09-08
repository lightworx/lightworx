<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <ooofox@gmail.com>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id: Bootstrap.php 21 2011-10-03 13:32:14Z Stephen.Lee $
 */

define('DS',DIRECTORY_SEPARATOR);

defined('LIGHTWORX_PATH') or define('LIGHTWORX_PATH',__DIR__.'/Lightworx/');

/**
 * Lightworx start running time.
 */
defined('LIGHTWORX_START_TIME') or define("LIGHTWORX_START_TIME",microtime(true));

/**
 * Define the framework debugging whether is enable or not,
 * default setting to false, means not enable debug.
 */
defined('LIGHTWORX_DEBUG') or define("LIGHTWORX_DEBUG",false);

/**
 * Application running model, you should be sure that you have to define the constant RUNNING_MODE
 * If not, the system will automatically set the running model as production
 */
defined('RUNNING_MODE') or define('RUNNING_MODE','production');


include_once(LIGHTWORX_PATH.'Foundation'.DS.'ClassLoader.php');

Lightworx\Foundation\ClassLoader::registerNamespaces(array(
	'Lightworx'=> __DIR__.DS
));

Lightworx\Foundation\ClassLoader::register();
Lightworx\Foundation\ClassLoader::helperImport(LIGHTWORX_PATH.'Helper'.DS);