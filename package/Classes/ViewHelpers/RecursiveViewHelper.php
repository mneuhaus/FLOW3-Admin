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
class RecursiveViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \F3\Admin\Utilities
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $utilities;
	
	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @param object $object
	 * @param string $objects
	 * @param string $subs
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render($object,$objects,$subs) {
		$output = "";
		$subContent = "";
		$properties = $this->utilities->getModelProperties(get_class($object));
		foreach ($properties as $property => $tags) {
			if(in_array("var",array_keys($tags)) && count($tags["var"]>0)){
				$type = current($tags["var"]);
				if($this->utilities->isEntity($type)){
					$child = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
					if(is_object($child))
						$subContent.= $this->render($child,$objects,$subs);
				}
				if($this->utilities->isEntity($this->utilities->getSubType($type))){
#					$singular = \F3\Admin\Service\Inflect::singularize($property);
#					$child = \F3\FLOW3\Reflection\ObjectAccess::getProperty($object,$property);
#					echo "type subtype:".$type." - ".$singular."<br />";
				}
			}
		}
		
		$this->templateVariableContainer->add($objects, array($object));
		$this->templateVariableContainer->add($subs, $subContent);
		$output .= $this->renderChildren();
		$this->templateVariableContainer->remove($objects);
		$this->templateVariableContainer->remove($subs);
		return $output;
	}
}

?>
