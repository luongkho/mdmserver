<?php

namespace Mcenter\DAO;

/**
 * Description of TenantSupportDAO
 *
 * @author hoanvd
 */
class TenantSupportDAO {

	protected $tenantId = '';

	public function __construct($_tenantId) {
		$this->tenantId = $_tenantId;
	}

}