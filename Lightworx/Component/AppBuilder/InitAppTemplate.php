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
				'log/',
				'message/',
				'models/',
				'modules/',
				'plugins/',
				'public/assets/js/',
				'public/assets/css/',
				'public/assets/images/',
				'runtime/',
				'tests/',
				'themes/',
				'tools/',
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
	
	'data'=>array(
		'connector'=>'PDOConnection',
		'dsn'=>'mysql:dbname=database;host=localhost',
		'username'=>'user',
		'password'=>'password',
		'options'=>array(PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES 'UTF8'\"),
		'tablePrefix'=>'',
	),
	'params' => require_once(__DIR__.'/params.php')
);
	",


	"config/params.php"=>"<?php
return array();
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
	
	"controllers/Controller.php"=>"<?php

use Lightworx\Controller\Controller as BaseController;

class Controller extends BaseController{}",

	"controllers/ServiceController.php"=>"<?php

use Lightworx\Component\Encryption\CryptString;
use Lightworx\Component\Encryption\XorEncrypt;
use Lightworx\Controller\ServiceController as BaseServiceController;

class ServiceController extends BaseServiceController{}",

	"controllers/MainController.php"=>"<?php
class MainController extends Controller
{
	public \$layout = 'main';

	public function actionIndex()
	{
		\$this->render('index');
	}
}",



	"models/ActiveRecord.php"=>'<?php
use Lightworx\Component\HttpFoundation\AssetManager;
use Lightworx\Queryworx\ORM\ActiveRecord as BaseActiveRecord;
class ActiveRecord extends BaseActiveRecord
{
	public function createUrl($rule,array $params=array())
	{
		return \Lightworx::getApplication()->router->createAbsoluteUrl($rule,$params);
	}

	public function getUid()
	{
		return Lightworx::getApplication()->user->getUid();
	}

	public function getServiceUrl()
	{
		// autoload srfl plugin, when the getServiceUrl() invoked.
		$jquery = array(
			"source"=>LIGHTWORX_PATH."Vendors/jQuery/",
			"filter"=>array(".svn",".txt",".DS_Store",".md")
		);
		AssetManager::$resourcePackages["jQuery"] = $jquery;
		AssetManager::attachPackageScriptFile("jQuery","jquery.srfl.min.js");
		
		$serviceName = \Lightworx::getApplication()->serviceControllerName;
		$urlPattern = "{module:[moduleName]}/".$serviceName."/".get_class($this).\'?\'.$this->getPrimaryKeyName().\'=\'.$this->getPrimaryKey();
		return $this->createUrl($urlPattern);
	}
	
	public function createServiceLink($label,$method,$linkOptions=array(\'srfl\'=>\'srfl\'),$url=\'\')
	{
		$defaultLinkOptions = array(
			\'request-url\'=>($url!=\'\' ? $url : $this->getServiceUrl()),
			\'request-method\'=>$method,
		);
		$linkOptions = array_merge($defaultLinkOptions,$linkOptions);
		return \Lightworx\Helper\Html::createLink($linkOptions[\'request-url\'],$label,$linkOptions);
	}
	
	public function getUpdateUrl($urlPattern=\'\')
	{
		if($urlPattern==\'\')
		{
			$urlPattern = "{module:[moduleName]}/".lcfirst(get_class($this)).\'/update?\'.$this->getPrimaryKeyName().\'=\'.$this->getPrimaryKey();
		}
		return $this->createUrl($urlPattern);
	}
}',



	"public/index.php"=>"<?php

define('LIGHTWORX_START_TIME',microtime(true));
define('BASE_PATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('PUBLIC_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
define('LIGHTWORX_PATH','".dirname(dirname(__DIR__)).DS."');
define('RUNNING_MODE','dev');
define('APP_PATH',BASE_PATH);

require_once(dirname(LIGHTWORX_PATH).'/Bootstrap.php');
require_once(LIGHTWORX_PATH.'Lightworx.php');

Lightworx::app(include_once(APP_PATH.'config/config.php'))->run();
	",


	"public/.htaccess"=>"RewriteEngine on\nRewritecond %{REQUEST_FILENAME} !-d\nRewritecond %{REQUEST_FILENAME} !-f\nRewriteRule  .* index.php",


"tools/app"=>"#!/usr/bin/env php
<?php

define('LIGHTWORX_START_TIME',microtime(true));
define('BASE_PATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('PUBLIC_PATH',dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'public/');
define('LIGHTWORX_PATH','".dirname(dirname(__DIR__)).DS."');
define('RUNNING_MODE','dev');
define('APP_PATH',BASE_PATH);

require_once(dirname(LIGHTWORX_PATH).'/Bootstrap.php');
require_once(LIGHTWORX_PATH.'Lightworx.php');
\$conf = array_merge(include_once(APP_PATH.'config/config.php'),array('ApplicationType'=>'CliApplication'));

Lightworx::app(\$conf)->run();

new \Lightworx\Component\AppBuilder\AppBuilder(\$argv);",

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
	'views/main/index.php'=>'<h3>Welcome to choose Lightworx</h3>',
	"public/assets/css/style.css"=>'
	html, body {
  margin:0;
  padding:0;
  font-size:12px;
}
label{
  margin:0;
  padding:0;
  cursor: pointer;
}
a{
  color:#999;
  text-decoration: none;
}
a:hover{
  text-decoration: none;
}

.btn,select, textarea, input[type="text"], input[type="password"], input[type="datetime"], input[type="datetime-local"], input[type="date"], input[type="month"], input[type="time"], input[type="week"], input[type="number"], input[type="email"], input[type="url"], input[type="search"], input[type="tel"], input[type="color"], .uneditable-input{
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
}

body{
  background:#EFEFEF;
}
.left-bracket{
  color:#CCC;
  font-size:400px;
}
.topbar{
  width:100%;
  height:50px;
  margin:0;
  padding:0;
  background:#3b5998;
  line-height:39px;
  color:#FFF;
  overflow: hidden;
}
.topbar .container .logo {
  font-size: 30px;
  font-weight: bold;
  float: left;
  color: #FFF;
}
.topbar .container{
  text-align: right;
  width:960px;
  line-height:50px;
}
.topbar .container a{
  list-style: none;
  font-size:14px;
  color:#EFEFEF;
  text-decoration: none;
  padding:17px 15px;
}
.topbar .container a:hover,
.topbar .container a.active{
  background: #6D84B4;
}
.topbar .container a:hover{
  color:#FFF;
}
header{
  display: block;
  width:100%;
  margin:0;
  padding:10px 0 10px 0;
  background:#EFEFEF;
}
header .logo{
  float:left;
}
header .logo .description{
  float:left;
}
.content-container{
  width:950px;
  min-width:950px;
  margin:20px auto 0 auto;
  background-color: #FFF;
  padding:15px;
  border:1px solid #DDD;
  -moz-border-radius: 4px;
  -webkit-border-radius: 4px;
  border-radius: 4px;
}
.main-container{
  background:#FFF;
  margin:30px auto;
  width:960px;
  padding:5px;
}
#footer{
  width:100%;
  background:#DDD;
  color:#555;
  margin: 50px 0 0 0;
}
#footer .footer-container{
  width:960px;
  margin:0px auto 0 auto;
  padding:50px 0px;
}
',
));