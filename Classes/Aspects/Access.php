<?php

namespace Admin\Aspects;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Aspect
 */
class Access {
	/**
	 * @var \Admin\Security\SecurityManager
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $securityManager;

	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;
	
	/**
	 * @var TYPO3\FLOW3\Security\Context
	 */
	protected $securityContext;
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * @var \TYPO3\FLOW3\MVC\FlashMessageContainer
	 * @FLOW3\Inject
	 */
	protected $flashMessageContainer;
	
	/**
	 * Constructor
	 *
	 * @param \TYPO3\FLOW3\Security\Context $securityContext
	 * @param \TYPO3\FLOW3\Log\SecurityLoggerInterface $securityLogger
	 */
	public function __construct(\TYPO3\FLOW3\Security\Context $securityContext, \TYPO3\FLOW3\Log\SecurityLoggerInterface $securityLogger) {
		$this->securityContext = $securityContext;
		$this->securityLogger = $securityLogger;
	}
	
	/**
	 * Advices the dispatch method so that illegal requests are blocked before invoking
	 * any controller.
	 *
	 * @FLOW3\Around("method(TYPO3\FLOW3\MVC\Dispatcher->dispatch())")
	 * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint The current joinpoint
	 * @return mixed Result of the advice chain
	 */
	public function checkAccess(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$this->securityManager->setRequest($joinPoint->getMethodArgument('request'));
		$this->securityManager->setResponse($joinPoint->getMethodArgument('response'));
		
		$request = $joinPoint->getMethodArgument('request');
		if(is_a($request, "\TYPO3\FLOW3\MVC\Web\Request")){
		
			$className = $request->getControllerObjectName();
			$methodName = $request->getControllerActionName()  . 'Action';
		
			try{
				if(!empty($className) && $this->reflectionService->isMethodAnnotatedWith($className, $methodName, "Admin\Annotations\Access")){
					$annotation = $this->reflectionService->getMethodAnnotation($className, $methodName, "Admin\Annotations\Access");
			
					if(!is_object($user = $this->securityManager->getUser()))
						return $this->securityManager->redirectToLogin($joinPoint);
			
					if($annotation->admin && !$user->isAdmin())
						return $this->securityManager->redirectToLogin($joinPoint);
				
					if($annotation->role !== null){
						$hasRole = false;
						foreach ($user->getRoles() as $role) {
							if($role->getName() == $annotation->role)
								$hasRole = true;
						}
						if(!$hasRole){
							$message = new \TYPO3\FLOW3\Error\Error("You don't have access to this page!");
							$this->flashMessageContainer->addMessage($message);
							return $this->securityManager->redirectToLogin($joinPoint);
						}
					}
				}
			}catch(\Exception $e){
			
			}
		
		}
			
		if(is_object($adviceChain = $joinPoint->getAdviceChain())){
			$result = $adviceChain->proceed($joinPoint);
			return $result;
		}
	}
}