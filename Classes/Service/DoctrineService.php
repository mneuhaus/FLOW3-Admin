<?php
namespace Admin\Service;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Service class for tasks related to Doctrine
 *
 * @FLOW3\Scope("singleton")
 */
class DoctrineService extends \TYPO3\FLOW3\Persistence\Doctrine\Service {
	/**
	 * @var \TYPO3\FLOW3\Package\PackageManagerInterface
	 * @author Marc Neuhaus <apocalip@gmail.com>
	 * @FLOW3\Inject
	 */
	protected $packageManager;
	
	/**
	 * Injects the FLOW3 settings, the persistence part is kept
	 * for further use.
	 *
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
#		$this->settings = $settings['persistence'];
	}
	
	public function getDatabaseDiff(){
		$configuration = $this->getMigrationConfiguration();
		$up = NULL;
		$down = NULL;

		$connection = $this->entityManager->getConnection();
		$platform = $connection->getDatabasePlatform();
		$metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

		if (empty($metadata)) {
			return 'No mapping information to process.';
		}

		$tool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
		
		$fromSchema = $connection->getSchemaManager()->createSchema();
		$toSchema = $tool->getSchemaFromMetadata($metadata);
		$up = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateToSql($toSchema, $platform));
		$down = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateFromSql($toSchema, $platform));

		if (!$up && !$down) {
			return 'No changes detected in your mapping information.';
		}
		
		$comparator = new \Doctrine\DBAL\Schema\Comparator();
		return $comparator->compare($fromSchema, $toSchema);
	}
	
	
	/**
	 * Generates a new migration file and returns the path to it.
	 *
	 * If $diffAgainstCurrent is TRUE, it generates a migration file with the
	 * diff between current DB structure and the found mapping metadata.
	 *
	 * Otherwise an empty migration skeleton is generated.
	 *
	 * @param boolean $diffAgainstCurrent
	 * @return string Path to the new file
	 */
	public function generateAndExecutePartialMigrationFor($tables = array(), $package = null, $diffAgainstCurrent = TRUE) {
		$configuration = $this->getMigrationConfiguration();
		$up = NULL;
		$down = NULL;

		if ($diffAgainstCurrent === TRUE) {
			$connection = $this->entityManager->getConnection();
			$platform = $connection->getDatabasePlatform();
			$metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

			if (empty($metadata)) {
				return 'No mapping information to process.';
			}

			$tool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);

			$fromSchema = $connection->getSchemaManager()->createSchema();
			$toSchema = $tool->getSchemaFromMetadata($metadata);
			
			$fromSchema = $this->filterSchema($fromSchema, $tables);
			$toSchema = $this->filterSchema($toSchema, $tables);
			
			$up = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateToSql($toSchema, $platform));
			$down = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateFromSql($toSchema, $platform));

			if (!$up && !$down) {
				return 'No changes detected in your mapping information.';
			}
		}
		
		if(!is_null($package) && $this->packageManager->isPackageActive($package)){
			$package = $this->packageManager->getPackage($package);
			$migrationsPath = $package->getPackagePath() . "Migrations/" . ucfirst($platform->getName()) . "/";
			$configuration->setMigrationsDirectory($migrationsPath);
		}
		
		$migrationFile = $this->writeMigrationClassToFile($configuration, $up, $down);
		preg_match("/Version([0-9]+)\.php/", $migrationFile, $matches);
		$version = $matches[1];
		$this->executeMigration($version);
	}
	
	public function filterSchema($schema, $filter = array()){
		if(count($filter) > 0){
			$tables = array();
			foreach ($schema->getTables() as $table => $object) {
				if(in_array($table, $filter)){
					$tables[$table] = $object;
				}
			}
			$filteredSchema = new \Doctrine\DBAL\Schema\Schema($tables, $schema->getSequences());
			return $filteredSchema;
		}else{
			return $schema;
		}
	}
	
	/**
	 * Returns the current migration status formatted as plain text.
	 *
	 * @return string
	 */
	public function getMigrationStatus() {
		$configuration = $this->getMigrationConfiguration();

		$currentVersion = $configuration->getCurrentVersion();
		if ($currentVersion) {
			$currentVersionFormatted = $configuration->formatVersion($currentVersion) . ' ('.$currentVersion.')';
		} else {
			$currentVersionFormatted = 0;
		}
		
		$migrationsWaiting = $configuration->getMigrationsToExecute("up", $configuration->getLatestVersion());
		$migrationsWaiting = array_reverse($migrationsWaiting, true);
		
		$latestVersion = key($migrationsWaiting);
		if ($latestVersion) {
			$latestVersionFormatted = $configuration->formatVersion($latestVersion) . ' ('.$latestVersion.')';
		} else {
			$latestVersionFormatted = 0;
		}
		
		$status = array(
			'migration-current'     => $currentVersionFormatted,
			'migration-lastest'     => $latestVersionFormatted,
			'migrations-waiting'    => $migrationsWaiting,
		);
		
		return $status;
	}
	
	public function migrateToLatest(){
		$configuration = $this->getMigrationConfiguration();
		$migrationsWaiting = $configuration->getMigrationsToExecute("up", $configuration->getLatestVersion());
		$migrationsWaiting = array_reverse($migrationsWaiting, true);
		$latestVersion = key($migrationsWaiting);
		
		return $this->executeMigrations($latestVersion);
	}
}

?>