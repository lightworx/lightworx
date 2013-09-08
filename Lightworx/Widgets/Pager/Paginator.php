<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\Pager;

use Lightworx\Foundation\Widget;

/**
 * The widget paginator is a data pagination tool.
 * @version $Id$
 * @since 0.1
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @package Lightworx.Widget.Pager
 */
class Paginator extends Widget
{
	public $pageCount;
	public $linkTemplate;
	public $template = "{page}\n{pageInfo}\n{pageForm}";
	public $theme;
	public $pageRange = 5;
	public $paginatorStyleClass = 'paginator';
	public $pageItemStyleClass = 'page_item';
	public $pageInfoStyleClass = 'page_info';
	public $sequenceTurnPage = true;
	public $alwaysEnableFirstAndLastTag = true;
	public $currentPage;
	public $pageItemTemplate = '<a href="{url}" class="{class}">{pageName}</a>';
	
	// enable ajax page loading
	public $ajaxPageLoadingId = '#main';
	public $enableAjaxPage = false;
	
	/**
	 * When enable ajax to request page, that will go to the specified anchor.
	 */
	public $anchor;
	public $anchorScrollSpeed = 100;
	public $enablePageForm = false;
	public $enablePageInfo = false;
	public static $selfIds;
	
	protected $placeholder = 'page';
	
	public function __construct($currentPage,$pageCount,$pageRequestName='page',$template=null)
	{
		parent::__construct();
		$this->currentPage = $currentPage;
		$this->pageCount = $pageCount;
		$this->linkTemplate = $template;
		$this->placeholder = $pageRequestName;
	}
	
	public function __call($method,$params)
	{
		$pages = array('First'=>1,'Last'=>$this->pageCount,'Previous'=>($this->currentPage-1),'Next'=>($this->currentPage+1));
		if(array_key_exists(($type = substr($method,3,-8)),$pages))
		{
			if(($type=="Previous" and $pages[$type]<=0) or ($type=="Next" and $pages[$type]>$this->pageCount))
			{
				return;
			}
			$link = $this->createPageUrl($pages[$type]);
			$pageStyle = strtolower($type).'_page '.$this->pageItemStyleClass;
			$pageName = $this->__($type.' Page');
			return str_replace(array("{class}","{url}","{pageName}"),array($pageStyle,$link,$pageName),$this->pageItemTemplate);
		}
	}
	
