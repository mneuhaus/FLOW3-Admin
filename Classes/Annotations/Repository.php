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
final class Repository {
	
	/**
	 * @var integer
	 */
	public $class = "";
	
	/**
	 * @param string $value
	 */
	public function __construct(array $values = array()) {
		$this->class = isset($values['value']) ? $values['value'] : $this->class;
		$this->class = isset($values['class']) ? $values['class'] : $this->class;
	}
	
	public function __toString(){
		return $this->class;
	}
}

?>