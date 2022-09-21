<?php
if (empty($_ENV)) {
    $_ENV = $_SERVER;
}

require_once 'cloudfiles_ini.php';
set_include_path(get_include_path() . PATH_SEPARATOR . "../");
require_once 'cloudfiles.php';

error_reporting(E_ALL);

function read_callback_test($bytes)
{
    if (VERBOSE)
        print "=> read_callback_test: transferred " . $bytes . " bytes\n";
}

function write_callback_test($bytes)
{
    if (VERBOSE)
        print "=> write_callback_test: transferred " . $bytes . " bytes\n";
}

# common test utility functions
#

# re-implementation of PECL's http_date
#
function httpDate($ts=NULL)
{
    if (!$ts) {
        return gmdate("D, j M Y h:i:s T");
    } else {
        return gmdate("D, j M Y h:i:s T", $ts);
    }
}


# Specify a word length and any characters to exlude and return
# a valid UTF-8 string (within the ASCII range)
#
function genUTF8($len=10, $excludes=array())
{
    $r = "";
    while (strlen($r) < $len) {
        $c = rand(32,127); # chr() only works with ASCII (0-127)
        if (in_array($c, $excludes)) { continue; }
        $r .= chr($c); # chr() only works with ASCII (0-127)
    }
    return utf8_encode($r);
}

# generate a big string
#
function big_string($length)
{
    $r = array();
    for ($i=0; $i < $length; $i++) {
        $r[] = "a";
    }
    return join("", $r);
}

# To be used with $UTF8_TEXT return the len of $length_string of char
#  contained in $utf8_array
#
function random_utf8_string($length_string, $utf8_array)
{
    $bigtext = "";
    $random_string = "";

    foreach( $utf8_array as $lang => $text )
    {
        $bigtext .= $text;
    }

    for ($i = 0; $i < $length_string; $i++)
    {
        $random_pick = mt_rand(1, strlen($bigtext));
        $random_char=NULL;
        $random_char = trim($bigtext[$random_pick-1]);
        $random_string .= $random_char;
    }
    return utf8_encode($random_string);
}


function debug($texto){
    file_put_contents('/tmp/quick-cf-api.log',date('d/m/Y H:i:s').' - '.$texto."\n",FILE_APPEND);
}

/**
   * Get the temporary directory abstracted of the OS
   *
   */
function get_tmpdir() {
    if (isset($_ENV['TMP']))
        return realpath($_ENV['TMP']);
    if (isset($_ENV['TMPDIR']))
        return realpath( $_ENV['TMPDIR']);
    if (isset($_ENV['TEMP']))
        return realpath( $_ENV['TEMP']);

    $tempfile=tempnam(uniqid(rand(),TRUE),'');
    if (file_exists($tempfile)) 
        unlink($tempfile);
    return realpath(dirname($tempfile));
}

?>
