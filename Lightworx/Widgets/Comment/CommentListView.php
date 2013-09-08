<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Comment;

use Lightworx\Widgets\DataList\ListView;

class CommentListView extends ListView
{
	/**
	 * Whether enable the smilie plugin.
	 * @var boolean
	 */
	public $enableSmilie;
	
	public $model;
	
	/**
	 * Whether enable to feedback a comment or not.
	 * @var boolean defaults to false
	 */
	public $enableFeedback = false;
	
	public $commentLimit;
	
	public $feedbackLimit = 140;
	
	public $enableUBB = true;
	
	public $commentForm;
	
	public $template = "{items}\n{pager}\n{commentForm}";
	
	/**
	 * Display Comment Form
	 */
	public function renderCommentForm()
	{
		// if($this->commentForm!==null and isset($this->commentForm['commentFormView']))
		// {
		// 	echo $this->getRender()->renderPartial($this->commentForm['commentFormView']);
		// 	return ;
		// }
		$this->commentForm['model'] = $this->model;
		return $this->getRender()->widget('Lightworx.Widgets.Comment.CommentForm',$this->commentForm,true);
	}
}