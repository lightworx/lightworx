<?php

return array(

'paths'=>array(
				'authenticator/',
				'cache/',
				'config/authorization/',
				'config/controllers/',
				'config/models/',
				'config/routing/',
				'controllers/',
				'components/',
				'errors/',
				'helpers/',
				'lib/',
				'message/',
				'models/',
				'plugins/',
				'tests/',
				'themes/',
				'validators/',
				'vendors/',
				'viewMessage/',
				'views/layouts/',
				'views/main/',
				'widgets/',
),

'files'=>array(
	"config/config.php"=>"<?php
return array(
	'name' => 'App Name',
	'charset' => 'utf-8',
	'language' => 'zh_cn',
	'applicationPath' => substr(dirname(__FILE__),0,-6),
	'errorReporting' => E_ALL,
	'defaultController' => 'Main',
	'appBootstrap'=>'AppBootstrap',
	'route'=>include_once(__DIR__.'/routing/rules.php'),
	'controllerPath' => 'controllers/',
	'viewPath' => 'views/',
	'themePath' => 'themes/',
	'modelExtension' => '',
	'messagePath' => 'message/',
	'viewMessagePath' => 'viewMessage/',
	'uploadPath'=>'/upload/', // based on public path.
	'pluginExtension'=>'Plugin',
	'formPluginExtension'=>'FormPlugin',
	'import' => array('controllers.*','models.*','components.*','widgets.*','authenticator.*','lib.*','validators.*'),
	'serviceControllerName'=>'service',
);
	",

	"config/routing/rules.php"=>"<?php
return array();
	",


	"lib/AppBootstrap.php"=>'<?php
use Lightworx\Foundation\Bootstrap;
use Lightworx\Component\HttpFoundation\AssetManager;
use Lightworx\Component\HttpFoundation\UserAgent;

class AppBootstrap extends Bootstrap
{
	public function __construct()
	{
		parent::__construct();

		$jquery = array(
			"source"=>LIGHTWORX_PATH."Vendors/jQuery/",
			"filter"=>array(".svn",".txt",".DS_Store",".md")
		);
		AssetManager::$resourcePackages["jQuery"] = $jquery;
		AssetManager::attachPackageScriptFile("jQuery","jquery.min.js");

		$bootstrapConfig = array(
			"source"=>LIGHTWORX_PATH."Vendors/Bootstrap/",
			"filter"=>array(".svn",".txt",".DS_Store",".md")
		);

		AssetManager::$resourcePackages["Bootstrap"] = $bootstrapConfig;
		AssetManager::attachPackageCssFile("Bootstrap","src2.2/css/bootstrap.min.css");
		AssetManager::attachPackageCssFile("Bootstrap","src2.2/css/bootstrap-responsive.min.css");
		AssetManager::attachPackageScriptFile("Bootstrap","src2.2/js/bootstrap.min.js");
	}
}
	',

	"views/layouts/main.php"=>'<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo \Lightworx::getApplication()->charset;?>" />
<meta name="Content-Language" content="<?php echo \Lightworx::getApplication()->language;?>" />
<meta name="Csrf-token" content="<?php echo \Lightworx::getApplication()->getCsrfToken();?>" />
<meta name="Author" content="Lightworx" />
<?php echo \Lightworx\Component\HttpFoundation\AssetManager::publishCssFiles();?>
<link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
<title><?php echo $this->title; echo Lightworx::getApplication()->name;?></title>
<?php echo \Lightworx\Component\HttpFoundation\AssetManager::loadCssCode();?>
</head>

<body>
  <div class="topbar">
    <div class="container">
      <ul class="logo">Lightworx</ul>
      <ul class="navigation">
      </ul>
    </div>
  </div>

	<div class="main-container container">
		<?php echo $content;?>
	</div>

	<div id="footer">
		<div class="footer-container">
			<div class="content">Powered by <a href="http://lightworx.io">Lightworx</a></div>
		</div>
	</div>
	<?php echo \Lightworx\Component\HttpFoundation\AssetManager::publishScriptFiles();?>
	<?php echo \Lightworx\Component\HttpFoundation\AssetManager::loadScriptCode(true);?>
</body>
</html>',
'views/main/index.php'=>'<h3>There is a Lightworx module.</h3>',
));