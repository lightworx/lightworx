(function($){
	$.fn.resultNotify = {
		create : function(params)
		{
			$.fn.resultNotify.create.defaults = {
				timeout:5000,
				// the property type supports 'success', 'warning', 'error', 'info'
				type:'success',
				message:null,
				obj:'.result-notify',
			};
			jQuery.extend($.fn.resultNotify.create.defaults,params);
			$($.fn.resultNotify.create.defaults.obj).css("top","-"+$($.fn.resultNotify.create.defaults.obj).outerHeight(true)+"px");
		},
		show : function(message,title,type)
		{
			var options = $.fn.resultNotify.create.defaults;
			// set content to the result notify div.
			$(options.obj).children('p').html(message);
			$(options.obj).children('h4.title').html(title || 'Message');
			$(options.obj).css("display","block");

			if((type || options.type)=='success' && $(options.obj).hasClass('alert-error'))
			{
				$(options.obj).removeClass('alert-error');
			}

			$(options.obj).addClass('alert-'+(type || options.type));
			$(options.obj).animate({top: 0}, "slow");
			setTimeout(
				function(){
					$(options.obj).animate({top: "-"+$($.fn.resultNotify.create.defaults.obj).outerHeight(true)+"px"}, "slow",
						function()
						{
							$(options.obj).css("display","none");
						}
					);
				},options.timeout
			);
		},
		complete : function(xhr,textStatus,updateElements,updateUrl,redirectUrl) // when the request complete
		{
			if (xhr.getResponseHeader("lightworx-http-error")){
				var errorMessage = "";
				$.each($.parseJSON(xhr.getResponseHeader("lightworx-http-error")),function(i,x){
					errorMessage += "<li>"+x+"</li>";
				});
				$.fn.resultNotify.show(errorMessage,"返回结果","error");
			}else{
				if(redirectUrl!==undefined){
					$.fn.resultNotify.redirect(redirectUrl);
				}
				$.fn.resultNotify.redraw(updateElements,updateUrl);
			}
		},
		redraw : function(updateElements,updateUrl){
			// initialize ajax options.
			$.ajaxSetup({type:'get',data:''});
			if(updateUrl==undefined){
				var updateUrl = window.location.href;
			}

			if(updateElements!==undefined){
				var selector = updateElements;
				var replaceObjects = selector.split(',');
				var tempContainer = '#filter_temp_container';
				$.multipleLoad.load({url:updateUrl,selector:selector,tempContainer:tempContainer,replaceObjects:replaceObjects});
				$.ajaxSetup({type:'get',data:''});
			}
		},
		redirect : function(url,timeout){
			if(timeout==undefined)
				var timeout = 3000;	
			if(url==undefined)
				var url = window.location.href;
			setTimeout(
				function(){
					window.location = url;
				},timeout
			);
		}
	}
})(jQuery);