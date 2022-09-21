<?php
$VERBOSE = False;                       # informational messages to stdout
$USER    = "storage_27811_3";                          # Rackspace Cloud Username
$API_KEY = "3bc8248055bed3a8706b1ed6d9cdfe26";				# Rackspace Cloud API Key
$ACCOUNT = "storage_27811_3";                        # account name
$HOST    = 'https://api.clodo.ru/';                        # authentication host URL

# Allow override by environment variable
if (isset($_ENV["RCLOUD_API_USER"])) {
    $USER = $_ENV["RCLOUD_API_USER"];
}

if (isset($_ENV["RCLOUD_API_KEY"])) {
    $API_KEY = $_ENV["RCLOUD_API_KEY"];
}

if (isset($_ENV["RCLOUD_API_VERBOSE"])) {
    $VERBOSE = $_ENV["RCLOUD_API_VERBOSE"];
}

# Make it global
define('USER', $USER);
define('API_KEY', $API_KEY);
define('ACCOUNT', $ACCOUNT);
define('VERBOSE', $VERBOSE);

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
