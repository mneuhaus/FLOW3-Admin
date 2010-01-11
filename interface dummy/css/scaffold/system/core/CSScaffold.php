<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * CSScaffold
 *
 * Handles all of the inner workings of the framework and juicy goodness.
 * This is where the metaphorical cogs of the system reside. 
 *
 * @author Anthony Short
 **/
final class CSScaffold 
{	 
	/**
	 * The configuration settings
	 */
	private static $configuration;
	
	/**
	 * Stores the user agent string
	 *
	 * @var string
	 */
	public static $user_agent;
	
	/**
	 * The location of the cache file
	 *
	 * @var string
	 */
	private static $cached_file; 
	
	/**
	 * Internal cache
	 */
	private static $internal_cache;
		
	/**
	 * Stores the flags
	 *
	 * @var array
	 */
	public static $flags;
	
	/**
	 * Include paths
	 *
	 * @var array
	 */
	private static $include_paths;
	
	/**
	 * System-required URL params.
	 *
	 * @var array
	 */
	protected static $system_url_params = array
	(
		'recache',
		'request',
	);
	
	/**
	 * Modules
	 */
	protected static $modules = array
	(
		'Constants',
		'Expression',
		'Import',
		'Iteration',
		'Mixins',
		'NestedSelectors',
		'Minify',
		'Layout',
		'Typography',
		'Validate'
	);
	
	/**
	 * Plugins that are installed
	 */
	public static $plugins = array();
	 
