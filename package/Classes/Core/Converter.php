<?php

namespace F3\Admin\Core;

class Converter {
    /**
	 * @var \F3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \F3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $reflectionService;

    /**
	 * @var F3\FLOW3\Cache\CacheManager
	 * @inject
	 */
	protected $cacheManager;
    
    public function __construct(){
        
    }

    public function getType($value){
        if(is_object($value))
            return get_class($value);

        return gettype($value);
    }

    public function toString($mixed, $conf = array()){
        if(is_object($mixed))
            if(is_callable(array($mixed,"__toString")))
                return $mixed->__toString();
        $objectConverters = $this->getObjectConverter();
        $type = $this->getType($mixed);

        if(isset($objectConverters[$type])){
            if(is_callable(array($objectConverters[$type],"toString"))){
                $conversionResult = $objectConverters[$type]->toString($mixed, $conf);
                if (!$conversionResult instanceof \F3\FLOW3\Error\Error)
                    return $conversionResult;
            }
        }
        
        if(\F3\Admin\Core\Helper::isIteratable($mixed)){
            $strings = array();
            foreach($mixed as $value){
                $string = $this->toString($value);
                if(!empty($string))
                    $strings[] = $this->toString($value);
            }
            if(!empty($strings))
                return implode(", ",$strings);
        }


        if(in_array(gettype($mixed),explode(",","string,integer,float,double,boolean")))
            return strval($mixed);

        return "";
    }

    protected function getObjectConverter(){
        $cache = $this->cacheManager->getCache('Admin_ImplementationCache');

        $identifier = "ConverterInterface";
        if(!$cache->has($identifier)){
            $objectConverters = array();
            foreach($this->reflectionService->getAllImplementationClassNamesForInterface('F3\Admin\Converter\ConverterInterface') as $objectConverterClassName) {
                $objectConverter = $this->objectManager->get($objectConverterClassName);
                foreach ($this->objectManager->get($objectConverterClassName)->getSupportedTypes() as $supportedType) {
                    $objectConverters[$supportedType] = $objectConverterClassName;
                }
            }

            $cache->set($identifier,$objectConverters);
        }else{
            $objectConverters = $cache->get($identifier);
        }

        foreach($objectConverters as $supportedType => $objectConverterClassName){
            $objectConverters[$supportedType] = $this->objectManager->get($objectConverterClassName);
        }
        
        return $objectConverters;
    }
}

?>