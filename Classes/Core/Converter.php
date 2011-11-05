<?php

namespace Admin\Core;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

class Converter {
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;

	/**
	 * @var TYPO3\FLOW3\Cache\CacheManager
	 * @FLOW3\Inject
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
				if (!$conversionResult instanceof \TYPO3\FLOW3\Error\Error)
					return $conversionResult;
			}
		}
		
		if(\Admin\Core\Helper::isIteratable($mixed)){
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
			foreach($this->reflectionService->getAllImplementationClassNamesForInterface('Admin\Converter\ConverterInterface') as $objectConverterClassName) {
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