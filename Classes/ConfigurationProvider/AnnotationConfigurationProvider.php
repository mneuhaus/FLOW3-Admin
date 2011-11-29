<?php

namespace Admin\ConfigurationProvider;

/* *
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

/**
 * ConfigurationProvider based on the FLOW3 Model Annotations
 *
 * @version $Id: ReflectionConfigurationProvider.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class AnnotationConfigurationProvider extends \Admin\Core\ConfigurationProvider\AbstractConfigurationProvider {
	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;
	
	public function get($class){
		$c = array();
		
		if( class_exists($class) ) {
			$c = array();
			
			// Loac Class Annotations
			foreach ($this->reflectionService->getClassAnnotations($class) as $key => $object) {
				$shortName = $this->convertAnnotationName(get_class($object));
				if(!isset($c[$shortName])) $c[$shortName] = array();
				$c[$shortName][] = $object;
			}
			
			$schema = $this->reflectionService->getClassSchema($class);
			if(is_object($schema)){
				if(!isset($c["repository"]))
					$c["repository"] = array(new \Admin\Annotations\Repository(array("class" => $schema->getRepositoryClassName())));
				$properties = $schema->getProperties();
			}else{
				$properties = array_flip($this->reflectionService->getClassPropertyNames($class));
			}
			foreach($properties as $property => $meta){
				if($property == "FLOW3_Persistence_Identifier") continue;
				$c["properties"][$property] = array();
				
					
				// Load legacy Annotations like @var,...
				foreach ($this->reflectionService->getPropertyTagsValues($class, $property) as $shortName => $tags) {
					if(!isset($c["properties"][$property])) $c["properties"][$property] = array();
					
					$c["properties"][$property][$shortName] = $tags;
				}
				
				
				// $c["properties"][$property]["type"] = array(
				// 	new \Admin\Annotations\Type(array(
				// 		"name" => $meta["type"],
				// 		"subtype" => $meta["elementType"],
				// 	))
				// );
				
				
				// Load Annotations and override legacy Annotations
				$annotations = $this->reflectionService->getPropertyAnnotations($class, $property);
				foreach ($annotations as $key => $objects) {
					$shortName = $this->convertAnnotationName($key);
					if(!isset($c["properties"][$property])) $c["properties"][$property] = array();
				
					$c["properties"][$property][$shortName] = $objects;
				}
			};
		}
		
		return $c;
	}
	
	public function convertAnnotationName($annotation){
		$name = $this->helper->getShortName($annotation);
		$name = lcfirst($name);
 		return $name;
	}
}
?>