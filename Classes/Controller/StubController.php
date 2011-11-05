<?php
 
namespace Admin\Controller;

/*                                                                        *
 * This script belongs to the FLOW3 package "Admin".                      *
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
 * A disposable controller for some testing
 *
 * @version $Id: $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class StubController extends \TYPO3\FLOW3\MVC\Controller\ActionController {
    public function indexAction(){
		$being = "\Admin\Domain\Model\Widgets";
		$classAnnotations = $this->reflectionService->getClassAnnotations($being);
		$propertyAnnotations = $this->reflectionService->getPropertyAnnotations($being, "string");
		print_r($classAnnotations);
		print_r($propertyAnnotations);
		return "";
    }
}

?>