	/**
	 * Sets the initial variables, checks if we need to process the css
	 * and then sends whichever file to the browser.
	 *
	 * @return void
	 * @author Anthony Short
	 **/
	public static function setup($url_params) 
	{
		static $run;

		# This function can only be run once
		if ($run === TRUE)
			return;
		
		# Change into the system directory
		chdir(SYSPATH);
		
		# Load the include paths
		self::include_paths(TRUE);
		
		# Turn on FirePHP
		FB::setEnabled(self::config('core.debug'));
				
		# Recache is off by default
		$recache = false;
		
		# Set the user agent
		self::$user_agent = ( ! empty($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');
		
		# Set timezone
		date_default_timezone_set('UTC');
		
		# Define Scaffold error constant
		define('E_SCAFFOLD', 42);
		
		# Set error handler
		set_error_handler(array('CSScaffold', 'exception_handler'));
		
		# Set exception handler
		set_exception_handler(array('CSScaffold', 'exception_handler'));
		
		if(!isset($url_params['request']))
			throw new Scaffold_Exception('core.no_file_requested');
		
		# Get rid of those pesky slashes
		$requested_file	= trim_slashes($url_params['request']);
		
		# Remove anything about .css - like /typography/
		$requested_file = preg_replace('/\.css(.*)$/', '.css', $requested_file);
		
		# Remove the start of the url if it exists (http://www.example.com)
		$requested_file = preg_replace('/https?\:\/\/[^\/]+/i', '', $requested_file);
		
		# Add our requested file var to the array
		$request['file'] = $requested_file;
		
		# Path to the file, relative to the css directory
		$request['relative_file'] = ltrim(str_replace(CSSURL, '/', $requested_file), '/');

		# Path to the directory containing the file, relative to the css directory		
		$request['relative_dir'] = pathinfo($request['relative_file'], PATHINFO_DIRNAME);
		
		# Find the server path to the requested file
		if(file_exists(DOCROOT.$requested_file))
		{
			# The request is sent with the absolute path most of the time
			$request['path'] = DOCROOT.$requested_file;
		}
		else
		{
			# Otherwise we'll try to find it inside the CSS directory
			$request['path'] = self::find_file($request['relative_dir'] . '/', basename($requested_file, '.css'), FALSE, 'css');
		}

		# If they've put a param in the url, consider it set to 'true'
		foreach($url_params as $key => $value)
		{
			if(!in_array($key, self::$system_url_params))
			{
				if($value == "")
				{
					self::config_set('core.options.'.$key, true);
				}
				else
				{
					self::config_set('core.options.'.$key, $value);
				}
			}
		}
		
		# If the file doesn't exist
		if(!file_exists($request['path']))
			throw new Scaffold_Exception("core.doesnt_exist", $request['file']); 

		# or if it's not a css file
		if (!is_css($requested_file))
			throw new Scaffold_Exception("core.not_css", $requested_file);
		
		# or if the requested file wasn't from the css directory
		if(!substr(pathinfo($request['path'], PATHINFO_DIRNAME), 0, strlen(CSSPATH)))
			throw new Scaffold_Exception("core.outside_css_directory");
		
		# Make sure the files/folders are writeable
		if (!is_dir(CACHEPATH) || !is_writable(CACHEPATH))
			throw new Scaffold_Exception("core.missing_cache", CACHEPATH);
		
		# Send it off to the config
		self::config_set('core.request',$request);
					
		# Get the modified time of the CSS file
		self::config_set('core.request.mod_time', filemtime(self::config('core.request.path')));
			
		# Set the recache to true if needed		
		if(self::config('core.always_recache') OR isset($url_params['recache']))
			$recache = true;
		
		# Set it back to false if it's locked
		if(self::config('core.cache_lock') === true || IN_PRODUCTION)
			$recache = false;

		# Load the modules.
		self::load_addons(self::$modules, 'modules');
		
		# Load the plugins
		$plugins = self::config('core.plugins');
		
		# If the plugin is disabled, remove it.
		foreach($plugins as $key => $value)
		{
			if($value !== false)
			{
				self::$plugins[] = $key;
			}
		}

		# Now we can load them
		self::load_addons(self::$plugins, PLUGINS);
		
		# Prepare the cache, and tell it if we want to recache
		self::cache_set($recache);
		
		# Work in the same directory as the requested CSS file
		chdir(dirname($request['path']));
		
		# Load the CSS file into the object
		CSS::load(file_get_contents(self::config('core.request.path')));
		
		# Setup is complete, prevent it from being run again
		$run = TRUE;
	}
	
	/**
	 * Displays nice backtrace information.
	 * @see http://php.net/debug_backtrace
	 *
	 * @param   array   backtrace generated by an exception or debug_backtrace
	 * @return  string
	 */
	public static function backtrace($trace)
	{
		if ( ! is_array($trace))
			return;

		// Final output
		$output = array();

		foreach ($trace as $entry)
		{
			$temp = '<li>';
			$temp .= '<pre>';
			
			if (isset($entry['file']))
			{
				$temp .= self::lang('core.error_file_line', preg_replace('!^'.preg_quote(DOCROOT).'!', '', $entry['file']), $entry['line']);
			}

			if (isset($entry['class']))
			{
				// Add class and call type
				$temp .= $entry['class'].$entry['type'];
			}

			// Add function
			$temp .= $entry['function'].'( ';

			// Add function args
			if (isset($entry['args']) AND is_array($entry['args']))
			{
				// Separator starts as nothing
				$sep = '';

				while ($arg = array_shift($entry['args']))
				{
					if (is_string($arg) AND is_file($arg))
					{
						// Remove docroot from filename
						$arg = preg_replace('!^'.preg_quote(DOCROOT).'!', '', $arg);
					}

					$temp .= $sep.htmlspecialchars((string)$arg, ENT_QUOTES, 'UTF-8');

					// Change separator to a comma
					$sep = ', ';
				}
			}

			$temp .= ' )</pre></li>';

			$output[] = $temp;
		}

		return '<ul class="backtrace">'.implode("\n", $output).'</ul>';
	}
	
	/**
	 * Empty the entire cache, removing every cached css file.
	 *
	 * @return void
	 * @author Anthony Short
	 */
	private static function cache_clear($path = CACHEPATH)
	{
		$path .= '/';

		foreach(scandir($path) as $file)
		{
			if($file[0] == ".")
			{
				continue;
			}
			elseif(is_dir($path.$file))
			{
				self::cache_clear($path.$file);
				rmdir($path.$file);
			}
			elseif(file_exists($path.$file))
			{
				unlink($path.$file);
			}
		}
	}
	
	/**
	 * Set the cache file which will be used for this process
	 *
	 * @return boolean
	 * @author Anthony Short
	 */
	private static function cache_set($recache = FALSE)
	{
		$checksum = "";
		$cached_mod_time = 0;
		
		if(self::$flags != null)
		{
			$checksum = "-" . implode("_", array_keys(self::$flags));
		}

		# Determine the name of the cache file
		self::$cached_file = join_path(CACHEPATH,preg_replace('#(.+)(\.css)$#i', "$1{$checksum}$2", self::config('core.request.relative_file')));

		# Check to see if we should delete the cache file
		if($recache === true && file_exists(self::$cached_file))
		{
			# Empty out the cache
			self::cache_clear();
		}
		elseif(file_exists(self::$cached_file))
		{
			# When was the cache last modified
			$cached_mod_time =  (int) filemtime(self::$cached_file);
		}
		
		self::config_set('core.cache.mod_time', $cached_mod_time);
	}

	/**
	 * Write to the set cache
	 *
	 * @return void
	 * @author Anthony Short
	 */
	private static function cache_write($data)
	{	   	
	   	# Make sure the cache exists
		$cache_info = pathinfo(self::$cached_file);
		
		# Make the cache mimic the css directory
		if ($cache_info['dirname'] . "/" != CACHEPATH)
		{
			$path = CACHEPATH;
			$dirs = explode('/', self::config('core.request.relative_dir'));
						
			foreach ($dirs as $dir)
			{
				$path = join_path($path, $dir);
				
				if (!is_dir($path)) { mkdir($path, 0777); }
			}
		}	
		
		# Put it in the cache
		file_put_contents(self::$cached_file, $data, 0777);
		
		# Set its properties
		chmod(self::$cached_file, 0777);
		touch(self::$cached_file, time());
		
		# Set the config file mod time
		self::config_set('core.cache.mod_time', time());
	}

	/**
	 * Get a config item or group.
	 *
	 * @param   string   item name
	 * @param   boolean  force a forward slash (/) at the end of the item
	 * @param   boolean  is the item required?
	 * @return  mixed
	 */
	public static function config($key, $slash = FALSE, $required = FALSE)
	{
		if (self::$configuration === NULL)
		{
			// Load core configuration
			self::$configuration['core'] = self::config_load('core');
			
			// Re-parse the include paths
			self::include_paths(TRUE);
		}

		// Get the group name from the key
		$group = explode('.', $key, 2);
		$group = $group[0];

		if ( ! isset(self::$configuration[$group]))
		{
			// Load the configuration group
			self::$configuration[$group] = self::config_load($group, $required);
		}

		// Get the value of the key string
		$value = self::key_string(self::$configuration, $key);

		if ($slash === TRUE AND is_string($value) AND $value !== '')
		{
			// Force the value to end with "/"
			$value = rtrim($value, '/').'/';
		}

		return $value;
	}

	/**
	 * Clears a config group from the cached configuration.
	 *
	 * @param   string  config group
	 * @return  void
	 */
	public static function config_clear($group)
	{
		// Remove the group from config
		unset(self::$configuration[$group], self::$internal_cache['configuration'][$group]);
	}

	/**
	 * Load a config file.
	 *
	 * @param   string   config filename, without extension
	 * @param   boolean  is the file required?
	 * @return  array
	 */
	public static function config_load($name, $required = TRUE)
	{
		if ($name === 'core')
		{
			// Load the application configuration file
			require CONFIG;

			if ( ! isset($config['cache_lock']))
			{
				// Invalid config file
				die('Your configuration file is not valid.');
			}

			return $config;
		}

		if (isset(self::$internal_cache['configuration'][$name]))
			return self::$internal_cache['configuration'][$name];

		// Load matching configs
		$configuration = array();

		if ($files = self::find_file('config', $name, $required))
		{
			foreach ($files as $file)
			{
				require $file;

				if (isset($config) AND is_array($config))
				{
					// Merge in configuration
					$configuration = array_merge($configuration, $config);
				}
			}
		}

		return self::$internal_cache['configuration'][$name] = $configuration;
	}
	
	/**
	 * Sets a configuration item, if allowed.
	 *
	 * @param   string   config key string
	 * @param   string   config value
	 * @return  boolean
	 */
	public static function config_set($key, $value)
	{
		// Do this to make sure that the config array is already loaded
		self::config($key);

		// Convert dot-noted key string to an array
		$keys = explode('.', $key);

		// Used for recursion
		$conf =& self::$configuration;
		$last = count($keys) - 1;

		foreach ($keys as $i => $k)
		{
			if ($i === $last)
			{
				$conf[$k] = $value;
			}
			else
			{
				$conf =& $conf[$k];
			}
		}
		
		if ($key === 'core.modules' OR $key === 'core.plugins')
		{
			// Reprocess the include paths
			self::include_paths(TRUE);
		}

		return TRUE;
	}

	/**
	 * Find a resource file in a given directory. Files will be located according
	 * to the order of the include paths. Configuration and i18n files will be
	 * returned in reverse order.
	 *
	 * @throws  Kohana_Exception  if file is required and not found
	 * @param   string   directory to search in
	 * @param   string   filename to look for (without extension)
	 * @param   boolean  file required
	 * @param   string   file extension
	 * @return  array    if the type is config, i18n or l10n
	 * @return  string   if the file is found
	 * @return  FALSE    if the file is not found
	 */
	public static function find_file($directory, $filename, $required = FALSE, $ext = FALSE)
	{
		// NOTE: This test MUST be not be a strict comparison (===), or empty
		// extensions will be allowed!
		if ($ext == '')
		{
			// Use the default extension
			$ext = EXT;
		}
		else
		{
			// Add a period before the extension
			$ext = '.'.$ext;
		}

		// Search path
		$search = $directory.'/'.$filename.$ext;
		
		if (isset(self::$internal_cache['find_file_paths'][$search]))
			return self::$internal_cache['find_file_paths'][$search];

		// Load include paths
		$paths = self::$include_paths;

		// Nothing found, yet
		$found = NULL;

		if ($directory === 'config' OR $directory === 'language')
		{
			// Search in reverse, for merging
			$paths = array_reverse($paths);

			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found[] = $path.$search;
				}
			}
		}
		elseif(in_array($directory, $paths))
		{
			if (is_file($directory.$filename.$ext))
			{
				// A matching file has been found
				$found = $path.$search;

				// Stop searching
				break;
			}
		}
		else
		{
			foreach ($paths as $path)
			{
				if (is_file($path.$search))
				{
					// A matching file has been found
					$found = $path.$search;

					// Stop searching
					break;
				}
			}
		}

		if ($found === NULL)
		{
			if ($required === TRUE)
			{
				// If the file is required, throw an exception
				throw new Scaffold_Exception('core.resource_not_found', $directory . $filename . $ext);
			}
			else
			{
				// Nothing was found, return FALSE
				$found = FALSE;
			}
		}

		return self::$internal_cache['find_file_paths'][$search] = $found;
	}

