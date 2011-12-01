<?php
namespace Admin\Security;

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

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * The repository for accounts
 *
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class UserRepository extends \TYPO3\FLOW3\Persistence\Repository {
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;
	
	/**
	 * Constructs the Account Repository
	 *
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct() {
		parent::__construct();
		$this->objectType = 'Admin\Security\User';
	}

	/**
	 * Returns the account for a specific authentication provider with the given identitifer
	 *
	 * @param string $accountIdentifier The account identifier
	 * @param string $authenticationProviderName The authentication provider name
	 * @return TYPO3\FLOW3\Security\Account
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function findByAccountIdentifierAndAuthenticationProviderName($accountIdentifier, $authenticationProviderName) {
		$result = array();

		$query = $this->createQuery();
		$result = $query->matching(
			$query->logicalAnd(
				$query->equals('accountIdentifier', $accountIdentifier),
				$query->equals('authenticationProviderName', $authenticationProviderName)
			)
		)->execute();

		return isset($result[0]) ? $result[0] : FALSE;
	}
	
	
	/**
	 * Schedules a modified object for persistence.
	 *
	 * @param object $object The modified object
	 * @throws \TYPO3\FLOW3\Persistence\Exception\IllegalObjectTypeException
	 * @api
	 */
	public function add($object) {
		if(!$this->helper->isDemoMode() || $object->__toString() != $this->helper->getSettings("Admin.SuperAdmin"))
			parent::add($object);
	}

	/**
	 * Schedules a modified object for persistence.
	 *
	 * @param object $object The modified object
	 * @throws \TYPO3\FLOW3\Persistence\Exception\IllegalObjectTypeException
	 * @api
	 */
	public function update($object) {
		if(!$this->helper->isDemoMode() || $object->__toString() != $this->helper->getSettings("Admin.SuperAdmin"))
			parent::update($object);
	}
	
	/**
	 * Returns a query for objects of this repository
	 *
	 * @return \TYPO3\FLOW3\Persistence\Doctrine\Query
	 * @api
	 */
	public function createQuery() {
		$query = parent::createQuery();
		if($this->helper->isDemoMode()){
			if($this->helper->getUser()){
				$query->matching(
					$query->logicalNot(
						$query->equals("accountIdentifier", $this->helper->getSettings("Admin.SuperAdmin"))
					)
				);
			}
		}
		return $query;
	}

	/**
	 * Schedules a modified object for persistence.
	 *
	 * @param object $object The modified object
	 * @throws \TYPO3\FLOW3\Persistence\Exception\IllegalObjectTypeException
	 * @api
	 */
	public function remove($object) {
		if(!$this->helper->isDemoMode() || $object->__toString() != $this->helper->getSettings("Admin.SuperAdmin"))
			parent::remove($object);
	}
}

?>