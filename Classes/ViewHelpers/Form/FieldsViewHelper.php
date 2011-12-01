<?php
namespace Admin\ViewHelpers\Form;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @api
 */
class FieldsViewHelper extends \TYPO3\Fluid\ViewHelpers\Form\AbstractFormViewHelper {
	/**
	 * @var TYPO3\FLOW3\Cache\CacheManager
	 * @FLOW3\Inject
	 */
	protected $cacheManager;
	
	/**
	 * @var \Admin\Core\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $helper;
	
	/**
	 *
	 * @param object $being
	 * @param string $variant
	 * @return string rendered form
	 * @api
	 */
	public function render($being = null, $variant = "Default") {
		$content = "";
		$content.= $this->renderHiddenClassField($being->class);
		$content.= $this->renderFormPartial($being->class, $variant);
		return $content;
	}
	
	public function renderHiddenClassField($class){
		return '<input type="hidden" name="being" value="' . $class . '" />' . chr(10);
	}
	
	public function renderFormPartial($class, $variant = "Default"){
		$replacements = array(
			"@partial" => "Form",
			"@package" => \Admin\Core\API::get("package"),
			"@being" => \Admin\Core\Helper::getShortName($class),
			"@action" => "Form",
			"@variant" => $variant
		);
		
		$cache = $this->cacheManager->getCache('Admin_TemplateCache');
		$identifier = str_replace("\\","_",implode("-", $replacements));
		$identifier = str_replace(".","_", $identifier);
		if(!$cache->has($identifier)){
			$template = $this->helper->getPathByPatternFallbacks("Partials", $replacements);
			$cache->set($identifier, $template);
		}else{
			$template = $cache->get($identifier);
		}
		
		$this->view = $this->viewHelperVariableContainer->getView();
		$this->view->setTemplatePathAndFilename($template);
		
		return $this->view->render();
	}
}

?>