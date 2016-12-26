<?php

namespace Mcenter\Service\Mail;

use Mcenter\Service\Mail\EmailAction;

/**
 * EmailContentType cons and config
 *
 */
class EmailContentType {

    const
            TEXT = 0,
            HTML = 1;

    public static function getContentTypeConfig()
    {
        return array(
            EmailAction::REQUEST_TOTP_SECRET_TOKEN => self::HTML
        );
    }

}
