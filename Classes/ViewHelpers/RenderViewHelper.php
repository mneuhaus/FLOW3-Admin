<?php
 
namespace F3\Admin\ViewHelpers;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 * @version $Id: ForViewHelper.php 3346 2009-10-22 17:26:10Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 * @scope prototype
 */
class RenderViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \F3\Admin\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $helper;
	
    /**
     * @var \F3\FLOW3\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;
    
    /**
     * @var \F3\Fluid\Core\Parser\TemplateParser
     * @inject
     */
    protected $templateParser;

    /**
	 * @var F3\FLOW3\Cache\CacheManager
	 * @inject
	 */
	protected $cacheManager;
    
	/**
	 *
	 * @param object $value
	 * @param string $partial
	 * @param string $fallbacks
	 * @param array $vars
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($value='',$partial='',$fallbacks='',$vars = array()) {
		if($value !== '')
			return $value;

		if ($partial !== '') {
			if($fallbacks !== ''){
                $replacements = array(
                    "@partial" => $partial,
                    "@package" => \F3\Admin\Register::get("package"),
                    "@being" => \F3\Admin\Core\Helper::getShortName(\F3\Admin\Register::get("being")),
                    "@action" => $partial
                );

                $cache = $this->cacheManager->getCache('Admin_TemplateCache');
                $identifier = str_replace("\\","_",implode("-",$replacements));
                if(!$cache->has($identifier)){
                    $template = $this->helper->getPathByPatternFallbacks($fallbacks,$replacements);
                    $cache->set($identifier,$template);
                }else{
                    $template = $cache->get($identifier);
                }
                
				if(empty($vars)){
				    $this->view = $this->viewHelperVariableContainer->getView();
                    $this->view->setTemplatePathAndFilename($template);
				
	                if(!empty($template)){
	                    return $this->view->render();
	                }
				}else{
			        $partial = $this->parseTemplate($template);
			        $variableContainer = $this->objectManager->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $vars);
			        $renderingContext = $this->buildRenderingContext($variableContainer);
			        return $partial->render($renderingContext);
				}
			}
		}
	}
	
   protected function parseTemplate($templatePathAndFilename) {
        $templateSource = \F3\FLOW3\Utility\Files::getFileContents($templatePathAndFilename, FILE_TEXT);
        if ($templateSource === FALSE) {
            throw new \F3\Fluid\View\Exception\InvalidTemplateResourceException('"' . $templatePathAndFilename . '" is not a valid template resource URI.', 1257246929);
        }
        return $this->templateParser->parse($templateSource);
    }
    
    /**
     * Build the rendering context
     *
     * @param \F3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer
     * @return \F3\Fluid\Core\Rendering\RenderingContext
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     */
    protected function buildRenderingContext(\F3\Fluid\Core\ViewHelper\TemplateVariableContainer $variableContainer = NULL) {
        if ($variableContainer === NULL) {
            $variableContainer = $this->objectManager->create('F3\Fluid\Core\ViewHelper\TemplateVariableContainer', $this->variables);
        }

        $renderingContext = $this->objectManager->create('F3\Fluid\Core\Rendering\RenderingContext');
        $renderingContext->injectTemplateVariableContainer($variableContainer);
        if ($this->controllerContext !== NULL) {
            $renderingContext->setControllerContext($this->controllerContext);
        }

        $viewHelperVariableContainer = $this->objectManager->create('F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer');
        $viewHelperVariableContainer->setView($this->viewHelperVariableContainer->getView());
        $renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);

        return $renderingContext;
    }
}

?>