	/**
	 * Sets a cache flag
	 *
	 * @author Anthony Short
	 * @param $flag_name
	 * @return null
	 */
	public static function flag($flag_name)
	{
		self::$flags[$flag_name] = true;
	}

	/**
	 * Get all include paths. APPPATH is the first path, followed by module
	 * paths in the order they are configured, follow by the SYSPATH.
	 *
	 * @param   boolean  re-process the include paths
	 * @return  array
	 */
	public static function include_paths($process = FALSE)
	{
		if ($process === TRUE)
		{
			// Add APPPATH as the first path
			self::$include_paths = array
			(
				CSSPATH,
				SYSPATH . 'modules',
				PLUGINS,
			);
			
			# Find the modules and plugins installed	
			$modules = self::list_files('modules', FALSE, SYSPATH . 'modules');
			$plugins = self::list_files('plugins', FALSE, PLUGINS);
			
			foreach (array_merge($plugins,$modules) as $path)
			{
				$path = str_replace('\\', '/', realpath($path));
				
				if (is_dir($path))
				{
					// Add a valid path
					self::$include_paths[] = $path.'/';
				}
			}

			// Add SYSPATH as the last path
			self::$include_paths[] = SYSPATH;
			self::$include_paths[] = SCAFFOLD;
		}

		return self::$include_paths;
	}

