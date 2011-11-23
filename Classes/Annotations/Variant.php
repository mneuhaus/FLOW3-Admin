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
final class Variant {
	
	/**
	 * @var string
	 */
	public $variant = "Default";
	
	/**
	 * @var array
	 */
 	public $values = array();
	
	/**
	 * @param string $value
	 */
	public function __construct(array $values = array()) {
		$this->variant = isset($values['value']) ? $values['value'] : $this->variant;
		$this->variant = isset($values['variant']) ? $values['variant'] : $this->variant;
		$this->values = $values;
	}
	
	public function getDefault(){
		return $this->variant == "Default";
	}
}

?>