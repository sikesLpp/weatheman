<?php
/*
Class containing functionality to retieve weather data from https://api.openweathermap.org
Usage:
        // include this class file
        require_once('WeatherMan.php');

        // instantiate class
        $weatherman = new WeatherMan(['openweatherappkey'] => <openweathermap.org API KEY>);

        // get weatherdata with parameters
        $weatherdata = $weatherman->gettemperature(
                'cityname' => <city name as known by openweathermap.org>   
                'country' => <iso2 country code as know to countrycode.dev>,
                'UNLOCODE' => <UNLOCODE as know to ort-api.com>
        );

        // note:
        // cityname ONLY will yield data for city
        // OR
        // cityname AND countryname will yield data for city in country 
        // OR 
        // countryname ONLY will yield data for the capital of the coubtry
        // OR
        // UNLOCODE ONLY will yield data for lola correxponding to UNLOCODE

Output on

success:

[
        'temperature' => 
        [
                'kelvin': <temperature in degrees kelvin>,
                'celsius': <temperature in degrees celsius>,
                'fahrenheit': <temperature in degrees fahrenheit>,
        ],
        'rawdata' => <full data object from >

] 

failure
        false
        error message in $weatherman->error

License:  AGPL v3 https://www.gnu.org/licenses/agpl-3.0.html
Author and copyright: sikes@lo-res.org 


*/





class WeatherMan
{
        // the constructor
        public function __construct($options)
        {
                // if an options array is passed
                if (is_array($options)) {
                        // loop through it
                        foreach ($options as $key => $value) 
                        {
                                // set local value
                                $this->$key = $value;
        
                        }
                }
        }

        // a function to get the temperature for a place
        public function gettemperature($parameters=NULL)
        {
                // if no params are give
                if(!$parameters)
                {
                        // set an error
                        $this->error = "No parameters";

                        // return false;
                        return false;
                }

                // depending on what parameters are set
                // only city
                if($parameters['cityname'])
                {
                        // q param array
                        $q[] = $parameters['cityname'];

                        // state (us stuff)
                        if(strtoupper($parameters['country']) == 'US' && $parameters['state'])
                                // add state
                                 $q[] = $parameters['state'];


                        // country
                        if($parameters['country'])
                                // add country
                                $q[] = $parameters['country'];

                        // implode q
                        $q = implode(',',$q);
                }
                // country
                else if ($parameters['country'])
                {
                        // attempt to get country data
                        $countryresponse = $this->curlcall('https://countrycode.dev/api/countries/iso2/' . strtoupper($parameters['country']));

                        // if there is a connection problem
                        if($countryresponse['info']['http_code'] != '200')
                        {
                                // set an error
                                $this->error = "Could not connect to countrycode.dev at https://countrycode.dev/api/countries/iso2/" . strtoupper($parameters['country']);

                                // return false
                                return false;
                        }


                        // if the country API return an application error
                        if(!$countryresponse['output'][0]['capital_city'])
                        {
                                // set an error
                                $this->error = "countrycode.dev error: " . $weatherresponse['output']['detail'];

                                // return false
                                return false;
                        }

                        // q param array
                        $q[] = $countryresponse['output'][0]['capital_city'];


                        // country
                        if($parameters['country'])
                                // add country
                                $q[] = strtoupper($parameters['country']);

                        // implode q
                        $q = implode(',',$q);
                }
                // if we have a UNLOCODE
                else if($parameters['UNLOCODE'])
                {
                        // attempt to get data for the code
                        $coderesponse = $this->curlcall('https://port-api.com/unlocode/code/' . strtoupper($parameters['UNLOCODE']));

                        // if there is a connection problem
                        if($coderesponse['info']['http_code'] != '200')
                        {
                                // set an error
                                $this->error = "Could not connect to port-api.com at https://port-api.com/unlocode/code/" . strtoupper($parameters['UNLOCODE']);

                                // return false
                                return false;
                        }


                        // if the country API return an application error
                        if(!$coderesponse['output']['geometry']['coordinates'])
                        {
                                // set an error
                                $this->error = "port-api.com error: location " . strtoupper($parameters['UNLOCODE']) . " not found";

                                // return false
                                return false;
                        }

                        // lom
                        $lon = $coderesponse['output']['geometry']['coordinates'][0];

                        // lat
                        $lat = $coderesponse['output']['geometry']['coordinates'][1];

                }


                // The url
                $openweatherurl = "https://api.openweathermap.org/data/2.5/weather?appid=" . $this->openweatherappkey;

                // if we have a $q
                if($q)
                        // use it
                        $openweatherurl .= '&q=' . $q;
                // or a lola
                else if(isset($lon) && isset($lat))
                        // use it
                        $openweatherurl .= '&lat='.$lat.'&lon=' . $lon;

                // response from the weather guys
                $weatherresponse = $this->curlcall($openweatherurl);

                // if there is a connection problem
                if($weatherresponse['info']['http_code'] != '200')
                {
                        // set an error
                        $this->error = "Could not connect to api.openweathermap.org at https://api.openweathermap.org/data/2.5/weather?q=".$q;

                        // return false
                        return false;
                }


                // if the weather types return an application error
                if($weatherresponse['output']['cod'] != 200)
                {
                        // set an error
                        $this->error = "api.openweathermap.org error: " . $weatherresponse['output']['cod'] ." " . $weatherresponse['output']['message'];

                        // return false
                        return false;
                }


                // assemble output object then
                $weatherdata = [
                        'temperature' => 
                        [
                                'kelvin' => $weatherresponse['output']['main']['temp'],
                                'celsius' => $this->kelvintocelsius($weatherresponse['output']['main']['temp']),
                                'fahrenheit' => $this->kelvintofahrenheit($weatherresponse['output']['main']['temp'])
                        ],
                        'rawdata' => $weatherresponse['output']
                ];

                // return weatherdata output
                return $weatherdata;
        }





