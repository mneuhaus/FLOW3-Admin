<?php

namespace Admin\Core\Adapters;

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
 * Interface for the Adapters
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @author Marc Neuhaus <marc@mneuhaus.com>
 */
interface AdapterInterface {
	
	/**
	 * apply filters
	 *
	 * @param string $beings 
	 * @param string $filters 
	 * @return void
	 * @author Marc Neuhaus
	 */
	public function applyFilters($beings, $filters);
	
	public function applyLimit($limit);
	
	public function applyOffset($offset);
	
    /**
     * Returns a Representational String for a being
     *
     * @param string $being The Identifier for the being
     * @return string $name The Name of the Being
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getName($being);

    /**
     * Should return all Available Beings grouped by Package
     *
     * @return array $groups of Beings
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getGroups();

    /**
     * Handles the needed Steps to get a Single Being from
     * whereever the Adapters stores its Data
     *
     * @param string $being Class of the Being
     * @param string $id Identifier of the Being
     * @return mixed $object
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getObject($being, $id);

    /**
     * Same as getObject but gets all Available Objects
     *
     * @return mixed $objects
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getObjects($being);

    /**
     * Returns the ID for the given Object
     *
     * @param mixed $object the Object in question
     * @return string $id
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function getId($object);

    /**
     * Handles the creation of a new Being
     *
     * @param string $being Class of the Being
     * @param array $data Raw Form Data
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function createObject($being, $data);

    /**
     * Updates a Being
     *
     * @param string $being Class of the Being
     * @param string $id
     * @param array $data Raw Form Data
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function updateObject($being, $id, $data);

    /**
     * Deletes a Being
     *
     * @param string $being Class of the Being
     * @param string $id
     * @author Marc Neuhaus <mneuhaus@famelo.com>
     * */
	public function deleteObject($being, $id);
}

?>