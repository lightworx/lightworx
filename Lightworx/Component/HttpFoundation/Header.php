<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Component\HttpFoundation;

class Header implements \ArrayAccess
{
	static public $headers = array();
	
	public $status = array("100"=>"100 Continue",
							"101"=>"101 Switching Protocols",
							"200"=>"200 OK",
							"201"=>"201 Created",
							"202"=>"202 Accepted",
							"203"=>"203 Non-Authoritative Information",
							"204"=>"204 No Content",
							"205"=>"205 Reset Content",
							"206"=>"206 Partial Content",
							"300"=>"300 Multiple Choices",
							"301"=>"301 Moved Permanently",
							"302"=>"302 Found",
							"303"=>"303 See Other",
							"304"=>"304 Not Modified",
							"305"=>"305 Use Proxy",
							"306"=>"306 (Unused)",
							"307"=>"307 Temporary Redirect",
							"400"=>"400 Bad Request",
							"401"=>"401 Unauthorized",
							"402"=>"402 Payment Required",
							"403"=>"403 Forbidden",
							"404"=>"404 Not Found",
							"405"=>"405 Method Not Allowed",
							"406"=>"406 Not Acceptable",
							"407"=>"407 Proxy Authentication Required",
							"408"=>"408 Request Timeout",
							"409"=>"409 Conflict",
							"410"=>"410 Gone",
							"411"=>"411 Length Required",
							"412"=>"412 Precondition Failed",
							"413"=>"413 Request Entity Too Large",
							"414"=>"414 Request-URI Too Long",
							"415"=>"415 Unsupported Media Type",
							"416"=>"416 Requested Range Not Satisfiable",
							"417"=>"417 Expectation Failed",
							"500"=>"500 Internal Server Error",
							"501"=>"501 Not Implemented",
							"502"=>"502 Bad Gateway",
							"503"=>"503 Service Unavailable",
							"504"=>"504 Gateway Timeout",
							"505"=>"505 HTTP Version Not Supported"
						);
	
	public function getAll()
	{
		return self::$headers;
	}
	
	public function offsetSet($offset, $value)
	{
        if (is_null($offset)){
            self::$headers[] = $value;
        } else {
            self::$headers[$offset] = $value;
        }
    }

    public function offsetExists($offset)
	{
        return isset(self::$headers[$offset]);
    }

    public function offsetUnset($offset)
	{
        unset(self::$headers[$offset]);
    }

    public function offsetGet($offset)
	{
        return isset(self::$headers[$offset]) ? self::$headers[$offset] : null;
    }
}