/**
 * Copyright (c) 2011 - 2012, Stephen Lee <stephen.lee@lightworx.io>
 * All rights reserved.
 * @link http://lightworx.io
 * @version 0.1
 * 
 * Licensed under the MIT License.
 * The SRFL is a HTTP request component.
 */

(function($){
	$.fn.srfl = {
		create : function(params)
		{
			$.fn.srfl.create.defaults = {
				bindObject: '[srfl]',
				triggerEvent: 'click',
				beforeFunction:null,
				beforeSend: $.fn.srfl.beforeSend,
				cursor:'pointer',
				cancelToken:false,
				success:$.fn.srfl.success,
				complete:$.fn.srfl.complete,
				error:$.fn.srfl.error,
				dataFilter:$.fn.srfl.dataFilter,
				completeFunction:null,
				successFunction:null,
				errorFunction:null,
				dataFilterFunction:null
			};
			var triggerEvent = 'click';

			$("body").delegate("[srfl]",triggerEvent,function(){
				jQuery.extend($.fn.srfl.create.defaults,params);
				return $.fn.srfl.request($.fn.srfl.create.defaults,$(this));
			});
			$($.fn.srfl.create.defaults.bindObject).css('cursor',$.fn.srfl.create.defaults.cursor);
		},
		request : function(options,obj){
				if($(obj).attr('ajax-options')!==undefined)
				{
					jQuery.extend(options,jQuery.parseJSON($(obj).attr("ajax-options")));
				}

				options = $.fn.srfl.init(options,obj);
				if(options.beforeFunction!==null && eval(options.beforeFunction)!==true)
				{
					return false;
				}
				options = $.fn.srfl.getData(options,obj);

				$.ajaxSetup(options);
				$.ajax({});
				return options.requestContinue;
		},
		init : function(properties,obj)
		{
			properties.type = $(obj).attr('request-method')!==undefined ? $(obj).attr('request-method') : 'post';
			properties.global = $(obj).attr('request-global')!==undefined ? $(obj).attr('request-global') : false;
			properties.url = $(obj).attr('request-url')!==undefined ? $(obj).attr('request-url') : '';
			properties.beforeFunction = $(obj).attr('before-function')!==undefined ? $(obj).attr('before-function') : null;

			properties.completeFunction = $(obj).attr('after-function')!==undefined ? $(obj).attr('after-function') : null;
			properties.successFunction = $(obj).attr('request-success')!==undefined ? $(obj).attr('request-success') : null;
			properties.errorFunction = $(obj).attr('request-fail')!==undefined ? $(obj).attr('request-fail') : null;
			properties.dataFilterFunction = $(obj).attr('data-filter')!==undefined ? $(obj).attr('data-filter') : null;

			properties.requestContinue = false;

			if($(obj).attr("request-continue")!==undefined)
			{
				properties.requestContinue = $(obj).attr("request-continue")=="true" ? true : false;
			}
			return properties;
		},
		getData : function(properties,obj)
		{
			var seq = 0;
			var submitData = Array();
			var data = Array();

			if($(obj).attr("serialize-form")!==undefined)
			{
				data[0] = $($(obj).attr("serialize-form")).serialize();
			}
			if($(obj).attr("request-data")!==undefined)
			{
				data[1] = $(obj).attr("request-data");
			}
			if($(obj).attr("request-token")!==undefined)
			{
				data[2] = $(obj).attr("request-token");
			}

			$.each(data,function(i,x){
				if(x!==undefined)
				{
					submitData[seq] = x;
					seq++;
				}
			});

			properties.data = submitData.join("&");
			return properties;
		},
		beforeSend : function(xhr)
		{
			if($("meta[name='Csrf-token']").attr("content")===undefined)
			{
				console.log('The Csrf-token is undefined.');
			}

			var token = $("meta[name='Csrf-token']").attr("content");
			if($($.fn.srfl.create.defaults.bindObject).attr('Csrf-token')!==undefined)
			{
				var token = $($.fn.srfl.create.defaults.bindObject).attr('Csrf-token');
			}

			if($.fn.srfl.create.defaults.cancelToken===false)
			{
				xhr.setRequestHeader('Csrf-token',token);
			}
			return true;
		},
		success:function(data, textStatus, jqXHR){
			if($.fn.srfl.create.defaults.successFunction!==null)
			{
				return eval($.fn.srfl.create.defaults.successFunction+"(data, textStatus, jqXHR)");
			}
		},
		complete:function(xhr,textStatus){
			if($.fn.srfl.create.defaults.completeFunction!==null)
			{
				return eval($.fn.srfl.create.defaults.completeFunction+"(xhr,textStatus)");
			}
		},
		error:function(xhr,textStatus,errorThrown){
			if($.fn.srfl.create.defaults.errorFunction!==null)
			{
				return eval($.fn.srfl.create.defaults.errorFunction+"(xhr,textStatus,errorThrown)");
			}
		},
		dataFilter:function(data, type){
			if($.fn.srfl.create.defaults.dataFilterFunction===null)
			{
				return data;
			}
			return eval($.fn.srfl.create.defaults.dataFilterFunction+"(data, type)");
		},
		validate : function()
		{

		}
	}
})(jQuery);