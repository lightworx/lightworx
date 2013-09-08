<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Test;

require_once LIGHTWORX_PATH."Vendors/PHPUnit/PHPUnit/Autoload.php";
require_once LIGHTWORX_PATH."Vendors/PHPUnit/PHPUnit/Framework/TestCase.php";

/**
 * The PHPUnit should be register to  ClassLoader::$namespaces in manually.
 * First of all open the file Bootstrap.php, set the key `PHPUnit_Framework_TestCase` 
 * in the method registerNamespaces, like the following:
 * @example : 
 * <pre>
 *    Lightworx\Foundation\ClassLoader::registerNamespaces(array(
 *	    'Lightworx'=> __DIR__.DS,
 *      'PHPUnit_Framework_TestCase'=> '' // The PHPUnit path.
 *    ));
 * </pre>
 */ 
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

}