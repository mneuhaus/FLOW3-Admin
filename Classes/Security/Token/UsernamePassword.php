<?php
namespace Admin\Security\Token;

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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An authentication token used for simple username and password authentication.
 *
 * @version $Id: UsernamePassword.php 3926 2010-03-10 17:57:21Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("prototype")
 */
class UsernamePassword extends \TYPO3\FLOW3\Security\Authentication\Token\UsernamePassword {

	/**
	 * Returns the account if one is authenticated, NULL otherwise.
	 *
	 * @return TYPO3\FLOW3\Security\Account An account object
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getAccount() {
		return $this->account;
	}

	/**
	 * Set the (authenticated) account
	 *
	 * @param TYPO3\FLOW3\Security\Account $account An account object
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setAccount(\TYPO3\FLOW3\Security\Account $account = NULL) {
		$this->account = $account;
	}

	/**
	 * Returns the currently valid roles.
	 *
	 * @return array Array of TYPO3\FLOW3\Security\Authentication\Role objects
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getRoles() {
		if ($this->account !== NULL && $this->isAuthenticated()) return $this->account->getRoles();

		return array();
	}

	/**
	 * @var Admin\Security\User
	 */
	protected $user;

	/**
	 * Returns the account if one is authenticated, NULL otherwise.
	 *
	 * @return Admin\Security\User An account object
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Set the (authenticated) account
	 *
	 * @param Admin\Security\User $account An account object
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setUser(\Admin\Security\User $user) {
		$this->user = $user;
	}
	
}

?>