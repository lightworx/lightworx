<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Captcha;

use Lightworx\Foundation\Widget;

require_once LIGHTWORX_PATH.'Vendors/Captcha/recaptcha-php/recaptchalib.php';

class Recaptcha extends Widget
{
	public $publicKey;
	public $privateKey;
	
	protected $valid = false;
	protected $response;
	protected $error;
	
	public function init(){}
	
	/**
	 * Whether pass the validation
	 * @return boolean
	 */
	public function getValid()
	{
		if(isset($_POST["recaptcha_response_field"]))
		{
       		$this->response = \recaptcha_check_answer (
						$this->privateKey,
						$_SERVER["REMOTE_ADDR"],
						$_POST["recaptcha_challenge_field"],
						$_POST["recaptcha_response_field"]
			);

			if($this->response->is_valid)
			{
				$this->valid = true;
			}else{
				$this->valid = false;
				$this->error = $this->response->error;
			}
		}
		return $this->valid;
	}
	
	public function run()
	{
		echo \recaptcha_get_html($this->publicKey, $this->error);
	}
}