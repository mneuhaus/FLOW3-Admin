<?php

namespace F3\Admin\Actions;

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

/**
 * Abstract validator
 *
 * @version $Id: AbstractValidator.php 3837 2010-02-22 15:17:24Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @prototype
 */
class DeleteAction implements ActionInterface {
    /**
     * @var F3\Admin\Adapters\AdapterInterface $adapter
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    protected $adapter;

    /**
     * @param F3\Admin\Adapters\AdapterInterface $adapter
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function injectAdapter($adapter){
        $this->adapter = $adapter;
    }

    /**
     * The Name of this Action
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function __toString(){
        return "Delete";
    }

    /**
     * Delete objects
     *
     * @param array $ids
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
    public function execute($ids, $being){
        foreach($ids as $id){
            $this->adapter->deleteObject($being, $id);
        }
    }
}

?>