<?php
/*                                                                        *
 * This script belongs to the FLOW3 package "Admin".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

namespace Admin\Core;

/**
 * This is a global register for some variables like package, being, ...
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class Register {
	static protected $container = array();

	static function set($name,$mixed){
		self::$container[$name] = $mixed;
	}

	static function get($name, $key = null){
		if(!is_null($key) && isset(self::$container[$name]) && is_array(self::$container[$name]))
			return isset(self::$container[$name][$key]) ? self::$container[$name][$key] : null;
		else
			return isset(self::$container[$name]) ? self::$container[$name] : null;
	}

	static function has($name){
		return array_key_exists($name,self::$container);
	}

	static function add($name,$key = null,$value = null){
		if(isset(self::$container[$name]) && !is_array(self::$container[$name])){
			self::$container[$name] = array();
		}
		if($key === null)
			self::$container[$name][] = $value;
		else
			self::$container[$name][$key] = $value;
	}

	static function remove($name){
		unset(self::$container[$name]);
	}
}

?>