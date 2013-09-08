<?php
/**
 *  This file is part of the Lightworx
 *  @author Stephen Lee <stephen.lee@lightworx.io>
 *  @link http://lightworx.io/
 *  @license All copyright and license information, please visit the web page
 *           http://lightworx.io/license
 *  @version $Id$
 */

namespace Lightworx\Widgets\Comment;

use Lightworx\Foundation\Widget;

class CommentForm extends Widget
{
	/**
	 * The charset of the comment content, defaults to utf-8
	 * @see http://www.php.net/manual/en/mbstring.supported-encodings.php
	 */
	public $commentCharset = 'utf-8';
	
	/**
	 * The content of the comment
	 * @var integer
	 */
	public $commentLimit = 140;
	
	/**
	 * Whether enable the smilie
	 * @var boolean
	 */
	public $enableSmilie = true;
	
	public $enableCommentSubmitButton = true;
	
	public $enableAjaxSubmit = true;
	
	public $template;
	
	public $commentTextareaOptions = array('class'=>'comment required',"style"=>"height:200px;width:600px;");
	
	public $commentButtonOptions = array("type"=>"button","value"=>"Comment");
	
	public $action;
	
	public $method = 'post';
	
	public $model;
	
	public $commentFormView;
	
	public $formBuilderOptions = array();
	
	public function init()
	{
		if($this->action===null)
		{
			$this->action = $_SERVER['REQUEST_URI'];
		}
	}
	
	public function createFormBuilder()
	{
		$this->formBuilderOptions['model'] = $this->model;
		return $this->getRender()->widget("Lightworx.Widgets.Form.FormBuilder",$this->formBuilderOptions);
	}
	
	public function run()
	{
		if($this->commentFormView!==null)
		{
			echo $this->getRender()->renderPartial($this->commentFormView,array('commentModel'=>$this->model));
			return;
		}
		$this->renderCommentForm();
	}
	
	public function renderCommentForm()
	{
		$form = $this->createFormBuilder();
		
		$commentForm  = $form->beginForm($this->action);
		$commentForm .= $form->errorSummary();
		$commentForm .= $form->label("contents");
		$commentForm .= $form->textArea("contents",$this->commentTextareaOptions);
		$commentForm .= $form->submitButton("submit",array("value"=>"提交"));
		$commentForm .= $form->endForm();
		
		echo $commentForm;
	}
}