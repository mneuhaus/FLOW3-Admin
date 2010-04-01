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
class NavigationViewHelper extends \F3\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @var \F3\Admin\Helper
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @inject
	 */
	protected $helper;
	
	/**
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @return string Rendered string
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @api
	 */
	public function render() {
		$output = '';
		$items = array(
			array(
				"name"=>"Overview",
				"controller"=>"standard",
				"action"=>"index",
				"active" => true
			)
		);
		$groups = $this->helper->getGroups();
		foreach ($groups as $group => $beings) {
			$items[] = array(
				"name"=>$group,
				"controller"=>"standard",
				"action"=>"index",
				"group"=>$group,
				"active" => false
			);
		}
		
        foreach ($items as $item) {
			foreach ($item as $key => $value) {
				$this->templateVariableContainer->add($key, $value);
			}
			$output .= $this->renderChildren();
			foreach ($item as $key => $value) {
				$this->templateVariableContainer->remove($key, $value);
			}
		}
		
		return $output;
	}
}

?>
