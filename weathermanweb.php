<?php
/*
web server REST API for the WeatherMan class

License:  AGPL v3 https://www.gnu.org/licenses/agpl-3.0.html
Author and copyright: sikes@lo-res.org
*/

// set json header
header('Content-type: application/json; charset=utf-8');

// make sure we have all necessary input first
if(
        !$_GET['country'] && !$_GET['cityname'] && !$_GET['UNLOCODE']
)
{
        // print an error otherwise
        echo json_encode(
                [
                        'error_code' => 412,
                        'error_message' => 'Please supply cityname and/or country or UNLOCODE' 
                ]
        );

        // we are done
        exit;
}


// include the class file
require_once('WeatherMan.php');

// instantiate class
$weatherman = new WeatherMan(['openweatherappkey'] => <openweathermap.org API KEY>);

// attemto tp get weathe data
$weather = $weatherman->gettemperature($_GET);

// something went wrong
if(!$weather)
        // print error
        echo json_encode(
                [
                        'error_code' => 412,
                        'error_message' => $weatherman->error 
                ]
        );
// all is good
else
        // print output
        echo json_encode($weather);
