<?php

namespace F3\Admin\Adapters;

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

/**
 * Abstract validator
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @prototype
 */
abstract class AbstractAdapter implements AdapterInterface {
	/**
	 * @var \F3\Admin\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $helper;
	
	protected $conf = array(
		"F3\Admin\Domain\Model\Tag" => array(
			array(
				"label" => "Name",
				"name" => "name",
				"type" => "Textfield",
				"validation" =>"required"
			)
		)
	);
	
	protected $objects = array(
		"F3\Admin\Domain\Model\Tag" => array(
			array("name"=>"Hello World"),
			array("name"=>"sdfasd"),
			array("name"=>"opdas8ioj"),
			array("name"=>"oasd0ißü")
		)
	);
	
	public function getName($being){
		$parts = explode("\\",$being);
		return str_replace("_AOPProxy_Development","",end($parts));
	}
	
	public function getGroups(){
		$groups = array();
		foreach ($this->conf as $key => $value) {
			$groups["Abstract"][] = array(
				"being" => $key,
				"name" => ucfirst($value["name"]),
			);
		}
		return $groups;
	}
	
	public function getAttributeSets($being, $id = null){		
		$attributes = array();
		$fields = $this->conf[$being];
		foreach ($fields as $key => $value) {
			$attributes["General"][] = array(
				"label" 	=> $value["name"],
				"name" 	=> $key,
				"error" 	=> "",
				"widget" 	=> $value["type"]
			);
		}
		return $attributes;
	}
	
	public function createObject($being, $data){
		$fields = $this->conf[$being];
		$errors = array();
		foreach ($fields as $conf) {
			if(array_key_exists("validation",$conf)){
				switch ($conf["validation"]) {
					case 'required':
							if(empty($data[$conf["name"]]))
								$errors[$conf["name"]][] = "Field is required!";
						break;
					
					default:
						break;
				}
			}
		}
		
		return $errors;
	}
	
	public function getObjects($being){
		$objects = array();
		foreach ($this->objects[$being] as $id => $object) {
			foreach ($this->conf[$being] as $property) {
				$property["value"] = $object[$property["name"]];
				$objects[$id][] = $property;
			}
		}
		
		return $objects;
	}
	
	public function getObject($being,$id){
		$objects = $this->getObjects($being);
		$object = $objects[$id];
		return $object;
	}
	
	public function updateObject($being, $data){
		$fields = $this->conf[$being];
		$errors = array();
		foreach ($fields as $conf) {
			if(array_key_exists("validation",$conf)){
				switch ($conf["validation"]) {
					case 'required':
							if(empty($data[$conf["name"]]))
								$errors[$conf["name"]][] = "Field is required!";
						break;
					
					default:
						break;
				}
			}
		}
		
		return $errors;
	}
	
	public function deleteObject($being,$id){
		unset($this->objects[$id]);
	}
}

?>