        // a function to do a curl call
        public function curlcall($url=NULL,$data=false,$nowait=false, $extraheaders=null, $rawoutput=false)
        {
                // if we dont have a cuyrl object already
                $this->curlobject = curl_init();

                // set the url
                curl_setopt($this->curlobject, CURLOPT_URL, $url);

                // we want an answer
                curl_setopt($this->curlobject, CURLOPT_RETURNTRANSFER, 1);

                // if data is set, it's post
                if($data)
                {
                        // it's type post
                        curl_setopt($this->curlobject, CURLOPT_POST, true);

                        // if data is an array
                        if(is_array($data))
                                // convert to json string 
                                $data = json_encode($data);

                        // put the  $inputdataarray into the post fields
                        curl_setopt($this->curlobject, CURLOPT_POSTFIELDS, $data);

                        // the headers we will pass
                        $headers = [
                                'Content-Type: application/json',                                                                                
                                'Content-Length: ' . strlen($data)                                                                       
                        ];

                        // if extra headers are passed
                        if($extraheaders)
                                // add them to the headers array
                                $headers = array_merge($headers, $extraheaders);


                        // custom header including data length                                                                      
                        curl_setopt($this->curlobject, CURLOPT_HTTPHEADER, $headers);
                }

                // if it's a call we will not need to output of
                if($nowait)
                {
                        // we will barely (= not) wait for the output
                        curl_setopt($this->curlobject, CURLOPT_TIMEOUT_MS, 45);

                        // also not signal this process
                        curl_setopt($this->curlobject, CURLOPT_NOSIGNAL, 1);

                        // execute the transfer
                        curl_exec($this->curlobject);

                        // done
                        return true;
                }



                // execute the query and cature the output
                if($rawoutput)
                        $output = curl_exec($this->curlobject);
                else
                        $output = json_decode(curl_exec($this->curlobject),true);

                // and the info
                $info = curl_getinfo($this->curlobject);

                // close the connection
                curl_close($this->curlobject);

                // return the resultat
                return array(
                        'info' => $info,
                        'output' => $output
                );


        }

        // measuremnt conversion 
        public function kelvintofahrenheit($kelvin)
        {
                // convert and return
                return (($kelvin - 273.15) * 1.8) + 32;
        }

        // measuremnt conversion 
        public function kelvintocelsius($kelvin)
        {
                // convert and return
                return ($kelvin - 273.15);
        }


}
