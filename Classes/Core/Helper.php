<?php

namespace F3\Admin\Core;

class Helper {
    /**
     * Checks if the Variable is iteratable
     *
     * @param mixed $mixed $variable to check
     * @return boolean
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	static function isIteratable($mixed){
        /*
        \F3\var_dump(array(
            "mixed" => $mixed,
            "is_array" => is_array($mixed),
            "is_object"=> is_object($mixed),
            "ArrayAccess"=> $mixed instanceof \ArrayAccess,
            "SplObjectStorage" => $mixed instanceof \SplObjectStorage,
            "Iterator" => $mixed instanceof \Iterator
        ));
        */

        if(is_array($mixed))
            return true;

        if(is_object($mixed)){
            
            if($mixed instanceof \ArrayAccess)
                return true;

            if($mixed instanceof \SplObjectStorage)
                return true;

            if($mixed instanceof \Iterator)
                return true;
            
            if($mixed instanceof \Doctrine\ODM\MongoDB\PersistentCollection)
                return true;

            if($mixed instanceof \Doctrine\ODM\MongoDB\MongoCursor)
                return true;
        }
        
        return false;
    }

    static function getShortName($class){
        if(is_object($class))
            $class = get_class($class);

        $reflection = new \ReflectionClass($class);
        
        return $reflection->getShortName();
    }

	/**
	 * Returns the Repository for a Model based on the Class name
	 *
	 * @param $model String Name of the Model
	 * @return $repository String Repository Name
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 **/
    static public function getModelRepository($model){
		$repository = str_replace("Domain\Model","Domain\Repository",$model) . "Repository";
		if(substr($repository,0,1) == "\\"){
			$repository = substr($repository,1);
		}
		return $repository;
	}
}

?>