	/**
	 * Returns the value of a key, defined by a 'dot-noted' string, from an array.
	 *
	 * @param   array   array to search
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  string  if the key is found
	 * @return  void    if the key is not found
	 */
	public static function key_string($array, $keys)
	{
		if (empty($array))
			return NULL;

		// Prepare for loop
		$keys = explode('.', $keys);

		do 
		{
			// Get the next key
			$key = array_shift($keys);

			if (isset($array[$key]))
			{
				if (is_array($array[$key]) AND ! empty($keys))
				{
					// Dig down to prepare the next loop
					$array = $array[$key];
				}
				else
				{
					// Requested key was found
					return $array[$key];
				}
			}
			else
			{
				// Requested key is not set
				break;
			}
		}
		while ( ! empty($keys));

		return NULL;
	}

	/**
	 * Sets values in an array by using a 'dot-noted' string.
	 *
	 * @param   array   array to set keys in (reference)
	 * @param   string  dot-noted string: foo.bar.baz
	 * @return  mixed   fill value for the key
	 * @return  void
	 */
	public static function key_string_set( & $array, $keys, $fill = NULL)
	{
		if (is_object($array) AND ($array instanceof ArrayObject))
		{
			// Copy the array
			$array_copy = $array->getArrayCopy();

			// Is an object
			$array_object = TRUE;
		}
		else
		{
			if ( ! is_array($array))
			{
				// Must always be an array
				$array = (array) $array;
			}

			// Copy is a reference to the array
			$array_copy =& $array;
		}

		if (empty($keys))
			return $array;

		// Create keys
		$keys = explode('.', $keys);

		// Create reference to the array
		$row =& $array_copy;

		for ($i = 0, $end = count($keys) - 1; $i <= $end; $i++)
		{
			// Get the current key
			$key = $keys[$i];

			if ( ! isset($row[$key]))
			{
				if (isset($keys[$i + 1]))
				{
					// Make the value an array
					$row[$key] = array();
				}
				else
				{
					// Add the fill key
					$row[$key] = $fill;
				}
			}
			elseif (isset($keys[$i + 1]))
			{
				// Make the value an array
				$row[$key] = (array) $row[$key];
			}

			// Go down a level, creating a new row reference
			$row =& $row[$key];
		}

		if (isset($array_object))
		{
			// Swap the array back in
			$array->exchangeArray($array_copy);
		}
	}
	
