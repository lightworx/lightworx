<?php
/**
 * This file is part of the Lightworx
 * @author Stephen Lee <stephen.lee@lightworx.io>
 * @link https://lightworx.io/
 * @license All copyright and license information, please visit the web page
 *           https://lightworx.io/license
 * @version $Id$
 */

namespace Lightworx\Widgets\DataFilter;

use Lightworx\Helper\Html;
use Lightworx\Widgets\DataList\ListView;

class TagFilter extends DataFilter
{
    public $multipleChoice = false;

    public $tags = array(
        array('label'=>'published','value'=>'published','count'=>'10'),
        array('label'=>'draft','value'=>'draft','count'=>'10'),
        array('label'=>'closed','value'=>'closed','count'=>'10'),
    );

    public $delimiter = ".";

    static public $purifierParams = array();

    public $replaceContainerIds = '#main';
    public $requestEvent = 'click';

    public $template = '<div class="attribute-label">{attributeLabel}</div><div class="tag-filter-container"><div id="tmp-Tag-Filter-container" class="hide"></div>{items}</div>';

    public $tagItemTemplate = '<div class="tag-filter-item" value="{value}">{label}<span>{count}</span></div>';

    public function init()
    {
        $this->addCssCode('
            .attribute-label{float:left;line-height:30px;margin-left:20px;margin-top:20px;}
            .tag-filter-item{
                cursor:pointer;
                border:1px solid #DDD;
                float:left;
                margin-left:5px;
                padding:5px;
                background-color:#FFF;
                margin-top:20px;
                position:relative;
                padding-right:30px;
            }
            .tag-filter-item span{
                background:#CCC;
                padding:5px;
                position:absolute;
                top:0;
                right:0;
                margin-left:20px;

            }
            .tag-filter-container div.checked{
                background-color:#DDD;
            }
        ');

        $config = array('source'=>LIGHTWORX_PATH.'Vendors/jQuery/');
        self::publishResourcePackage('jQuery',$config);
        $this->attachPackageScriptFile("jQuery",'jquery.multipleload.js');
        
        $url = $this->getApp()->request->getRequestURI();
        $this->action = strpos($url,'?')!==false ? $url.'&' : $url.'?';

        self::$filterValue[$this->attribute] = "$('#tmp-Tag-Filter-container').html()";

        $this->addJqueryCode("
            function saveSelectedTagFilter(currentObj)
            {
                var selectedTags = $('#tmp-Tag-Filter-container').html().split('.');
                if(selectedTags=='')
                {
                    selectedTags = Array();
                }

                var currentTag = Array($(currentObj).attr('value'));
                
                if($(currentObj).hasClass('checked'))
                {
                    // unchecked the current obj
                    $(currentObj).removeClass('checked');
                    selectedTags = $.grep(selectedTags,function(n,i){
                        return n == $(currentObj).attr('value');
                    },true);
                }else{
                    $(currentObj).addClass('checked');
                    selectedTags = $.merge(selectedTags,currentTag);
                }

                selectedTags = $.unique(selectedTags);
                $('#tmp-Tag-Filter-container').html(selectedTags.join('.'));
            }


            $('".$this->getId(true)."').delegate('.tag-filter-item','".$this->requestEvent."',function(){
                
                saveSelectedTagFilter($(this));

                var selector = '".$this->replaceContainerIds."';
                var url = '".$this->action."'+getAllFilterValues();
                var replaceObjects = selector.split(',');
                var tempContainer = '#".$this->tempContainerId."';
                $.multipleLoad.load({url:url,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
            });
        ");
    }

    protected function getFilterParams($value)
    {
        if(self::$purifierParams!==array())
        {
            return self::$purifierParams;
        }

        if(isset($_GET[$this->attribute]))
        {
            self::$filterValue[$this->attribute] = $_GET[$this->attribute];
            $params = explode($this->delimiter,$value);
            foreach($params as $param)
            {
                self::$purifierParams[] = preg_replace('~(\s|\.|\"|\')~', '', $param);
            }
            return self::$purifierParams;
        }
    }

    public function renderItems()
    {
        if(!is_array($this->tags))
        {
            throw new \RuntimeException('The property tags must be an array.');
        }

        $items = $this->parseTags($this->tags);
        return $items;
    }

    protected function parseTags(array $tags)
    {
        $tagItems = array();
        foreach($tags as $tag)
        {
            $placeholders = array_map(array($this,'wrapPlaceholder'),array_keys($tag));
            $values = array_values($tag);
            $tagItems[] = str_replace($placeholders,$values,$this->tagItemTemplate);
        }
        return implode("\n",$tagItems);
    }

    protected function wrapPlaceholder($placeholder)
    {
        return '{'.$placeholder.'}';
    }

    public function createFilterCondition(ListView $listView,$value)
    {
        if(trim($value)!=='')
        {
            $value = implode('","',$this->getFilterParams($value));
            return $this->attribute.' IN ("'.$value.'")';
        }
    }
}