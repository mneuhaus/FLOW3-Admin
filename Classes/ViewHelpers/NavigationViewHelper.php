<?php
declare(ENCODING = 'utf-8');
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
	 * Iterates through elements of $each and renders child nodes
	 *
	 * @param string $title
	 * @param string $controller
	 * @param string $active
	 * @return string Rendered string
	 * @author Sebastian Kurf�rst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @api
	 */
	public function render($title, $controller, $active="active") {
		$output = '';
		/*
        foreach ($each as $keyValue => $singleElement) {
			$this->templateVariableContainer->add($as, $singleElement);
			$output .= $this->renderChildren();
			$this->templateVariableContainer->remove($as);
			if ($key !== '') {
				$this->templateVariableContainer->remove($key);
			}
		}
        */
		return $output;
	}
}

?>