	/**
	 * Fetch a language item.
	 *
	 * @param   string  language key to fetch
	 * @param   array   additional information to insert into the line
	 * @return  string  i18n language string, or the requested key if the i18n item is not found
	 */
	public static function lang($key, $args = NULL)
	{
		# Extract the main group from the key
		$keys = explode('.', $key, 2);
		$group = $keys[0];

		// Get locale name
		$locale = self::config('core.language');

		if (!isset(self::$internal_cache['language'][$locale][$group]))
		{
			// Messages for this group
			$messages = array();

			if ($files = self::find_file("language", "$locale/$group"))
			{
				foreach ($files as $file)
				{
					include $file;

					// Merge in configuration
					if ( ! empty($lang) AND is_array($lang))
					{
						foreach ($lang as $k => $v)
						{
							$messages[$k] = $v;
						}
					}
				}
			}
					
			self::$internal_cache['language'][$locale][$group] = $messages;
		}
	
		if(isset($keys[1]))
		{
			# Get the line from cache
			$line = self::$internal_cache['language'][$locale][$group][$keys[1]];
		}
		else
		{
			$line = self::$internal_cache['language'][$locale][$group];
		}

		if ($line === NULL)
		{
			# Return the key string as fallback
			return $key;
		}
		
		# Add extra text to the message
		if (is_string($line) AND func_num_args() > 1)
		{
			$args = array_slice(func_get_args(), 1);

			# Add the arguments into the line
			$line = vsprintf($line, is_array($args[0]) ? $args[0] : $args);
		}

		return $line;
	}

