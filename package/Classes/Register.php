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