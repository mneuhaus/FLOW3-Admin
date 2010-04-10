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
class DummyAdapter extends AbstractAdapter {
	public function getName($being){
		return "Dummy Being";
	}
	
	public function getGroups(){
		return array(
			"Dummy" => array(
				array(
					"name" => "Being",
					"being" => "being"
				)
			)
		);
	}
	
	public function getAttributeSets($being, $id = null){
		return array(
			"General" => array(
				array(
					"label" 	=> "Name",
					"name"		=> "name",
					"type"		=> "Textfield",
					"inline"	=> array()
				)
			)
		);
	}
	
	public function createObject($being, $data){
		if(empty($data["name"])){
			return array(
				"name" => array(
					"something went wrong here"
				)
			);
		}else{
			return array();
		}
	}
	
	public function getBeings($being){
		return array(
			array(
				"meta" => array(
					"id" => 1,
					"name" => "First Entry"
				),
				"properties" => array(
					"name" => array(
						"label" => "Name",
						"name" => "name",
						"type" => "Textfield",
						"value" => "First Entry"
					)
				)
			)
		);
	}
	
	public function getBeing($being,$id){
		return current($this->getBeings($being));
	}
	
	public function updateObject($being, $data){
		if(empty($data["name"])){
			return array(
				"name" => array(
					"something went wrong here"
				)
			);
		}else{
			return array();
		}
	}
	
	public function deleteObject($being,$id){
	}
}

?>