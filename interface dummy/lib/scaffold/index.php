<?php

/**
 * This file acts as the "front controller" for CSScaffold. You can
 * configure your CSScaffold, modules, plugins and system directories here.
 * PHP error_reporting level may also be changed.
 *
 * @see https://github.com/anthonyshort/csscaffold/tree/master
 */
 
/**
 * Run the installer to help you solve path issues.
 */
define('INSTALL', FALSE);
 
/**
 * Define the website environment status. When this flag is set to TRUE, 
 * errors in your css will result in a blank page rather than displaying
 * error information to the user.
 *
 * The CSS cache will also be locked and unable to be recached.
 */
define('IN_PRODUCTION', FALSE);

/**
 * The document root for the server. If you're server doesn't set this
 * variable, you can manually enter in the server path to the document root
 */
$document_root = $_SERVER['DOCUMENT_ROOT'];

/**
 * CSS directory. This is where you are storing your CSS files.
 *
 * This path can be relative to this file or absolute from the document root.
 */
$css = '../';

/**
 * The path to the scaffold directory. Usually the directory this file
 * is in, but you might have moved the index.php elsewhere.
 */
$scaffold = './';

/**
 * The path to the system folder. This path can be relative to this file 
 * or absolute from the document root.
 */
$system = 'system';

/**
 * Sets the cache path. By default, this is inside of the system folder.
 * You can set it to a custom location here. Be aware that when Scaffold
 * recaches, it empties the whole cache to remove all flagged cache files. 
 */
$cache = 'cache';

/**
 * Path to the plugins directory. This path can be relative to this file 
 * or absolute from the document root.
 */
$plugins = 'plugins';

/**
 * Path to the default config file
 */
$config = 'config.php';

/**
 * Make sure the we're using PHP 5.2 or newer
 */
version_compare(PHP_VERSION, '5.2', '<') and exit('CSScaffold requires PHP 5.2 or newer.');

/**
 * Set the error reporting level. Unless you have a special need, E_ALL is a
 * good level for error reporting.
 */
error_reporting(E_ALL & ~E_STRICT);

/**
 * Setting it to false will remove all errors
 */
ini_set('display_errors', TRUE);

/**
 * If you rename all of your .php files to a different extension, set the new
 * extension here. This option can left to .php, even if this file has a
 * different extension.
 */
define('EXT', '.php');

/**
 * --------------------------------------------------------------------------------
 * Don't touch anything below here.
 * --------------------------------------------------------------------------------
 */

# Path information about the current file
$path = pathinfo(__FILE__);

# This file
define('FRONT', $path['basename']);

# If this is a symlink, change to the real file
is_link(FRONT) and chdir(dirname(realpath(__FILE__)));

# Set the docroot
define('DOCROOT', str_replace('\\', '/', $document_root). '/');

# Check if the paths are relative or absolute
$scaffold = file_exists(realpath($scaffold)) ? realpath($scaffold) : DOCROOT.$scaffold;
$css = file_exists(realpath($css)) ? realpath($css) : DOCROOT.$css;
$system = file_exists(realpath($system)) ? realpath($system) : DOCROOT.$system;
$cache = file_exists(realpath($cache)) ? realpath($cache) : DOCROOT.$cache;
$plugins = file_exists(realpath($plugins)) ? realpath($plugins) : DOCROOT.$plugins;
$config = file_exists(realpath($config)) ? realpath($config) : DOCROOT.$config;

# Set the constants
define('SCAFFOLD',  str_replace('\\', '/', $scaffold). '/');
define('SYSPATH', 	str_replace('\\', '/', $system). '/');
define('CSSPATH', 	str_replace('\\', '/', $css). '/');
define('CACHEPATH', str_replace('\\', '/', $cache). '/');
define('PLUGINS',   str_replace('\\', '/', $plugins). '/');
define('CONFIG',    str_replace('\\', '/', $config));

# URL to the css directory
define('CSSURL', str_replace(DOCROOT, '/', CSSPATH));
define('SYSURL', str_replace(DOCROOT, '/', SYSPATH));

# Clean up
unset($css, $document_root, $path, $system, $cache, $scaffold, $plugins, $config); 

if(INSTALL && !IN_PRODUCTION)
{
	require 'install'.EXT;
}
else
{
	require SYSPATH.'core/Bootstrap'.EXT;
}