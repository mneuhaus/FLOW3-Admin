<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Minify Plugin
 **/
class Minify extends Plugins
{
	public static function compress()
	{
		self::load_library('Minify_Compressor');
		
		CSS::$css = Minify_CSS_Compressor::process(CSS::$css);
	}
} 
