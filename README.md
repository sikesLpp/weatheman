# Weatherman
A proxy REST API for retrieving weather data from https://api.openweathermap.org ( POP demo)

# The WeatherMan Class
Class containing functionality to retrieve weather data from https://api.openweathermap.org

The class is context neutral, the constructor and the data function require a PHP associate config array.
Likewise, the data function will return an associative result array or false on failure, dumping error information the class instace error member.

The constructor requires a valid API key for https://api.openweathermap.org as an named member in the options array

The Class uses the  https://api.openweathermap.org Geocoding API for citynames, the https://countrycode.dev API to figure out a country's capital, and the https://port-api.com/ API for UNLOCODES

Usage:

        // include this class file
        require_once('WeatherMan.php');

        // instantiate class providing a valid openweathermap.org API KEY
        $weatherman = new WeatherMan(['openweatherappkey'] => <openweathermap.org API KEY>);

        // get weatherdata with parameters
        $weatherdata = $weatherman->gettemperature(
                [
                    'cityname' => <city name as known by openweathermap.org>   
                    'country' => <iso2 country code as know to countrycode.dev>,
                    'UNLOCODE' => <UNLOCODE as know to port-api.com>
                ]
        );

        // note:
        // cityname ONLY will yield data for city
        // OR
        // cityname AND countryname will yield data for city in country 
        // OR 
        // countryname ONLY will yield data for the capital of the coubtry
        // OR
        // UNLOCODE ONLY will yield data for lola corresponding to the UNLOCODE

Output on

success:

        [
                'temperature' => 
                [
                        'kelvin': <temperature in degrees kelvin>,
                        'celsius': <temperature in degrees celsius>,
                        'fahrenheit': <temperature in degrees fahrenheit>,
                ],
                'rawdata' => <full data object from openweathermap.org>

        ] 

failure:
        false
        error message in $weatherman->erroropenweathermap.org API KEY

License:  AGPL v3 https://www.gnu.org/licenses/agpl-3.0.html
Author and copyright: sikes@lo-res.org 


# the weathermanweb.php server script

This script makes usage makes usage of the WeatherMan class, takes the necessary parameters in the GET string and provided a JSON content typed output as REST API output

# the weathermancli.php command line script

This script makes usage of the WeatherMan class and takes the necessary parameters in the get string and dumps an PHP associative data array as a formatted string to the command line 
