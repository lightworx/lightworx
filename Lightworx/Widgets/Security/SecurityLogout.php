<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link http://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Security;

use Lightworx\Foundation\Widget;
use Lightworx\Widgets\Security\SecurityLink;

class SecurityLogout extends SecurityLink
{
	/**
	 * Specific an element, when the enableAjax is true
	 */
	public $updateHtmlElement;
	
	/**
	 * The logout url.
	 */
	public $action;
	
	/**
	 * The logout link text.
	 */
	public $linkContent = 'Logout';
	
	/**
	 * When the user successful to log out, the backUrl will be redirect.
	 * @var string
	 */
	public $backUrl = '/';
	public $token;
	
	public function init()
	{
		if(!in_array($this->method,$this->methods))
		{
			throw new \RuntimeException("The logout method should be one of ".implode(",",$this->methods));
		}
		
		$this->addJqueryCode('$("'.$this->getId(true).'").live("click",function(){
			$.'.$this->method.'($(this).attr("href"),function(){
				location.href="'.$this->backUrl.'";
			});
			return false;
		});');
	}
	
	public function run()
	{
		$options = array('href'=>$this->action.$this->token,'id'=>$this->getId());
		$options = array_merge($this->linkOptions,$options);
		echo '<a'.$this->getHtmlOptions($options).'>'.$this->linkContent.'</a>';
	}
}