<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\DataList;

use Lightworx\Widgets\Pager\Paginator;
use Lightworx\Exception\HttpException;
use Lightworx\Queryworx\Tools\DataProvider;
use Lightworx\Widgets\DataFilter\DataFilter;

class ListView extends BaseListView
{
	public $itemView;
	public $emptyView;
	public $wrapId;
	public $template = "{filters}\n{items}\n{pager}\n{others}";
	public $listViewStyleClass = 'clearfix';
	public $count;

	public $dataProviderParams = array();
	public $withoutBaseCondition = false;
	
	// about page
	public $pageSize = 10;
	public $currentPage = 1;
	public $pageUrlTemplate;
	public $pageRequestName = 'page';
	public $orderRequestName = 'order';
	public $paginatorConfig = array();
	public $anchor;
	public $enableGoToAnchor = false;
	
	protected $order = 'asc';
	protected $orderField;
	protected $enableDataOrder = true;
	protected $orderConstraint = array('asc','desc');
	protected $filterParams = array();

	// The $_contents is a template container.
	protected $_contents = array();

	
	public function initializePage()
	{
		if($this->pageUrlTemplate===null)
		{
			$this->pageUrlTemplate = $this->getApp()->request->getRequestURI();
		}
		
		if($this->anchor===null and $this->enableGoToAnchor===true)
		{
			$this->anchor = $this->getId(true);
		}
		
		if(isset($_GET[$this->pageRequestName]))
		{
			if((int)$_GET[$this->pageRequestName]>0)
			{
				$this->currentPage = (int)$_GET[$this->pageRequestName];
			}else{
				throw new HttpException(404,"Cannot found the page");
			}
		}
	}
	
	public function initializeDataCondition()
	{
		$conditions = array();
		foreach($this->filters as $attribute=>$filter)
		{
			if(isset($_GET[$attribute]) and trim($_GET[$attribute])!='')
			{
				$condition = $this->getFilterClass($this->model,$attribute,$filter)->createFilterCondition($this,$_GET[$attribute]);
				if($condition!==null)
				{
					$conditions[] = $condition;
				}
			}
		}
		
		if(trim(implode('',$conditions))!='')
		{
			if(isset($this->criteriaParams['condition']) and $this->withoutBaseCondition===false)
			{
				$this->criteriaParams['condition'] .= ' AND '.implode(" AND ",$conditions);
			}else{
				$this->criteriaParams['condition'] = implode(" AND ",$conditions);
			}
		}
	}
	
	public function initializeDataProvider()
	{
		if(isset($this->criteriaParams['values']))
		{
			$this->bindValues = array_merge($this->criteriaParams['values'],$this->bindValues);
			unset($this->criteriaParams['values']);
		}
		
		$defaultParams = array(
				'criteriaParams'=>$this->criteriaParams,
				'pageSize'=>$this->pageSize,
				'values'=>$this->bindValues,
		);

		$params = array_merge($defaultParams,$this->dataProviderParams);
		$this->dataProvider = new DataProvider($this->model,$params);
	}
	
	public function init()
	{
		$this->initializePage();
		$this->initializeDataOrder();
		$this->initializeDataCondition();
		$this->initializeDataProvider();
		$this->renderTemplates();
	}

	public function renderTemplates()
	{
		$filters = array();
		preg_match_all('~{(\w+)}~',$this->template,$templates);

		foreach($templates[1] as $key=>$placeholder)
		{
			$method = 'render'.ucfirst($placeholder);
			if(!method_exists($this,$method))
			{
				$this->_contents[] = '';
			}else{
				if($placeholder!='filters')
				{
					$this->_contents[] = $this->$method();
				}else{
					$this->_contents[] = 'filters';
				}
			}
			$this->template = str_replace($templates[0][$key],"%s",$this->template);
		}
		
		if(in_array('filters',$this->_contents))
		{
			$method = 'renderFilters';
			$filters = array_keys($this->_contents,'filters');
			foreach($filters as $filter)
			{
				$this->_contents[$filter] = $this->$method();
			}
		}
	}
	
	/**
	 * Running and display the widget.
	 */
	public function run()
	{
		echo vsprintf($this->template,$this->_contents);
	}
	
	/**
	 * Display list item
	 * @return string
	 */
	public function renderItems()
	{
		$items = $this->dataProvider->getData($this->currentPage);
		$content = array();
		foreach($items as $key=>$item)
		{
			$data['index'] = $key;
			$data['item'] = $item;
			$data['widget'] = $this;
			$content[] = $this->getRender()->renderPartial($this->itemView,$data);
		}
		if($items===array() and $this->emptyView!==null)
		{
			$content[] = $this->getRender()->renderPartial($this->emptyView);
		}
		$this->setReplaceContainerIds('items',$this->getId());
		$content = implode("\n",$content);
		$content = '<div id="'.$this->getId().'" class="'.$this->listViewStyleClass.'">'.$content.'</div>';
		return $content;
	}
	
	/**
	 * Display the pagination links
	 * @param boolean $returnData whether return the string or not.
	 * @return string
	 */
	public function renderPager($returnData=true)
	{
		$condition = isset($this->criteriaParams['condition']) ? $this->criteriaParams['condition'] : '';
		if(($count = $this->dataProvider->getPageCount($condition,$this->bindValues))<=1)
		{
			return ;
		}
		
		$paginator = new Paginator($this->currentPage,(int)$count,$this->pageRequestName,$this->pageUrlTemplate);
		$this->setReplaceContainerIds('pager',$paginator->getId());
		$paginator->language = $this->language;
		$paginator->anchor = $this->anchor;
		$paginator->ajaxPageLoadingId = $this->getId(true);
		foreach($this->paginatorConfig as $property=>$value)
		{
			$paginator->{$property} = $value;
		}
		
		if($returnData===true)
		{
			ob_start();
			ob_implicit_flush(false);
		}
		
		$paginator->init();
		$paginator->run();
		
		if($returnData===true)
		{
			return ob_get_clean();
		}
	}
	
	public function initializeDataOrder()
	{
		if(isset($_GET[$this->orderRequestName]) and $this->enableDataOrder===true)
		{
			if(strpos($_GET[$this->orderRequestName],'.')!==false)
			{
				list($this->orderField,$this->order) = explode('.',$_GET[$this->orderRequestName]);
			}else{
				$this->orderField=$_GET[$this->orderRequestName];
			}
			if(!in_array(trim($this->order),$this->orderConstraint))
			{
				$this->order = 'asc';
			}
			if(!in_array(trim($this->orderField),$this->model->getAttributes()))
			{
				$this->orderField = current($this->model->getPrimaryKeyName());
			}
			$this->criteriaParams['orderBy'] = trim($this->orderField).' '.trim($this->order);
		}
	}
	
	public function createOrderUrl($field)
	{
		$order = $this->order == 'asc' ? 'desc' : 'asc';
		$orderToggle = $field.'.'.$order;
		$params = array($this->pageRequestName=>$this->currentPage,$this->orderRequestName=>$orderToggle);
		$url = $this->getApp()->getRouter()->createUrl($this->pageUrlTemplate,$params,true,true);
		return $url;
	}
	
	/**
	 * Display the data filter 
	 * @return string
	 */
	public function renderFilters()
	{
		return implode("\n",$this->createDataFilters());
	}

	public function renderCount()
	{
		$count = 0;
		if($this->dataProvider!==null)
		{
			$count = $this->dataProvider->count;
		}
		return $count;
	}
}