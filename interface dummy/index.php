<?php
require("lib/redbean/redbean.inc.php");
require_once 'lib/Twig/Autoloader.php';
Twig_Autoloader::register();

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$c = array(
	"packages"=> array(
		"Blog"=> array(
			"Blog","Post","Comment","Tag"
		),
		"MyPackage" => array(
			"MyModel"
		)
	),
	"r" => $_REQUEST
);
$p = $_REQUEST["p"] ? $_REQUEST["p"] : "index";

$loader = new Twig_Loader_Filesystem('tpl');
$twig = new Twig_Environment($loader,array("cache"=>false));
$template = $twig->loadTemplate($p.'.html');

$template->display($c);