	public function init()
	{
		if($this->pageCount===null or $this->pageCount<=1)
		{
			return false;
		}
		$this->themeFile = __DIR__.'/PagerThemes/css/orange.css';
		
		if($this->theme!==null)
		{
			$themeFile = __DIR__.'/PagerThemes/css/'.$this->theme.'.css';
			if(!file_exists($themeFile))
			{
				throw new \RuntimeException("The theme file:".$themeFile." does not exist.");
			}
			$this->themeFile = $themeFile;
		}
		
		$this->attachCssFile($this->themeFile);
		
		$config = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
		self::publishResourcePackage('jQuery',$config);
		$this->attachPackageScriptFile("jQuery",'jquery.multipleload.js');
		
		if($this->enableAjaxPage===true)
		{
			$selfId = '';
			if(($ids = $this->getIds())!==null)
			{
				$selfId = ','.implode(',',$ids);
			}
			$selector=array();
			foreach($this->getIds() as $id)
			{
				$selector[] = $id.'>.'.$this->pageItemStyleClass;
			}
			$this->addJqueryCode("$('".implode(",",$selector)."').live('click',function (){
					var url = $(this).attr('href');
					var selector = '".$this->ajaxPageLoadingId.$selfId."';
					var replaceObjects = selector.split(',');
					$.multipleLoad.load({url:url,selector:selector,replaceObjects:replaceObjects});
					if('".$this->anchor."'!='')
					{
						var pos = $('".$this->anchor."').offset().top;
						$('html,body').animate({scrollTop: pos}, '".$this->anchorScrollSpeed."');
					}
					return false;
			});",true,$this);
		}
	}
	
	public function run()
	{
		$contents = array();
		preg_match_all('~{(\w+)}~',$this->template,$templates);
		
		foreach($templates[1] as $key=>$placeholder)
		{
			$method = 'render'.ucfirst($placeholder);
			$contents[] = method_exists($this,$method) ? $this->$method() : '';
			$this->template = str_replace($templates[0][$key],"%s",$this->template);
		}
		echo '<div id="'.$this->getId().'" class="'.$this->paginatorStyleClass.'">'.vsprintf($this->template,$contents).'</div>';
	}
	
	/**
	 * Display the page links
	 */
	public function renderPage()
	{
		list($start,$len) = $this->pageRange($this->pageCount);
		$pages = $this->pageLinkItem($start,$len);
		$content  = '';
		
		// first page link
		if($this->alwaysEnableFirstAndLastTag===false)
		{
			if($this->currentPage!==1)
			{
				$content .= $this->getFirstPageItem();	
			}
		}else{
			$content .= $this->getFirstPageItem();
		}
		
		// when the sequenceTurnPage is true, enable previous page link
		if($this->sequenceTurnPage===true)
		{
			$content .= $this->getPreviousPageItem();
		}
		
		// all the page link
		$content .= implode("\n",$pages);
		
		// when the sequenceTurnPage is true, enable next page link
		if($this->sequenceTurnPage===true)
		{
			$content .= $this->getNextPageItem();
		}

		// last page link
		if($this->alwaysEnableFirstAndLastTag===false)
		{
			if($this->currentPage!==$this->pageCount)
			{
				$content .= $this->getLastPageItem();
			}
		}else{
			$content .= $this->getLastPageItem();
		}
		return $content;
	}
	
	/**
	 * Return the pages range
	 * @param integer $page
	 * @return array
	 */
	public function pageRange($page)
	{
		$offset = 1;
		if($this->pageRange!==false and ($this->pageRange*2) < (int)$page)
		{
			if($this->currentPage-1>=$this->pageRange)
			{
				$offset = $this->currentPage-$this->pageRange;
				if($offset+$this->pageRange*2>$page)
				{
					$offset = $offset-(($offset-1+$this->pageRange*2)-$page);
				}
			}
			return array($offset,$this->pageRange*2);
		}
		return array($offset,$page);
	}
	
	/**
	 * Create the page link string
	 * @param integer $pageStart
	 * @param integer $length
	 * @return array
	 */
	public function pageLinkItem($pageStart=1,$length=10)
	{
		$items = array();
		for($i=$pageStart;$i<=$this->pageCount;$i++)
		{	
			if(($this->pageRange>1 or $this->pageRange!==false) and $i>=($pageStart+$length))
			{
				break;
			}
			$pageStyle = ($i==($this->currentPage)) ? "current_page ".$this->pageItemStyleClass : $this->pageItemStyleClass;
			$items[] = str_replace(array("{class}","{url}","{pageName}"),array($pageStyle,$this->createPageUrl($i),$i),$this->pageItemTemplate);
		}
		return $items;
	}
	
	/**
	 * Current the pages url link.
	 * @param integer $page
	 * @return string
	 */
	public function createPageUrl($page)
	{
		$params = array($this->placeholder=>$page);
		return $this->createUrl($this->linkTemplate,$params,true,true,true);
	}
	
	/**
	 * Display the page form
	 * @return string
	 */
	public function renderPageForm()
	{
		$content = '';
		if($this->enablePageForm===true)
		{
			$content .= '<div class="pager_form">';
			$content .= '<span class="label">'.$this->__('Go to:').'</span>';
			$content .= '<input type="text" size="2" name="page" id="'.$this->getId().'_GoToPageValue" value="'.$this->currentPage.'" />';
			$content .= '<span class="label">'.$this->__('page').'</span>';
			$content .= '<a href="#" id="'.$this->getId().'_PageGoTo" class="GoToPageButton">'.$this->__('Go').'</a>';
			$content .= '</div>';
			$this->addJqueryCode('
				$("#'.$this->getId().'_PageGoTo").live("click",function(){GoToPage();return false;});
				$("#'.$this->getId().'_GoToPageValue").live("keydown",function(e){
					var key = e.which;
					if(key==13)
					{
						GoToPage();
						return false;
					}
				});
				function GoToPage(){
					var pageNumber = $("#'.$this->getId().'_GoToPageValue").val();
					var url = "'.$this->createPageUrl('{pageNumber}').'";
					if(pageNumber>'.$this->pageCount.' || pageNumber<=0)
					{
						alert("'.$this->__("The page number must be one of the {one}-{pageCount}",array(
							'{one}'=>'1',
							'{pageCount}'=>$this->pageCount)
						).'");
						return false;
					}
					if(pageNumber!="")
					{
						$("'.$this->ajaxPageLoadingId.'").load(url.replace("{pageNumber}",pageNumber)+ " '.$this->ajaxPageLoadingId.' >*");
					}
					return false;
				}
			');
		}
		return $content;
	}
	
	public function renderPageInfo()
	{
		if($this->enablePageInfo===true)
		{
			$info = array('{currentPage}'=>$this->currentPage,'{pageCount}'=>$this->pageCount);
			return '<div class="'.$this->pageInfoStyleClass.'">'.$this->__('page {currentPage} of {pageCount}',$info).'</div>';
		}
	}
}