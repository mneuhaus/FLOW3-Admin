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

/**
 * Configurationprovider for the DummyAdapter
 *
 * @version $Id: YamlConfigurationProvider.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class YamlConfigurationProvider extends \Admin\Core\ConfigurationProvider\AbstractConfigurationProvider {
	
	public function get($being){
		$c = array();
		
		if(isset($this->settings["Beings"]) && isset($this->settings["Beings"][$being])){
			$rawConfigurations = $this->settings["Beings"][$being];
			$c = $this->convert($rawConfigurations);
		}
		
		return $c;
	}
	
	public function convert($rawConfigurations){
		$c = array();
		foreach ($rawConfigurations as $key => $value) {
			$annotation = sprintf("\Admin\Annotations\%s", $key);
			$name = lcfirst($key);
			
			switch (true) {
				case is_array($value) && isset($value[0]) && is_array($value[0]):
					foreach ($value as $subValue) {
						$subValue = $this->lcfirstArray($subValue);
						if(!isset($c[$name]))
							$c[$name] = array();
						$c[$name][] = new $annotation($subValue);
					}
					break;

				case $key == "Properties":
					$c[$name] = array();
					foreach ($value as $property => $raw) {
						$c[$name][$property] = $this->convert($raw);
					}
					break;

				case !is_array($value):
					$value = array("value" => $value);

				default:	
					$value = $this->lcfirstArray($value);
					if(!isset($c[$name]))
						$c[$name] = array();
					$c[$name][] = new $annotation($value);
					break;
			}
		}
		return $c;
	}
	
	public function lcfirstArray($array){
		$newArray = array();
		foreach ($array as $key => $value) {
			$newArray[lcfirst($key)] = $value;
		}
		return $newArray;
	}
}
?>