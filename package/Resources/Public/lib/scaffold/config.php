<?php defined('FRONT') OR die('No direct access allowed.');

/**
 * Cache Lock
 *
 * If you lock the cache, it will never recache your css
 */	
$config['cache_lock'] = false;

/**
 * Always Recache
 *
 * If true, it will recache the css every time. This means
 * you don't need to do ?recache during development.
 */	
$config['always_recache'] = false;
 
/**
 * Show CSS rendering information
 *
 * Output information at the top of your cached file.
 */
$config['show_header'] = true;

/**
 * Automatically include mixins
 *
 * By default, Scaffold includes any and all mixin files stored
 * in framework/mixins, to save the user the trouble of including
 * them by themselves. If you want Scaffold to run faster, you can
 * include them manually.
 *
 * Setting this to false means you need to include the framework/mixins manually.
 */
$config['auto_include_mixins'] = true;

/**
 * Override CSS @import
 *
 * Scaffold normally uses @include to import files, rather than
 * overriding the standard CSS @import. You can change this, and
 * use @import instead by setting this to true.
 *
 * Setting this to true means you'll use @import instead of @include
 */
$config['override_import'] = false;

/**
 * Make all URL paths absolute
 *
 * If you're calling CSS using scaffold/index.php?request=path/to/css method,
 * the relative paths to images will break in the browser, as it will
 * be looking for the images inside the scaffold folder. To fix this, Scaffold
 * can make all url() paths absolute.
 *
 * If you're having image path issues, set this to true.
 */
$config['absolute_urls'] = false;

/**
 * Use CSS-style constants
 *
 * You can use a syntax similar to the proposed CSS variable syntax which is
 * const(constantname) instead of the SASS-style !constantname
 *
 * Setting this to true uses the const(constantname) syntax for constants
 */
$config['use_css_constants'] = false;

/**
 * Minify/Prettify
 *
 * You can use the minify library to compress your CSS. Minify strips all 
 * unnecessary whitespace, empty and redundant selectors etc.
 *
 * By setting this to false, instead of minifying your CSS, it will prettify it, 
 * making it easier to read instead of worrying about compressing down the size. 
 */
$config['minify_css'] = false	;

/**
 * Custom Global CSS Constants
 *
 * You can set basic constants here that can be access throughout your 
 * entire project. I'd advise that you don't add stylesheet-specific styles
 * here (like colours), instead, just add any constants you might need,
 * like $_SERVER variables.
 *
 * If you want to add more complex constant-setting logic, create a plugin.
 */
$config['constants'] = array
(
	'scaffold_url' 	=> SYSURL,
	'css_url' 		=> CSSURL,
);
		
/**
 * Debug
 *
 * Enable Firebug output. You need Firebug and FirePHP for Firefox.
 * This is handy when you're viewing the page the CSS is used on,
 * as it will display CSScaffold errors in the console.
 *
 */
$config['debug'] = true;

/**
 * Language
 *
 * Choose a language. Currently, only supports English
 */
$config['language'] = 'english';

/**
 * Enabled Plugins
 *
 * Set which plugins are currently enabled. Plugins may also have
 * their own configuration options. Check the plugin folder for a 
 * config.php file to customize the way the plugin works.
 *
 * All of these plugins are already installed. This is where you can 
 * turn them on or off.
 * 
 * To install a new plugin, drop it in scaffold/plugins, then add it to
 * this list. For more information on any of these plugins, or about
 * creating your own plugins, check the wiki on Github.
 * 
 */
$config['plugins'] = array
(
	# Easily image replace text. Just use the image-replace property
	# and give it a url() like a normal image. Scaffold takes care of the rest.
	'ImageReplace' => true,

	# Set constants via XML, allowing a CMS to tie itself in with
	# your CSS files. 
	'XML_constants' => false
);