	/**
	 * Lists all files and directories in a resource path.
	 *
	 * @param   string   directory to search
	 * @param   boolean  list all files to the maximum depth?
	 * @param   string   full path to search (used for recursion, *never* set this manually)
	 * @return  array    filenames and directories
	 */
	public static function list_files($directory, $recursive = FALSE, $path = FALSE)
	{
		$files = array();

		if ($path === FALSE)
		{
			$paths = array_reverse(self::include_paths());

			foreach ($paths as $path)
			{
				// Recursively get and merge all files
				$files = array_merge($files, self::list_files($directory, $recursive, $path.$directory));
			}
		}
		else
		{
			$path = rtrim($path, '/').'/';

			if (is_readable($path))
			{
				$items = (array) glob($path.'*');
				
				if ( ! empty($items))
				{
					foreach ($items as $index => $item)
					{
						$name = pathinfo($item, PATHINFO_BASENAME);
						
						if(substr($name, 0, 1) == '.' || substr($name, 0, 1) == '-')
						{
							continue;
						}
						
						$files[] = $item = str_replace('\\', '/', $item);

						// Handle recursion
						if (is_dir($item) AND $recursive == TRUE)
						{
							// Filename should only be the basename
							$item = pathinfo($item, PATHINFO_BASENAME);

							// Append sub-directory search
							$files = array_merge($files, self::list_files($directory, TRUE, $path.$item));
						}
					}
				}
			}
		}

		return $files;
	}

	/**
	 * Loads modules and plugins
	 *
	 * @param $addons An array of addon names
	 * @param $directory The directory to look for these addons in
	 * @return void
	 */
	private static function load_addons($addons, $directory)
	{
		foreach($addons as $addon)
		{
			# The addon folder
			$folder = realpath(join_path($directory, $addon));
					
			# The controller for the plugin (Optional)
			$controller = join_path($folder,$addon.EXT);

			# The config file for the plugin (Optional)
			$config_file = $folder.'/config.php';
			
			# Set the paths in the config
			self::config_set("$addon.support", join_path($folder,'support'));
			self::config_set("$addon.libraries", join_path($folder,'libraries'));

			# Include the addon controller
			if(file_exists($controller))
			{
				require_once($controller);
				call_user_func(array($addon,'flag'));
			}
			
			# If there is a config file
			if(file_exists($config_file))
			{
				include $config_file;
				
				foreach($config as $key => $value)
				{
					self::config_set($addon.'.'.$key, $value);
				}
				
				unset($config);
			}
		}
	}
	
	/**
	 * Loads a view file and returns it
	 */
	public static function load_view($view)
	{
		if ($view == '')
				return;
		
		# Find the view file
		$view = self::find_file('views/', $view, true);
	
		# Buffering on
		ob_start();
	
		# Views are straight HTML pages with embedded PHP, so importing them
		# this way insures that $this can be accessed as if the user was in
		# the controller, which gives the easiest access to libraries in views
		try
		{
			include $view;
		}
		catch (Exception $e)
		{
			ob_end_clean();
			throw $e;
		}
	
		# Fetch the output and close the buffer
		return ob_get_clean();
	}

