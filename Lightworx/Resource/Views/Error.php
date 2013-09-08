<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
	<title>Lightworx error page</title>
	<style>
	html,body
	{
		margin: 0px;
		padding: 0px;
		color: #555;
		height:100%;
		background:#FFF;
		font: normal 10pt Arial,Helvetica,sans-serif;
	}

	#container{
		margin:0px auto 0px auto;
		padding:0px 0px;
		max-width:950px;
		min-height: 100%;
	    position: relative;
		background:#FFF;
	}

	#container div#header{
		background:none repeat scroll 0 0 #3B5998;
		-moz-border-radius: 0px 0px 8px 8px;
		-webkit-border-radius:0px 0px 8px 8px;
		height:50px;
		color:#FFF;
	}

	.logo{
		float:left;
		background-image:url('data:image/png;base64,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');
		height:50px;
		width:110px;
		margin:0px 0px 0px 20px;
		cursor:pointer;
	}

	#container div#header ul.navigation{
		margin:0px 10px 0px 0px;
		padding:0px;
		float:right;
		color:#FFF;
		font-size:14px;
	}
	#container div#header ul.navigation a{
		color:#FFF;
		text-decoration:none;
	}
	#container div#header ul.navigation li{
		margin:0px;
		padding:17px 10px 17px 10px;
		height:100%;
		float:left;
		list-style-type:none;
		cursor:pointer;
	}

	#container div#header ul.navigation li.actived{
		background:#6D84B4;
	}

	#container div#main{
		margin:0px 0px -10px 0px;
		padding:0px 0px 100px 0px;
		background:#FFF;
		clear:both;
		width:100%;
	}

	#container div#main div.debug_box{
		margin:30px 0px 10px 0px;
		padding:5px;
		width:99%;
		background:#FAFAFA;
		-moz-border-radius: 8px;
		-webkit-border-radius:8px;
		border:1px solid #EFEFEF;
		word-wrap:break-word;
	}
	
	#container div#main div.debug_box div.metainfo{
		float:left;
		margin-bottom:20px;
		padding:1%;
		background:#FFF;
	}
	
	#container div#main div.debug_box ol{
		clear:both;
		font: normal 12px Arial,Helvetica,sans-serif;
		line-height:150%;
	}
	
	#container div#main div.debug_box ol li.selected{
		background:#EFEFEF;
	}
	
	#container div#main div.debug_box div.metainfo span{
		float:left;
		width:100%;
		font-size:12px;
	}
	
	#container div#main div.debug_box div.metainfo span.meta_file{}
	
	#container div#main div.debug_box div.metainfo span.meta_line{}
	
	#container div#main div.error_message{
		margin:30px 0px 10px 0px;
		padding:5px;
		width:99%;
		background:#FAFAFA;
		-moz-border-radius: 8px;
		-webkit-border-radius:8px;
		border:1px solid #EFEFEF;
	}
	
	#container div#footer{
		clear:both;
		margin:0px auto;
		padding:20px 10px;
		font-size:12px;
		width:930px;
		height:10px;
		position:absolute;
		bottom:0;
		background:#EEE;
		-moz-border-radius: 8px 8px 0px 0px;
		-webkit-border-radius:8px 8px 0px 0px;
	}
	</style>
</head>

<body>
	<div id="container">
		<div id="header">
			<ul class="logo"></ul>
			<ul class="navigation">
				<li class="actived">Error page</li>
			</ul>
		</div>
		
		<div id="main">
		<?php echo $content; ?>
		</div>
		<div id="footer">Powered by <a href="http://lightworx.io">Lightworx</a></div>
	</div>
</body>
</html>