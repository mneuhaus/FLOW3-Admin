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
final class Navigation {

	/**
	 * @var string
	 */
	public $position = 'top';
	
	/**
	 * @var string
	 */
	public $title = null;
	
	/**
	 * @var integer
	 */
	public $priority = 100;
	
	/**
	 * @var string
	 */
	public $parent = null;
	
	/**
	 * @var array
	 */
	public $arguments = null;
	
	/**
	 * @param string $value
	 */
	public function __construct(array $values) {
		$this->position = isset($values['position']) ? $values['position'] : $this->position;
		$this->title = isset($values['title']) ? $values['title'] : $this->title;
		$this->priority = isset($values['priority']) ? $values['priority'] : $this->priority;
		$this->parent = isset($values['parent']) ? $values['parent'] : $this->parent;
		$this->arguments = isset($values['arguments']) ? $values['arguments'] : $this->arguments;
	}

}

?>