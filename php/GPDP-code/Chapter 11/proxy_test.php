<?php
error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';


class GlobalWeather {
	private $client;
	
	private function lazyLoad() {
		if (! $this->client instanceof SoapClient) {
			$this->client = new SoapClient('http://live.capescience.com/wsdl/GlobalWeather.wsdl'
		}
	}
	
	// 'Station getStation(string $code)',
	public function getStation($code) {
		$this->lazyLoad();
		return $this->client->getStation($code);
	}
	
	private function client() {
		if (! $this->client instanceof SoapClient) {
			$this->client = new SoapClient('http://live.capescience.com/wsdl/GlobalWeather.wsdl'
		}
		return $this->client;
	}
	
	// 'boolean isValidCode(string $code)
	public function isValidCode($code) {
		return $this->client()->isValidCode($code);
	}
	// 'ArrayOfstring listCountries()
	public function listCountries() {
		return $this->client()->listCountries();
	}
	// 'ArrayOfStation searchByCode(string $code)
	public function searchByCode($code) {
		return $this->client()->searchByCode($code);
	}
	// 'ArrayOfStation searchByCountry(string $country)
	public function searchByCountry($country) {
		return $this->client()->searchByCountry($country);
	}
	// 'ArrayOfStation searchByName(string $name)
	public function searchByName($name) {
		return $this->client()->searchByName($name);
	}
	// 'ArrayOfStation searchByRegion(string $region)
	public function searchByRegion($region) {
		return $this->client()->searchByRegion($region);
	}
	// 'WeatherReport getWeatherReport(string $code)
	public function getWeatherReport($code) {
		return $this->client()->getWeatherReport($code);
	}

}


class ProxyTestCase extends UnitTestCase {
	const WSDL = 'http://live.capescience.com/wsdl/GlobalWeather.wsdl';
	private $client;
	function setUp() {
		$this->client = new SoapClient(ProxyTestCase::WSDL);
	}
	function TestClassOfSoapClient() {
		$this->assertEqual('SoapClient', get_class($this->client));
	}
	function TestMethodsOfSoapClient() {
		$soap_client_methods = array(
			'__construct',
			'__call',
			'__soapCall',
			'__getLastRequest',
			'__getLastResponse',
			'__getLastRequestHeaders',
			'__getLastResponseHeaders',
			'__getFunctions',
			'__getTypes',
			'__doRequest');
		$this->assertEqual(
			$soap_client_methods, 
			get_class_methods(get_class($this->client)));
	}
	function TestSoapFunctions() {
		$globalweather_functions = array(
			'Station getStation(string $code)',
			'boolean isValidCode(string $code)',
			'ArrayOfstring listCountries()',
			'ArrayOfStation searchByCode(string $code)',
			'ArrayOfStation searchByCountry(string $country)',
			'ArrayOfStation searchByName(string $name)',
			'ArrayOfStation searchByRegion(string $region)',
			'WeatherReport getWeatherReport(string $code)'
			);
		$this->assertEqual(
			$globalweather_functions,
			$this->client->__getFunctions());
	}
	function TestSearchByName() {
		$moline_test_info = array (
			 'icao' => 'KMLI'
			,'wmo' => 72544
			,'iata' => ''
			,'elevation' => 179
			,'latitude' => 41.451
			,'longitude' => -90.515
			,'name' => 'Moline, Quad-City Airport'
			,'region' => 'IL'
			,'country' => 'United States'
			,'string' => "KMLI - Moline, Quad-City Airport, IL, United States @ 41.451'N -90.515'W 179m"
			);
		$moline_info = $this->client->searchByName('Moline');
		$moline = $moline_info[0];
		foreach($moline_test_info as $key => $value) {
			$this->assertEqual($moline->$key, $value);
		}
	}
	function TestGetWeatherReport() {
		$moline_weather = $this->client->getWeatherReport('KMLI');
		$this->assertIsA($moline_weather, 'stdClass');
		$weather_tests = array(
			 'timestamp' => 'String'
			,'station' => 'stdClass'
			,'phenomena' => 'Array'			
			,'precipitation' => 'Array'
			,'extremes' => 'Array'
			,'pressure' => 'stdClass'
			,'sky' => 'stdClass'
			,'temperature' => 'stdClass'
			,'visibility' => 'stdClass'
			,'wind' => 'stdClass'
			);
		foreach($weather_tests as $key => $isa) {
			$this->assertIsA($moline_weather->$key, $isa, "$key should be $isa, actually [%s]");
		}
		
		$temp = $moline_weather->temperature;
		$temperature_tests = array(
			 'ambient' => 'Float'
			,'dewpoint' => 'Float'
			,'relative_humidity' => 'Integer'
			,'string' => 'String'
			);
		foreach($temperature_tests as $key => $isa) {
			$this->assertIsA($temp->$key, $isa, "$key should be $isa, actually [%s]");
		}			
	}
}

class GlobalWeatherTestCase extends UnitTestCase {
	private $client;
	function setUp() {
		$this->client = new GlobalWeather;
	}
	function TestNewGlobalWeatherContainsNoSoapClient() {
		$this->assertWantedPattern('/private \$client = NULL/i',
			var_export($this->client,true));
	}
	function TestSearchByName() {
		$moline_test_info = array (
			 'icao' => 'KMLI'
			,'wmo' => 72544
			,'iata' => ''
			,'elevation' => 179
			,'latitude' => 41.451
			,'longitude' => -90.515
			,'name' => 'Moline, Quad-City Airport'
			,'region' => 'IL'
			,'country' => 'United States'
			,'string' => "KMLI - Moline, Quad-City Airport, IL, United States @ 41.451'N -90.515'W 179m"
			);
		$moline_info = $this->client->searchByName('Moline');
		$moline = $moline_info[0];
		foreach($moline_test_info as $key => $value) {
			$this->assertEqual($moline->$key, $value);
		}
	}
	function TestGetWeatherReport() {
		$moline_weather = $this->client->getWeatherReport('KMLI');
		$this->assertIsA($moline_weather, 'stdClass');
		$weather_tests = array(
			 'timestamp' => 'String'
			,'station' => 'stdClass'
			,'phenomena' => 'Array'			
			,'precipitation' => 'Array'
			,'extremes' => 'Array'
			,'pressure' => 'stdClass'
			,'sky' => 'stdClass'
			,'temperature' => 'stdClass'
			,'visibility' => 'stdClass'
			,'wind' => 'stdClass'
			);
		foreach($weather_tests as $key => $isa) {
			$this->assertIsA($moline_weather->$key, $isa, "$key should be $isa, actually [%s]");
		}
		
		$temp = $moline_weather->temperature;
		$temperature_tests = array(
			 'ambient' => 'Float'
			,'dewpoint' => 'Float'
			,'relative_humidity' => 'Integer'
			,'string' => 'String'
			);
		foreach($temperature_tests as $key => $isa) {
			$this->assertIsA($temp->$key, $isa, "$key should be $isa, actually [%s]");
		}			
	}
}

class GenericProxy {
	protected $subject;
	public function __construct($subject) {
		$this->subject = $subject;
	}
	public function __call($method, $args) {
        return call_user_func_array(
        	array($this->subject, $method),
        	$args);
	}
}

$test = new GroupTest('Proxy PHP5 Unit Test');
$test->addTestCase(new ProxyTestCase);
$test->addTestCase(new GlobalWeatherTestCase);

if (TextReporter::inCli()) {
	exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());

