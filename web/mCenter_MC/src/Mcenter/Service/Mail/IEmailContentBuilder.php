<?php
namespace Mcenter\Service\Mail;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author hoanvd
 */
interface IEmailContentBuilder {
	public function buildContent($emailAction, $modelMap, $tenantId);
}
