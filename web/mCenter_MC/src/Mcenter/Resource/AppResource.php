<?php

namespace Mcenter\Resource;

use Tonic\Resource;

/**
 * App resource
 *
 *
 * @uri /app
 * @author hoanvd
 */
class AppResource extends \Tonic\Resource {

	/**
	 * 
	 * 
	 * @uri /appGroups
	 * @method GET
	 * @param  int $roleId
	 * @param  str $tenantId
	 * @return Array
	 */
	public function getApplicationGroups($roleId, $tenantId) {
		
	}

	/**
	 * 
	 * @uri /appList
	 * @method GET
	 * @param  int $roleId
	 * @param  int $groupId
	 * @return Array
	 */
	public function getApplications($roleId, $groupId, $tenantId) {
		
	}

	/**
	 * 
	 * @uri /:id/:tenantId
	 * @method POST
	 * @param  int $id
	 * @return void
	 */
	public function deleteTenantById($id, $tenantId) {
		
	}

	/**
	 * 
	 * @uri /:roleId
	 * @method POST
	 * @param  array $app
	 * @param int $roleId Description
	 * @return bool
	 */
	public function addNewApplication($app, $roleId) {
		
	}

	/**
	 * 
	 * @uri /generateid/:tenantId
	 * @method GET
	 * @param string $tenantId
	 * @return int
	 */
	public function generateApplicatinID($tenantId) {
		
	}

	/**
	 * 
	 * @method PUT
	 * @param array $app
	 * @return void
	 */
	public function updateApplication($app) {
		
	}

	/**
	 * @uri /:id
	 * @method GET
	 * @param int $id
	 * @return array
	 */
	public function getAppliCationById($id) {
		
	}

}
