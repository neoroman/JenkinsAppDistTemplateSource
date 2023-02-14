<?php
function json_validate($string) {
    return json_validate2($string, false);
}
function json_validate2($string, $opt) {
    return json_validate3($string, $opt, NULL);
}
function json_validate3($string, $opt, $msg)
{
    // decode the JSON data
    $result = json_decode($string, $opt);

    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            if (isset($msg)) {
                $error = 'Syntax error, malformed JSON. Info:'. $msg;
            } else {
                $error = 'Syntax error, malformed JSON.';
            }
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        // throw the Exception or exit // or whatever :)
        exit($error);
    }

    // everything is OK
    return $result;
}


function unescape_unicode($input)
{
    return preg_replace_callback(
        '/\\\\u([0-9a-fA-F]{4})/',
        function ($match) {
            return mb_convert_encoding(
                pack('H*', $match[1]),
                'UTF-8',
                'UTF-16BE'
            );
        },
        $input
    );
}
?>