<?php

namespace Mcenter\DAO;

/**
 * Description of ApplicationDAO
 *
 * @author hoanvd
 */
class ApplicationDAO extends TenantSupportDAO {

	private static $logger;

	/**
	 * Constructor
	 * 
	 * @param type $_tenantId
	 */
	public function __construct($_tenantId) {
		parent::__construct($_tenantId);
	}

	/**
	 * Get list of Application by role
	 * 
	 * @param int $roleId Role Id
	 * @throws DatabaseException
	 * @return Array List Application
	 */
	public function getApplicationGroup($roleId) {
		
	}

	/**
	 * Get Applications by role and group
	 * 
	 * @param type $roleId Role Id
	 * @param type $group Group
	 * @throws DatabaseException
	 * @return Array List Application
	 */
	public function getApplications($roleId, $group) {
		
	}

	/**
	 * Delete an Application
	 * 
	 * @param type $app Application
	 * @throws DatabaseException
	 * @return bool True on success or False on failure
	 */
	public function deleteApplication($app) {
		
	}

	/**
	 * Insert an Application
	 * 
	 * @param type $app Application
	 * @throw DatabaseException
	 * @return bool True on success or False on failure
	 */
	public function insertApplication($app) {
		
	}

	/**
	 * Generate a new Id for Application
	 * 
	 * @throw DatabaseException
	 * @return int New Id
	 */
	public function generateIDApp() {
		
	}

	/**
	 * Update Application
	 * 
	 * @throws DatabaseException
	 * @param Array $app Application need to update
	 * @return True on success or False on failure
	 */
	public function updateApplication($app) {
		
	}

	/**
	 * Get an Application by id
	 * 
	 * @param type $id Application Id
	 * @thow DatabaseException
	 * @return Array An Application
	 */
	public function getApplicationByID($id) {
		
	}

}