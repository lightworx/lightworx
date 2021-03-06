<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\Userland;

abstract class Authenticator
{
	public $message = 'Access to the requested resource has been denied.';
	abstract public function isValid($controller,$action);
}