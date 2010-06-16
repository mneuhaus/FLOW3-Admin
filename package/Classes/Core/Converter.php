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
        if(!\F3\Admin\Register::has("objectConverters")){
            $objectConverters = array();
            foreach($this->reflectionService->getAllImplementationClassNamesForInterface('F3\FLOW3\Property\ObjectConverterInterface') as $objectConverterClassName) {
                $objectConverter = $this->objectManager->get($objectConverterClassName);
                foreach ($this->objectManager->get($objectConverterClassName)->getSupportedTypes() as $supportedType) {
                    $objectConverters[$supportedType] = $objectConverter;
                }
                \F3\Admin\Register::set("objectConverters",$objectConverters);
            }
        } else {
            $objectConverters = \F3\Admin\Register::get("objectConverters");
        }
        return $objectConverters;
    }
}

?>