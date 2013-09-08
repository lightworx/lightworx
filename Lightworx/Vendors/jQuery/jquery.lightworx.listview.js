/**
 * Copyright (c) 2011 - 2012, Stephen Lee <ooofox@126.com>
 * All rights reserved.
 *
 * Licensed under the BSD and MIT Licenses:
 */
;(function($){
	jQuery.lightworxListView = {
		load : function(parameters)
		{
			defaults = {
				container:'#filter_temp_container',
				url:'/',
				selector:'#header,#main',
				replaceObjects:null,
			};
			jQuery.extend(defaults,parameters);
			
			if($(defaults.container).length==0)
			{
				$('body').append('<div id="'+defaults.container.replace('#','')+'" style="display:none;"></div>');
			}
			if(defaults.replaceObjects!=null)
			{
				$(defaults.container).load(defaults.url+" "+defaults.selector,function(){
					$.each(defaults.replaceObjects,function(i,n){
						$(n).html($(defaults.container+' > '+n).html());
					});
					$(defaults.container).html('');
				});
			}
		}
	};
})(jQuery);