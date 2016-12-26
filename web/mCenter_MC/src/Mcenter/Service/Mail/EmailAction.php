<?php
namespace Mcenter\Service\Mail;

/**
 * Description of EmailAction
 *
 * @author hoanvd
 */
class EmailAction {
	const 
		SEND_USER_REGISTER_REQUEST = 1,
		SEND_USER_REGISTERED_RESPONSE = 2,
		SEND_UPDATE_USER_ROLE_RESPONSE = 3,
		SEND_DEACTIVATE_USER_ROLE_RESPONSE = 4,
		SEND_EXCEPTION_EMAIL = 5,
		REQUEST_OTP_PASSWORD = 6,
		REQUEST_PASSWORD = 7,
		SMS_OTP_REQUEST = 8,
        REQUEST_TOTP_SECRET_TOKEN = 9;
}