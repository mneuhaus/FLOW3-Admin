<?php

namespace Admin\Aspects;

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Aspect
 */
class Navigation {
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;
	
	/**
	 * @var \TYPO3\FLOW3\Reflection\ReflectionService
	 * @api
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $reflectionService;
	
	/**
	 * @var \TYPO3\FLOW3\Object\ObjectManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $objectManager;
	
	/**
	 * Add the Annotated Method to the Navigation
	 *
	 * @param \TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint
	 * @FLOW3\Before("method(public .*\Controller\.*Controller->.*Action(.*))")
	 * @return void
	 */
	public function addNavigationitem(\TYPO3\FLOW3\AOP\JoinPointInterface $joinPoint) {
		$currentClassName = $joinPoint->getClassName();
		$currentMethodName = $joinPoint->getMethodName();
		$controllers = $this->reflectionService->getAllSubClassNamesForClass("\TYPO3\FLOW3\MVC\Controller\ActionController");
		foreach ($controllers as $className) {
			$methods = get_class_methods($className);
			foreach ($methods as $methodName) {
				if($this->reflectionService->isMethodAnnotatedWith($className, $methodName, "Admin\Annotations\Navigation")){
					$annotation = $this->reflectionService->getMethodAnnotation($className, $methodName, "Admin\Annotations\Navigation");

					$action = str_replace("Action", "", $methodName);
					$controller = $this->helper->getControllerByClassName($className);
					$package = $this->objectManager->getPackageKeyByObjectName($className);
					$arguments = array(
						"action" => $action,
						"controller" => $controller,
						"package" => $package
					);
					$title = !is_null($annotation->title) ? $annotation->title : sprintf("%s (%s)", $controller, $action);

					\Admin\Core\API::addNavigationitem($title, $annotation->position, $arguments, $annotation->priority, $annotation->parent);
				}
			}
		}
		$settings = $this->helper->getSettings("Admin.Navigation");
		foreach ($settings as $position => $items) {
			foreach ($items as $title => $conf) {
				$priority = isset($conf["priority"]) ? $conf["priority"] : 100;
				$arguments = $conf["Arguments"];
				\Admin\Core\API::addNavigationitem($title, strtolower($position), $arguments, $priority);
			}
		}
	}

}