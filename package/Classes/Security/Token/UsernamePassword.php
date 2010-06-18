<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin\Security\Token;

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
 * An authentication token used for simple username and password authentication.
 *
 * @version $Id: UsernamePassword.php 3926 2010-03-10 17:57:21Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class UsernamePassword extends \F3\FLOW3\Security\Authentication\Token\UsernamePassword {

	/**
	 * @var F3\Admin\Security\User
	 */
	protected $user;

	/**
	 * Returns the account if one is authenticated, NULL otherwise.
	 *
	 * @return F3\Admin\Security\User An account object
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Set the (authenticated) account
	 *
	 * @param F3\Admin\Security\User $account An account object
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function setUser(\F3\Admin\Security\User $user) {
		$this->user = $user;
	}
}

?>