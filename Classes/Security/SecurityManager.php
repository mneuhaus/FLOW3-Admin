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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * 
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @FLOW3\Scope("singleton")
 */
class SecurityManager extends \TYPO3\FLOW3\Mvc\Controller\ActionController {
	/**
	 * The current request
	 * @var \TYPO3\FLOW3\Mvc\RequestInterface
	 */
	protected $request;

	/**
	 * The response which will be returned by this action controller
	 * @var \TYPO3\FLOW3\Mvc\ResponseInterface
	 */
	protected $response;
	
	/**
	 * @var \TYPO3\FLOW3\Security\Context
	 * @FLOW3\Inject
	 */
	protected $securityContext;

	/**
	 * @var \TYPO3\FLOW3\Log\SecurityLoggerInterface
	 * @FLOW3\Inject
	 */
	protected $securityLogger;
	
	public function setRequest(\TYPO3\FLOW3\Mvc\RequestInterface $request){
		$this->request = $request;
	}
	
	public function setResponse(\TYPO3\FLOW3\Mvc\ResponseInterface $response){
		$this->response = $response;
	}
	
	/**
	 * this function checks if the user is allowed to see this page
	 *
	 * @param string $being 
	 * @param string $action 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function isAllowed($being, $action){
		foreach ($this->securityContext->getAuthenticationTokens() as $token){
			if(is_callable(array($token,"getUser"))){
				$user = $token->getUser();
				if($user->isAdmin())
					return true;

				foreach ($user->getRoles() as $role) {
					foreach ($role->getGrant() as $policy) {
						if($this->comparePolicy(array("action"=>$action,"being"=>$being), $policy)) return true;
					}
				}
			}
		}
		return false;
	}
	
	public function getUser(){
		$activeTokens = $this->securityContext->getAuthenticationTokens();
		foreach ($activeTokens as $token) {
			if ( $token->isAuthenticated() && is_callable(array($token,"getUser")) ) {
				$user = $token->getUser();
				return $user;
			}
		}
		return false;
	}
	
	public function redirectToLogin(){
		$entryPointFound = FALSE;
		foreach ($this->securityContext->getAuthenticationTokens() as $token) {
			if(!is_object($token)) continue;
			
			$entryPoint = $token->getAuthenticationEntryPoint();
			if ($entryPoint !== NULL) {
				$entryPointFound = TRUE;
				if ($entryPoint instanceof \TYPO3\FLOW3\Security\Authentication\EntryPoint\WebRedirect) {
					$options = $entryPoint->getOptions();
					$options['uri'] = $options['uri'] . "?_redirect=" . urlencode($this->request->getHttpRequest()->getURI());
					$entryPoint->setOptions($options);
					$this->securityLogger->log('Redirecting to authentication entry point with URI ' . (isset($options['uri']) ? $options['uri'] : '- undefined -'), LOG_INFO);
				} else {
					$this->securityLogger->log('Starting authentication with entry point of type ' . get_class($entryPoint), LOG_INFO);
				}
				$rootRequest = $this->request;
				if ($this->request instanceof \TYPO3\FLOW3\Mvc\Web\SubRequest) $rootRequest = $this->request->getRootRequest();
				$this->securityContext->setInterceptedRequest($rootRequest);
				$entryPoint->startAuthentication($rootRequest->getHttpRequest(), $this->response);
				
				throw new \TYPO3\FLOW3\Mvc\Exception\StopActionException();
			}
		}
		if ($entryPointFound === FALSE) {
			$this->securityLogger->log('No authentication entry point found for active tokens, therefore cannot authenticate or redirect to authentication automatically.', LOG_NOTICE);
			throw new \TYPO3\FLOW3\Security\Exception\AuthenticationRequiredException('No authentication entry point found for active tokens, therefore cannot authenticate or redirect to authentication automatically.', 1317309673);
		}
	}
	
}

?>