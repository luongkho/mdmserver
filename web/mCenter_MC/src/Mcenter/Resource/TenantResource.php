<?php

namespace Mcenter\Resource;

use Tonic\Resource;

/**
 * Mail resource
 *
 *
 * @uri /tenants
 * @author hoanvd
 */
class TenantResource extends Tonic\Resource {

	/**
	 * 
	 * 
	 * @uri
	 * @method GET
	 * @return Array
	 */
	public function getListTenant() {
		
	}

	/**
	 * 
	 * 
	 * @uri /:id
	 * @method GET
	 * @param string $id
	 * @return Array
	 */
	public function getTenantById($id) {
		
	}

	/**
	 * 
	 * 
	 * @uri /:id
	 * @method POST
	 * @param string $id
	 * @return void
	 */
	public function deleteTenantById($id) {
		
	}

	/**
	 * 
	 * 
	 * @uri
	 * @method POST
	 * @param Array $tenant
	 * @return bool
	 */
	public function addNewTenant($tenant) {
		
	}

	/**
	 * 
	 * 
	 * @uri
	 * @method PUT
	 * @param Array $tenant
	 * @return void
	 */
	public function updateTenant($tenant) {
		
	}

}