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
final class Access {

	/**
	 * @var string
	 */
	public $role = null;
	
	/**
	 * @var string
	 */
	public $admin = false;
	
	/**
	 * @param string $values
	 */
	public function __construct(array $values) {
		$this->role = isset($values['role']) ? $values['role'] : $this->role;
		$this->admin = isset($values['admin']) ? $values['admin'] : $this->admin;
	}

}

?>