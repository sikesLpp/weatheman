#!/usr/bin/php
<?php
/*
command line for the WeatherMan class

License:  GPL v3 https://www.gnu.org/licenses/gpl-3.0.html
Author and copyright: sikes@lo-res.org
*/

// turn off nottices and warnings
error_reporting(E_ERROR | E_PARSE);

// get comman line options
$commandlineoptions = getopt("c:C:U:");

// make sure we have all necessary input first
if(
        !$commandlineoptions['c'] && !$commandlineoptions['C'] && !$commandlineoptions['U']
)
{
        // print an error otherwise
        print_r(
                [
                        'error_code' => 412,
                        'error_message' => 'Please supply cityname(C) and/or country(c) or UNLOCODE(U)' 
                ]
        );

        // we are done
        exit;
}


// include this class file
require_once('WeatherMan.php');

// instantiate class
$weatherman = new WeatherMan(['openweatherappkey'] => <openweathermap.org API KEY>);

// attemto tp get weathe data
$weather = $weatherman->gettemperature(['cityname' => $commandlineoptions['C'], 'country' => $commandlineoptions['c'], 'UNLOCODE' => $commandlineoptions['U']]);

// something went wrong
if(!$weather)
        print_r(
                [
                        'error_code' => 412,
                        'error_message' => $weatherman->error 
                ]
        );
// all is good
else
        // print output
        print_r($weather);
