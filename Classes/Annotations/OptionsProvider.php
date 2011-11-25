<?php
namespace Admin\Annotations;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @Annotation
 */
final class OptionsProvider {
	
	/**
	 * @var integer
	 */
	public $name = "";
	
	/**
	 * @param string $value
	 */
	public function __construct(array $values = array()) {
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
		$this->name = isset($values['value']) ? $values['value'] : $this->name;
		$this->name = isset($values['name']) ? $values['name'] : $this->name;
		if(class_exists(sprintf("\\Admin\\OptionsProvider\\%sOptionsProvider", $this->name)))
			$this->name = sprintf("\\Admin\\OptionsProvider\\%sOptionsProvider", $this->name);
	}
	
	public function __toString(){
		return $this->name;
	}
}

?>