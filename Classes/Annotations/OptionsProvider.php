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
		$this->name = isset($values['value']) ? $values['value'] : $this->name;
		$this->name = isset($name['amount']) ? $name['amount'] : $this->name;
	}
	
	public function __toString(){
		return $this->name;
	}
}

?>