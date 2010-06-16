<?php
declare(ENCODING = 'utf-8');
namespace F3\Admin;

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

	// Those are needed before the autoloader is active
require_once(__DIR__ . '/../../../../Framework/FLOW3/Classes/Core/Bootstrap.php');

/**
 * General purpose central core hyper FLOW3 bootstrap class
 *
 * @version $Id: Bootstrap.php 4443 2010-06-04 15:16:21Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class FLOW3 extends \F3\FLOW3\Core\Bootstrap {
    public function getObjectManager(){
        return $this->objectManager;
    }

    public function initializeErrorHandling() {
        #return parent::initializeErrorHandling();
    }

}

?>