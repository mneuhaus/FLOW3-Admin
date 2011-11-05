<?php
declare(ENCODING = 'utf-8');
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * An authentication provider that authenticates
 * TYPO3\FLOW3\Security\Authentication\Token\UsernamePassword tokens.
 * The accounts are stored in the Content Repository.
 *
 * @version $Id: PersistedUsernamePasswordProvider.php 4021 2010-03-29 15:09:47Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("prototype")
 */
class AdminProvider implements \TYPO3\FLOW3\Security\Authentication\AuthenticationProviderInterface {

	/**
	 * @var \Admin\Security\UserRepository
     * @FLOW3\Inject
	 */
	protected $userRepository;

	/**
	 * @var \TYPO3\FLOW3\Security\Cryptography\HashService
	 */
	protected $hashService;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * Injects the user repository
	 *
	 * @param \Admin\Security\UserRepository $userRepository The account repository
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function injectUserRepository(\Admin\Security\UserRepository $userRepository) {
		$this->userRepository = $userRepository;
	}

	/**
	 * Injects the hash service
	 *
	 * @param \TYPO3\FLOW3\Security\Cryptography\HashService $hashService
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectHashService(\TYPO3\FLOW3\Security\Cryptography\HashService $hashService) {
		$this->hashService = $hashService;
	}

	/**
	 * Constructor
	 *
	 * @param string $name The name of this authentication provider
	 * @param array $options Additional configuration options
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function __construct($name, array $options) {
		$this->name = $name;
	}

	/**
	 * Returns TRUE if the given token can be authenticated by this provider
	 *
	 * @param TYPO3\FLOW3\Security\Authentication\TokenInterface $authenticationToken The token that should be authenticated
	 * @return boolean TRUE if the given token class can be authenticated by this provider
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function canAuthenticate(\TYPO3\FLOW3\Security\Authentication\TokenInterface $authenticationToken) {
		if ($authenticationToken->getAuthenticationProviderName() === $this->name) return TRUE;
		return FALSE;
	}

	/**
	 * Returns the classnames of the tokens this provider is responsible for.
	 *
	 * @return string The classname of the token this provider is responsible for
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function getTokenClassNames() {
		return array('Admin\Security\Token\UsernamePassword');
	}

	/**
	 * Sets isAuthenticated to TRUE for all tokens.
	 *
	 * @param TYPO3\FLOW3\Security\Authentication\TokenInterface $authenticationToken The token to be authenticated
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function authenticate(\TYPO3\FLOW3\Security\Authentication\TokenInterface $authenticationToken) {
		if (!($authenticationToken instanceof \Admin\Security\Token\UsernamePassword)) throw new \TYPO3\FLOW3\Security\Exception\UnsupportedAuthenticationTokenException('This provider cannot authenticate the given token.', 1217339840);

		$user = NULL;
		$credentials = $authenticationToken->getCredentials();

		if (is_array($credentials) && isset($credentials['username'])) {
			$user = $this->userRepository->findByAccountIdentifierAndAuthenticationProviderName($credentials['username'], $this->name);
		}

		if (is_object($user)) {
			if ($this->hashService->validateSaltedMd5($credentials['password'], $user->getCredentialsSource())) {
				$authenticationToken->setAuthenticationStatus(\TYPO3\FLOW3\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL);
				$authenticationToken->setUser($user);
			} else {
				$authenticationToken->setAuthenticationStatus(\TYPO3\FLOW3\Security\Authentication\TokenInterface::WRONG_CREDENTIALS);
			}
		} elseif ($authenticationToken->getAuthenticationStatus() !== \TYPO3\FLOW3\Security\Authentication\TokenInterface::AUTHENTICATION_SUCCESSFUL) {
			$authenticationToken->setAuthenticationStatus(\TYPO3\FLOW3\Security\Authentication\TokenInterface::NO_CREDENTIALS_GIVEN);
		}
	}
}

?>