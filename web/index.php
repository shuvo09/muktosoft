<?php
	
	//////////////////////////////////////////////////////////////////////////////////////////
	// Project Name: Test Project for Screening Test
	// Developer's Name: Md. Shuvo
	// Developer's Email: shuvo.voboghure007@gmail.com
	// Description: This is just an implementation of some REST APIs 
	///////////////////////////////////////////////////////////////////////////////////////////
	
	class Kitty
	{
		public $urlParts;
		public $route;
		public $question;
		public $outputArray;
		
		public function __construct( $uri, $get )
		{
			$this->urlParts = explode( "?", $uri);
			$this->route = $this->urlParts[0];
			$this->question = $get['q'];
			$this->question = urldecode( $this->question );
		}
		
		
		//////////////////////////////////////////////////////////////////////
		// Function Name: ProcessRouteToGenerateAnaswer
		// Param: void
		// Return: null
		// Description: Just update the value of the outputArray for output
		////////////////////////////////////////////////////////////////////////
		
		public function ProcessRouteToGenerateAnaswer()
		{
			if( $this->route == "/greetings" )
			{
				$this->GenerateGreetingAnswer();
			}
			else if( $this->route == "/qa" )
			{
				$this->GenerateWorldAffairsAnswer();
			}
			else if( $this->route == "/weather" )
			{
				$this->GetWeatherInfoAnswer();
			}
			else
			{
				$this->outputArray['answer'] = 'Please pass a valid question!';
			}
		}
		
		
		
		//////////////////////////////////////////////////////////////////////
		// Function Name: GetAnswer
		// Param: void
		// Return: json encoded output string
		////////////////////////////////////////////////////////////////////////
		
		public function GetAnswer()
		{
			$this->ProcessRouteToGenerateAnaswer();
			return json_encode( $this->outputArray );
		}
		
		
		
		//////////////////////////////////////////////////////////////////////
		// Function Name: GenerateWorldAffairsAnswer
		// Param: void
		// Return: null
		// Description: Take the question, it send CURL GET requestion to API endpoint with necessary data and get the output for the world affairs answer
		////////////////////////////////////////////////////////////////////////
		function GenerateWorldAffairsAnswer()
		{
			$params = array( 
							"question" => urldecode( $this->question )
					  );
			
			$queryResponseObj = $this->GetCurl( "http://quepy.machinalis.com/engine/get_query", $params );
			$queryResponseArray = json_decode( $queryResponseObj, true );
			
			$query = $queryResponseArray['queries'][0]['query'];
			
			if( $query == null )
			{
				return $this->outputArray['answer'] = "Your majesty! Jon Snow knows nothing! So do I!";
			}
			
			$params = array(
							"debug" => "on",
							"format" => "json",
							"timeout" => 0,
							"query" => $query
					  );
			$queryResponseObj = $this->GetCurl( "http://dbpedia.org/sparql", $params );
			$queryResponseArray = json_decode( $queryResponseObj, true );
			
			$bindings = $queryResponseArray['results']['bindings'];	
			
			if( count($bindings) == 0 )
			{
				return $this->outputArray['answer'] = "Your majesty! Jon Snow knows nothing! So do I!";
			}
			
			foreach( $bindings as $aBinding )
			{	
				foreach( $aBinding as $anItem ) // this will be continued for one step only //
				{
					if( $anItem['xml:lang'] == "en" )
					{
						return $this->outputArray['answer'] = $anItem['value'];
					}
				}
			}	
		}
		
		
		
		//////////////////////////////////////////////////////////////////////
		// Function Name: GetWeatherInfoAnswer
		// Param: void
		// Return: null
		// Description: It takes the question and parse it to get the important attribute and call API to show the required weather data 
		////////////////////////////////////////////////////////////////////////
		
		function GetWeatherInfoAnswer()
		{
			$stringParts = explode( "in", $this->question );
			$lastStringPart = $stringParts[count($stringParts)-1];
			$stringParts = explode( "?", $lastStringPart );
			
			$cityName = $stringParts[0];
			
			
			
			$params = array();
			$params['key'] = "c57c6bfca8cdffa9588deb66310db";
			$params['format'] = "json";
			$params['q'] = $cityName;
			
			$responseObj = $this->GetCurl( "http://api.worldweatheronline.com/free/v2/weather.ashx", $params );
			$responseArray = json_decode( $responseObj, true );
			
			$currentWeatherData = $responseArray['data'];
			
			//////// determining the attribute of the question /////////////////
			
			if( (stripos($this->question,'temperature') !== false ) )
			{
				$tempC = $responseArray['data']['current_condition'][0]['temp_C'];
				$tempF = $responseArray['data']['current_condition'][0]['temp_F'];
				
				$this->outputArray['answer'] = "{$tempC} C or {$tempF} F";
			}
			else if( (stripos($this->question,'humidity') !== false ) )
			{
				$this->outputArray['answer'] = $responseArray['data']['current_condition'][0]['humidity'] . "%";
			}
			else if( (stripos($this->question,'pressure') !== false ) )
			{
				$this->outputArray['answer'] = $responseArray['data']['current_condition'][0]['pressure'] . " P";
			}
			else if( (stripos($this->question,'cloud') !== false ) || (strpos($this->question,'rain') !== false ) )
			{
				if( $responseArray['data']['current_condition'][0]['cloudcover'] > 0 )
				{
					$this->outputArray['answer'] = "Yes";
				}
				else
				{
					$this->outputArray['answer'] = "No";
				}
			}
			else
			{
				$this->outputArray['answer'] = "Your majesty! Jon Snow knows nothing! So do I! ";
			}
		}
		
		
		
		//////////////////////////////////////////////////////////////////////
		// Function Name: GenerateGreetingAnswer
		// Param: void
		// Return: null
		// Description: It takes the question and parse it to get the important attribute and build the answer for the grettings to Kitty! 
		////////////////////////////////////////////////////////////////////////
		
		
		public function GenerateGreetingAnswer()
		{
			if( (stripos($this->question,'how') !== false ) )
			{
				$this->outputArray['answer'] = "Hello, Kitty! Its fine, thanks!";
			}
			else if( (stripos($this->question,'your name') !== false ) )
			{
				$this->outputArray['answer'] = "Hello, Kitty! This is Shuvo!";
			}
			else if( (stripos($this->question,'pleasure') !== false ) )
			{
				$this->outputArray['answer'] = "Hello, Kitty! Thank you!";
			}
			else
			{
				$this->outputArray['answer'] = "Hello, Kitty! I do not know!";
			}
		}
		
		
		
		
		//////////////////////////////////////////////////////////////////////
		// Function Name: GetCurl
		// Param: url to call, required parameters for GET request as an array
		// Return: CURL calling response
		// Description: It takes url and generate data for CURL and return the response
		////////////////////////////////////////////////////////////////////////
		
		function GetCurl( $url, $param )
		{
			$queryString = "";
			foreach( $param as $key=>$value )
			{
				$encodedValue = urlencode( $value );
				$queryString .= "&{$key}={$encodedValue}";
			}
			
			$queryString = substr( $queryString, 1, strlen($queryString)-1 );
			
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
			    
			    CURLOPT_RETURNTRANSFER => 1,
			    
			    CURLOPT_URL => "{$url}?{$queryString}",
			    
			    CURLOPT_CONNECTTIMEOUT => 0,
			    
			    CURLOPT_TIMEOUT => 30
			));
			
			$response = curl_exec($curl);
			
			curl_close($curl);
			
			return $response;
		}
	}
	
	$kittyObj = new Kitty( $_SERVER['REQUEST_URI'], $_GET );
	
	echo $kittyObj->GetAnswer();
	
?>