	/**
	 * Output the CSS to the browser
	 *
	 * @return void
	 * @author Anthony Short
	 */
	public static function output_css()
	{	
		if (
			isset($_SERVER['HTTP_IF_MODIFIED_SINCE'], $_SERVER['SERVER_PROTOCOL']) && 
			self::config('core.cache.mod_time') <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
		)
		{
			header("{$_SERVER['SERVER_PROTOCOL']} 304 Not Modified");
			exit;
		}
		else
		{
			# Set the default headers
			header('Content-Type: text/css');
			header("Vary: User-Agent, Accept");
			header('Last-Modified: '. gmdate('D, d M Y H:i:s', self::config('core.cache.mod_time')) .' GMT');

			echo file_get_contents(self::$cached_file);
			exit;
		}
	}
	
	/**
	 * Parse the CSS
	 *
	 * @return string - The processes css file as a string
	 * @author Anthony Short
	 **/
	public static function parse_css()
	{						
		# If the cache is stale or doesn't exist
		if (self::config('core.cache.mod_time') <= self::config('core.request.mod_time'))
		{			
			# Start the timer
			Benchmark::start("parse_css");
			
			# Compress it before parsing
			CSS::compress(CSS::$css);
						
			# Import CSS files
			Import::parse();
			
			if(self::config('core.auto_include_mixins') === true)
			{
				# Import the mixins in the plugin/module folders
				Mixins::import_mixins('framework/mixins');
			}
														
			# Parse our css through the plugins
			foreach(self::$plugins as $plugin)
			{
				call_user_func(array($plugin,'import_process'));
			}
			
			# Compress it before parsing
			CSS::compress(CSS::$css);

			# Parse the constants
			Constants::parse();

			foreach(self::$plugins as $plugin)
			{
				call_user_func(array($plugin,'pre_process'));
			}
			
			# Parse the @grid
			Layout::parse_grid();
			
			# Replace the constants
			Constants::replace();
			
			# Parse @for loops
			Iteration::parse();
			
			foreach(self::$plugins as $plugin)
			{
				call_user_func(array($plugin,'process'));
			}
			
			# Compress it before parsing
			CSS::compress(CSS::$css);
			
			# Parse the mixins
			Mixins::parse();
			
			# Find missing constants
			Constants::replace();
			
			# Compress it before parsing
			CSS::compress(CSS::$css);
			
			foreach(self::$plugins as $plugin)
			{
				call_user_func(array($plugin,'post_process'));
			}
			
			# Parse the expressions
			Expression::parse();
			
			# Parse the nested selectors
			NestedSelectors::parse();
			
			# Convert all url()'s to absolute paths if required
			if(self::config('core.absolute_urls') === true)
			{
				CSS::convert_to_absolute_urls();
			}
			
			# Replaces url()'s that start with ~ to lead to the CSS directory
			CSS::replace_css_urls();
			
			# Add the extra string we've been storing
			CSS::$css .= CSS::$append;
			
			# If they want to minify it
			if(self::config('core.minify_css') === true)
			{
				Minify::compress();
			}
			
			# Otherwise, we'll make it pretty
			else
			{
				CSS::pretty();
			}
			
			# Formatting hook
			foreach(self::$plugins as $plugin)
			{
				call_user_func(array($plugin,'formatting_process'));
			}
			
			# Validate the CSS
			Validate::check();
			
			# Stop the timer...
			Benchmark::stop("parse_css");
			
			if (self::config('core.show_header') === TRUE)
			{		
				CSS::$css  = "/* Processed by CSScaffold on ". gmdate('r') . " in ".Benchmark::get("parse_css", "time")." seconds */\n\n" . CSS::$css;
			}

			# Write the css file to the cache
			self::cache_write(CSS::$css);
			
			# Output process hook for plugins to display views.
			# Doesn't run in production mode.
			if(!IN_PRODUCTION)
			{				
				foreach(array_merge(self::$plugins, self::$modules) as $plugin)
				{
					call_user_func(array($plugin,'output'));
				}
			}
		} 
	}

