<?php

namespace F3\Admin;

class Register {
	/**
	 * @var array
	 */	
	static protected $container = array();
	
	static function set($name,$mixed){
        self::$container[$name] = $mixed;
    }
    
	static function get($name){
        return self::$container[$name];
    }
	
	static function has($name){
		return array_key_exists($name,self::$container);
	}
}

?>