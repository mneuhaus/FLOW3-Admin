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
class ReflectionConfigurationProvider extends \Admin\Core\ConfigurationProvider\AbstractConfigurationProvider {
	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;

	public function get($being){
		$configuration = array();
		
		if( class_exists($being, false) ) {
			$configuration = array(
				"class" => $this->reflectionService->getClassTagsValues($being),
				"properties" => $this->helper->getModelProperties($being)
			);
			$annotations = array();
			foreach ($this->reflectionService->getClassAnnotations($being) as $key => $object) {
				$class = get_class($object);
				if(!stristr($class, "Admin\Annotations\\")) continue;
				$annotation = strtolower(str_replace("Admin\Annotations\\", "", $class));
				if(!isset($annotations[$annotation])) $annotations[$annotation] = array();
				$annotations[$annotation][] = get_object_vars($object);
			}
			$configuration["class"]["annotations"] = $annotations;
			
			foreach($configuration["properties"] as $property => $conf) {
				// Injected or ignored Properties shouldn't be managed
				if( array_key_exists("inject", $conf) || array_key_exists("ignore", $conf) )
					$configuration["properties"][$property]["ignore"] = true;
				
				foreach($conf as $key => $value){
					unset($configuration["properties"][$property][$key]);
					$key = str_replace("admin\annotations\\", "", $key);
					$configuration["properties"][$property][$key] = $value;
					
					if(is_array($value) && empty($value)){
						$configuration["properties"][$property][$key] = true;
					}
					
					if(is_array($value) && count($value) == 1){
						while(is_array($value))
							$value = current($value);
						$configuration["properties"][$property][$key] = $value;
					}
				}
			}
		}
		return $configuration;
	}
}
?>