	/**
	 * Handles Exceptions
	 *
	 * @param   integer|object  exception object or error code
	 * @param   string          error message
	 * @param   string          filename
	 * @param   integer         line number
	 * @return  void
	 */
	public static function exception_handler($exception, $message = NULL, $file = NULL, $line = NULL)
	{
		try
		{
			# PHP errors have 5 args, always
			$PHP_ERROR = (func_num_args() === 5);
	
			# Test to see if errors should be displayed
			if (IN_PRODUCTION OR ($PHP_ERROR AND error_reporting() === 0))
				die;
				
			# Error handling will use exactly 5 args, every time
			if ($PHP_ERROR)
			{
				$code     = $exception;
				$type     = 'PHP Error';
			}
			else
			{
				$code     = $exception->getCode();
				$type     = get_class($exception);
				$message  = $exception->getMessage();
				$file     = $exception->getFile();
				$line     = $exception->getLine();
			}

			if(is_numeric($code))
			{
				$codes = self::lang('errors');
	
				if (!empty($codes[$code]))
				{
					list($level, $error, $description) = $codes[$code];
				}
				else
				{
					$level = 1;
					$error = $PHP_ERROR ? 'Unknown Error' : get_class($exception);
					$description = '';
				}
			}
			else
			{
				// Custom error message, this will never be logged
				$level = 5;
				$error = $code;
				$description = '';
			}
			
			// Remove the DOCROOT from the path, as a security precaution
			$file = str_replace('\\', '/', realpath($file));
			$file = preg_replace('|^'.preg_quote(DOCROOT).'|', '', $file);

			if($PHP_ERROR)
			{
				$description = 'An error has occurred which has stopped Scaffold';
	
				if (!headers_sent())
				{
					# Send the 500 header
					header('HTTP/1.1 500 Internal Server Error');
				}
			}
			else
			{
				if (method_exists($exception, 'sendHeaders') AND !headers_sent())
				{
					# Send the headers if they have not already been sent
					$exception->sendHeaders();
				}
			}
			
			if ($line != FALSE)
			{
				// Remove the first entry of debug_backtrace(), it is the exception_handler call
				$trace = $PHP_ERROR ? array_slice(debug_backtrace(), 1) : $exception->getTrace();

				// Beautify backtrace
				$trace = self::backtrace($trace);
				
			}
			
			# Log to FirePHP
			FB::log($error . "-" . $message);
			
			require(SYSPATH . '/views/scaffold_error_page.php');

			# Turn off error reporting
			error_reporting(0);
			exit;
		}
		catch(Exception $e)
		{
			die('Fatal Error: '.$e->getMessage().' File: '.$e->getFile().' Line: '.$e->getLine());
		}
	}

}

/**
 * Creates a generic exception.
 */
class Scaffold_Exception extends Exception 
{
	# Header
	protected $header = FALSE;

	# Scaffold error code
	protected $code = E_SCAFFOLD;

	/**
	 * Set exception message.
	 *
	 * @param  string  i18n language key for the message
	 * @param  array   addition line parameters
	 */
	public function __construct($error)
	{
		$args = array_slice(func_get_args(), 1);

		# Fetch the error message
		$message = CSScaffold::lang($error, $args);
		
		if ($message === $error OR empty($message))
		{
			# Unable to locate the message for the error
			$message = 'Unknown Exception: '.$error;
		}

		# Sets $this->message the proper way
		parent::__construct($message);
	}

	/**
	 * Magic method for converting an object to a string.
	 *
	 * @return  string  i18n message
	 */
	public function __toString()
	{
		return (string) $this->message;
	}

	/**
	 * Sends an Internal Server Error header.
	 *
	 * @return  void
	 */
	public function sendHeaders()
	{
		// Send the 500 header
		header('HTTP/1.1 500 Internal Server Error');
	}
}

/**
 * Scaffold_User_Exception
 *
 * Captures errors and displays them.
 * 
 * @author Anthony Short
 */
class Scaffold_User_Exception extends Scaffold_Exception
{
	/**
	 * Set exception message.
	 *
	 * @param $title string The name of the exception
	 * @param  array   addition line parameters
	 */
	public function __construct($title, $message)
	{
		Exception::__construct($message);

		$this->code = $title;
	}
}