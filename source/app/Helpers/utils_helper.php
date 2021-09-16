<?php

/**
 * Extract the domain from an email address.
 *
 * @param string $email
 * @return string
 */
function getDomainFromEmail(string $email): string {
    // make sure we've got a valid email
    if( filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
        // split on @ and return last value of array (the domain)
        $tmp = explode('@', $email);
        $domain = array_pop($tmp);

        return $domain;
    }
}
