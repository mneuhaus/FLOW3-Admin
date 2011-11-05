<?php
namespace Admin\Adapters;

/*                                                                        *
 * This script belongs to the FLOW3 package "Contacts".                   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Dummy adapter for testing and most basic demonstration of the adapter principle
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * 
 */
class DummyAdapter extends \Admin\Core\Adapters\AbstractAdapter {

	public function init() {
		$this->settings = $this->helper->getSettings("Dummy");
		parent::init();
		$this->fmc = $this->objectManager->get('TYPO3\FLOW3\MVC\FlashMessageContainer');
		
		$this->yamlSource = $this->objectManager->get('TYPO3\FLOW3\Configuration\Source\YamlSource');
		
		#\dump($this->settings);
		$this->loadData();
	}

	public function loadData() {
		$this->data = $this->yamlSource->load($this->settings["DataStorage"]);
	}

	public function saveData() {
		$this->yamlSource->save($this->settings["DataStorage"], $this->data);
	}

	public function getName($being) {
		return ucfirst($being);
	}

	public function getGroups() {
		$this->settings = $this->helper->getSettings("Dummy");
		$beings = array ();
		
		if( $this->objectManager->getContext() == "Development" ) {
			foreach($this->settings ["Beings"] as $being => $conf) {
				$beings ["Dummy"] [$being] = array ("name" => $this->getName($being), "being" => $being );
			}
		}
		
		return $beings;
	}

	public function getObject($being, $id) {
		return isset($this->data [$being] [$id]) ? $this->data [$being] [$id] : array ();
	}

	public function getObjects($being) {
		return isset($this->data [$being]) ? $this->data [$being] : array ();
	}

	public function getId($object) {
		return isset($object ["id"]) ? $object ["id"] : null;
	}

	public function createObject($being, $data) {
		$id = count($this->data);
		$data ["id"] = $id;
		$this->data [$being] [$id] = $data;
		$this->saveData();
		return array ();
	}

	public function updateObject($being, $id, $data) {
		$this->loadData();
		$data ["id"] = $id;
		$this->data [$being] [$id] = $data;
		$this->saveData();
	}

	public function deleteObject($being, $id) {
		unset($this->data [$being] [$id]);
		$this->saveData();
	}

	public function getConfiguration($being) {
		$c = $this->settings["Beings"][$being];
		
		foreach($c["properties"] as $property => $conf){
			preg_match("/\\\\Beings*\\\\([A-Za-z]+)$/", $conf ["type"], $matches);
			if(!empty($matches))
				$c["properties"][$property]["being"] = $matches [1];
			$c["properties"][$property] = array_merge_recursive($this->settings["Defaults"]["properties"], $c["properties"][$property]);
		}
		
		return $c;
	}
	
	## Conversion Functions
	

	public function beingsToIdentifiers($beings) {
		$identifiers = array();
		if( is_array($beings) ) {
			foreach($beings as $key => $being) {
				$identifiers [] = $this->getId($being);
			}
		}
		return implode(",", $identifiers);
	}

	public function identifiersToBeings($identifiers, $conf, $property) {
		preg_match("/\\\\Beings\\\\([A-Za-z]+)$/", $conf ["type"], $matches);
		$being = $matches [1];
		$identifiers = explode(",", $identifiers);
		$beings = array();
		foreach($identifiers as $identifier) {
			$beings [] = $this->getObject($being, $identifier);
		}
		return $beings;
	}

	public function identifierToBeing($identifier, $conf, $property) {
		preg_match("/\\\\Being\\\\([A-Za-z]+)$/", $conf ["type"], $matches);
		$being = $matches [1];
		return $this->getObject($being, $identifier);
	}
}

?>