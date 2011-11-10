<?php
declare(ENCODING = 'utf-8');
namespace Admin\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * A controller which allows logging into the backend
 *
 * @version $Id: LoginController.php 2817 2009-07-16 14:32:53Z k-fish $
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LoginController extends \TYPO3\FLOW3\MVC\Controller\ActionController {

	/**
	 * @var \Admin\Security\UserRepository
     * @FLOW3\Inject
	 */
	protected $userRepository;

	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
    
	/**
	 * @FLOW3\Inject
	 * @var \TYPO3\FLOW3\Security\Authentication\AuthenticationManagerInterface
	 */
	protected $authenticationManager;

	/**
	 *
	 *
	 * @return string
	 */
	public function indexAction() {
		$users = $this->userRepository->findAll();
		
		$tag = $this->objectManager->get("Admin\Domain\Model\Tag");
		$tag->test();
		
		if($users->count() < 1){
			$user = $this->objectManager->get("Admin\Security\User");
			$user->setAccountIdentifier("admin");
			$user->setCredentialsSource("password");
			$user->setAdmin(true);
			$this->userRepository->add($user);
			$message = new \TYPO3\FLOW3\Error\Message('A User has been Created: admin/password');
			$this->flashMessageContainer->addMessage($message);
			$message = new \TYPO3\FLOW3\Error\Warning('Please Change the Passwort after Login!');
			$this->flashMessageContainer->addMessage($message);
			$this->view->assign("username", "admin");
			$this->view->assign("password", "password");
		}else{
			$this->view->assign("username", "");
			$this->view->assign("password", "");
		}

	}

	/**
	 * Authenticates an account by invoking the Provider based Authentication Manager.
	 *
	 * On successful authentication redirects to the list of posts, otherwise returns
	 * to the login screen.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function authenticateAction() {
		try {
			$this->authenticationManager->authenticate();
			$this->redirect('index', 'Standard');
			$message = new \TYPO3\FLOW3\Error\Message('Successfully logged in');
			$this->flashMessageContainer->addMessage($message);
		} catch (\TYPO3\FLOW3\Security\Exception\AuthenticationRequiredException $exception) {	
			$message = new \TYPO3\FLOW3\Error\Error('Wrong username or password.');
			$this->flashMessageContainer->addMessage($message);
			throw $exception;
		}
	}

	/**
	 *
	 * @return void
	 * @author Andreas Förthner <andreas.foerthner@netlogix.de>
	 */
	public function logoutAction() {
		$this->authenticationManager->logout();
		$message = new \TYPO3\FLOW3\Error\Message('Successfully logged out.');
		$this->flashMessageContainer->addMessage($message);
		$this->redirect('index', 'Login');
	}
}

?>