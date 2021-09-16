<?php

/**
 * Validate a professor by url provided.
 *
 * @param string @web_url
 * @param string @email
 * @param string @first_name
 * @param string @last_name
 * @return bool
 */
function validate_professor(string $web_url, string $email, string $first_name, string $last_name): bool
{
    // Get content and check professor's email
    $prof_email = htmlentities($email);
    $prof_web = htmlentities($web_url);
    $prof_data = @file_get_contents($prof_web);
    preg_match_all("/($prof_email)/imx", $prof_data, $data_matches);
    $num_matches_data = sizeof($data_matches[0]);

    // Check if a word "professor" exists
    $keyword_no1 = 'professor';
    preg_match_all("/($keyword_no1)/imx", $prof_data, $data_matches);
    $num_matches_keyword_no1 = sizeof($data_matches[0]);

    // Check if a word "faculty" exists
    $keyword_no2 = 'faculty';
    preg_match_all("/($keyword_no2)/imx", $prof_data, $data_matches);
    $num_matches_keyword_no2 = sizeof($data_matches[0]);

    // Check if first name exists
    $prof_first = htmlentities($first_name);
    $keyword_no3 = $prof_first;
    preg_match_all("/($keyword_no3)/imx", $prof_data, $data_matches);
    $num_matches_keyword_no3 = sizeof($data_matches[0]);

    // Check if last name exists
    $prof_last = htmlentities($last_name);
    $keyword_no4 = $prof_last;
    preg_match_all("/($keyword_no4)/imx", $prof_data, $data_matches);
    $num_matches_keyword_no4 = sizeof($data_matches[0]);

    // Check if email is valid
    $prof_email = htmlentities($email);
    $prof_email_trim = substr($prof_email, 0, stripos($prof_email, ".edu"));
    $prof_email_trim = substr($prof_email_trim, strpos($prof_email_trim, "@") + 1);

    if ( isset($num_matches_data) && isset($num_matches_keyword_no1) && isset($num_matches_keyword_no2) && isset($num_matches_keyword_no3) && isset($num_matches_keyword_no4) && isset($prof_email_trim) ) {
        if ($num_matches_data == 0) {
            return false;
        }
        if ( $num_matches_keyword_no1 == 0) {
            return false;
        }
        if ( $num_matches_keyword_no2 == 0) {
            return false;
        }
        if ( $num_matches_keyword_no3 == 0) {
            return false;
        }
        if ( $num_matches_keyword_no4 == 0) {
            return false;
        }

        return true;
    }

    return false;
}