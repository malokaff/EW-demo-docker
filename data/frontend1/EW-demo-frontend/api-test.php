<?php
include('config.php');
$pagename = "api-test.php";
$curl = curl_init();


function sendApiRequest($url, $headers, $body, $cookieFile, $method) {
	global $curl;

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $method,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_POSTFIELDS => $body,
		CURLOPT_COOKIEFILE => $cookieFile,
		CURLOPT_COOKIEJAR => $cookieFile,
		CURLOPT_HEADER => true
	));

	$response = curl_exec($curl);

	if (curl_errno($curl)) {
		echo curl_error($curl);
		die();
	}

	$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	if ($http_code == intval(200)) {
		return $response;
	} else {
		return "Ressource introuvable : " . $http_code . $response;
	}

	curl_close($curl);
}

//function to check if PSM is available
function urlExists($url=NULL)  
{  
    if($url == NULL) return false;  
    $ch = curl_init($url);  
    curl_setopt_array($ch, array(
		CURLOPT_TIMEOUT => 5,  
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
		));
    $data = curl_exec($ch);  
	if (curl_errno($ch)) {
		echo curl_error($ch);
		die();
	}
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);  
    curl_close($ch);  
    return $httpcode >= 200 && $httpcode < 300;
}  
//.$ip_psm
if(urlExists('https://'.$ip_psm)== false) {
		echo 'no psm at https://'.$ip_psm;
		echo urlExists('https://'.$ip_psm);
		header("Refresh:0,url=no-psm.php");
}
//API Authentication:
//authent API PSM
//API Authentication:
$url = 'https://'.$ip_psm.'/v1/login';
$headers = array('Content-Type: text/plain');
$body = '{"username": "'.$usr_PSM.'","password": "'.$pwd_PSM.'","tenant": "default"}';
$response = sendApiRequest($url,$headers,$body,'./cookie.txt','POST');
//parse cookie info
preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
$cookies = array();
foreach($matches[1] as $item) {
	parse_str($item, $cookie);
	$cookies = array_merge($cookies, $cookie);
	}

//2nd request to get rule
$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/'.$policy_PSM;
$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
$body = '{"username": "'.$usr_PSM.'","password": "'.$pwd_PSM.'","tenant": "default"}';
$response = sendApiRequest($url,$headers,$body,'./cookie.txt','GET');
//echo $response;
//print_r($headers);
// Your HTTP response containing JSON
$httpResponse = $response;

// Use regular expressions to extract JSON content from the response
$pattern = '/\{.*\}/s'; // Pattern to match JSON data
preg_match($pattern, $httpResponse, $matches);

if (!empty($matches)) {
	$jsonContent = $matches[0];
	// Now $jsonContent contains the extracted JSON
	$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
	// Access and work with the extracted JSON data
	//var_dump($decodedJson);
} else {
	echo "No JSON found in the HTTP response.";
}
// Assuming $jsonContent holds your extracted JSON
// Decode JSON to PHP array
$decodedJson = json_decode($jsonContent, true);

if ($decodedJson && isset($decodedJson['spec']['rules'])) {
	$rules = $decodedJson['spec']['rules'];
	} else {
		echo "<br>No 'rules' found in the JSON content.";
			}

//control if rules already exist:
//go through the table and check if rule name exist
foreach ($decodedJson['spec']['rules'] as $key => $value) {
	if ($value['name'] === "autoblock_mqtt") {
		$MqttInPlace = true; }
	if ($value['name'] === "autoblock_mysql") {
		$MysqlInPlace = true; }
}

if($_GET['action'] == 'postMQTT')
{
	if($_POST['checkMQTT']=='on')
	{
	// add new rule into json
	$newRule = [
		"proto-ports" => [
			[
				"protocol" => "TCP",
				"ports" => "1883"
			]
		],
		"action" => "deny",
		"from-ip-addresses" => [$ip_backend],
		"to-ip-addresses" => [$ip_mqttbroker],
		"name" => "autoblock_mqtt"
	];

	// Check if the 'rules' key exists in the JSON structure
	if (isset($decodedJson['spec']['rules'])) {
		// Add the new rule to the beginning of the existing rules array
		array_unshift($decodedJson['spec']['rules'], $newRule);
		} 
		else {
		// If the 'rules' key doesn't exist, create it and add the new rule
		$decodedJson['spec']['rules'] = [$newRule];
		}

	// Convertir le tableau PHP modifié en JSON
	$updatedJson = json_encode($decodedJson, JSON_PRETTY_PRINT);

	//Request to add rule
	$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/'.$policy_PSM;
	$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
	$body=$updatedJson;
	
	$httpResponse = sendApiRequest($url,$headers,$body,'./cookie.txt','PUT');
	//echo $response;
	
	//collect status of the answer
	$pattern = '/\{.*\}/s'; // Pattern to match JSON data
	preg_match($pattern, $httpResponse, $matches);

	if (!empty($matches)) {
		$jsonContent = $matches[0];
		// Now $jsonContent contains the extracted JSON
		$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
	// Check if the 'status' key and 'propagation-status' key exist
		if (isset($decodedJson['status']['propagation-status'])) {
			$propagationStatus = $decodedJson['status']['propagation-status'];
			
			// Accessing specific fields within propagation-status
			$generationId = $propagationStatus['generation-id'];
			$updated = $propagationStatus['updated'];
			$pending = $propagationStatus['pending'];
			$status = $propagationStatus['status'];
			
			// Output or use the propagation status values as needed
			echo "Generation ID: " . $generationId . "<br>";
			echo "Updated: " . $updated . "<br>";
			echo "Pending: " . $pending . "<br>";
			echo "Status: " . $status . "<br>";
			} 
			else {
				echo "No 'propagation-status' found in the JSON content.<br>";
				//echo $httpResponse;
				$result = $decodedJson['result'];
				$str = $result['Str'];
				$messages = $decodedJson['message'];
				 // Access the 'Str' value
				echo '<b><font color=red>';
				echo "Str: " . $str . "<br>";

				// Access the 'message' array
				echo "Messages: <br>";
				foreach ($messages as $message) {
					echo "- " . $message . "<br>";
					}
				echo '</font></b>';
			}		
		} 
		else {
			echo "No JSON found in the HTTP response.";	
			}

		header("Refresh:0,url=$pagename");

	}
	else
	{
		//remove rule 
		echo "we remove the rule";
		//first need to collect the rule again
		$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/'.$policy_PSM;
		$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
		$body = '{"username": "'.$usr_PSM.'","password": "'.$pwd_PSM.'","tenant": "default"}';
		$response = sendApiRequest($url,$headers,$body,'./cookie.txt','GET');
		// Your HTTP response containing JSON
		$httpResponse = $response;

		// Use regular expressions to extract JSON content from the response
		$pattern = '/\{.*\}/s'; // Pattern to match JSON data
		preg_match($pattern, $httpResponse, $matches);

		if (!empty($matches)) {
			$jsonContent = $matches[0];
			// Now $jsonContent contains the extracted JSON
			$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
			// Access and work with the extracted JSON data
			//var_dump($decodedJson);
		} else {
			echo "No JSON found in the HTTP response.";
		}
		// Assuming $jsonContent holds your extracted JSON
		// Decode JSON to PHP array
		$decodedJson = json_decode($jsonContent, true);
		if ($decodedJson && isset($decodedJson['spec']['rules'])) {
			$rules = $decodedJson['spec']['rules'];
			} else {
				echo "<br>No 'rules' found in the JSON content.";
					}
		//go through the table and delete the rule name autoblock_mqtt
		foreach ($decodedJson['spec']['rules'] as $key => $value) {
			if ($value["name"] == "autoblock_mqtt") {
				// Supprimer l'élément du tableau
				echo "<br><b>we enter in unset</b><br>";
				//unset($decodedJson['spec']['rules'][$key]);
				array_splice($decodedJson['spec']['rules'], $key, 1);
			}
		//$decodedJson = array_values($decodedJson['spec']['rules']);
		}
		
		//Request to remove rule (update policy)
		// convert PHP table into JSON
		$updatedJson = json_encode($decodedJson, JSON_PRETTY_PRINT);
		$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/'.$policy_PSM;
		$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
		$body=$updatedJson;
		
		$httpResponse = sendApiRequest($url,$headers,$body,'./cookie.txt','PUT');
		//echo $response;
		
		//collect status of the answer
		$pattern = '/\{.*\}/s'; // Pattern to match JSON data
		preg_match($pattern, $httpResponse, $matches);

		if (!empty($matches)) {
			$jsonContent = $matches[0];
			// Now $jsonContent contains the extracted JSON
			$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
		// Check if the 'status' key and 'propagation-status' key exist
			if (isset($decodedJson['status']['propagation-status'])) {
				$propagationStatus = $decodedJson['status']['propagation-status'];
				
				// Accessing specific fields within propagation-status
				$generationId = $propagationStatus['generation-id'];
				$updated = $propagationStatus['updated'];
				$pending = $propagationStatus['pending'];
				$status = $propagationStatus['status'];
				
				// Output or use the propagation status values as needed
				echo "Generation ID: " . $generationId . "<br>";
				echo "Updated: " . $updated . "<br>";
				echo "Pending: " . $pending . "<br>";
				echo "Status: " . $status . "<br>";
				} 
				else {
					echo "No 'propagation-status' found in the JSON content.<br>";
					//echo $httpResponse;
					$result = $decodedJson['result'];
					$str = $result['Str'];
					$messages = $decodedJson['message'];
					 // Access the 'Str' value
					echo '<b><font color=red>';
					echo "Str: " . $str . "<br>";

					// Access the 'message' array
					echo "Messages: <br>";
					foreach ($messages as $message) {
						echo "- " . $message . "<br>";
						}
					echo '</font></b>';
				}		
			} 
			else {
				echo "No JSON found in the HTTP response.";	
				}
		
	header("Refresh:0,url=$pagename");
	}
}

//control form mysql
if($_GET['action']== 'postMYSQL') {
	if($_POST['checkMYSQL']=='on'){
	// add new rule into json
	$newRule = [
		"proto-ports" => [
			[
				"protocol" => "TCP",
				"ports" => "3306"
			]
		],
		"action" => "deny",
		"from-ip-addresses" => [$ip_backend],
		"to-ip-addresses" => [$ip_mqttbroker],
		"name" => "autoblock_mysql"
	];
	print_r($newRule);
	// Check if the 'rules' key exists in the JSON structure
	if (isset($decodedJson['spec']['rules'])) {
		// Add the new rule to the beginning of the existing rules array
		array_unshift($decodedJson['spec']['rules'], $newRule);
		} 
		else {
		// If the 'rules' key doesn't exist, create it and add the new rule
		$decodedJson['spec']['rules'] = [$newRule];
		}

	// Convertir le tableau PHP modifié en JSON
	$updatedJson = json_encode($decodedJson, JSON_PRETTY_PRINT);

	//Request to add rule
	$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/'.$policy_PSM;
	$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
	$body=$updatedJson;
	
	$httpResponse = sendApiRequest($url,$headers,$body,'./cookie.txt','PUT');
	//echo $response;
	
	//collect status of the answer
	$pattern = '/\{.*\}/s'; // Pattern to match JSON data
	preg_match($pattern, $httpResponse, $matches);

	if (!empty($matches)) {
		$jsonContent = $matches[0];
		// Now $jsonContent contains the extracted JSON
		$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
	// Check if the 'status' key and 'propagation-status' key exist
		if (isset($decodedJson['status']['propagation-status'])) {
			$propagationStatus = $decodedJson['status']['propagation-status'];
			
			// Accessing specific fields within propagation-status
			$generationId = $propagationStatus['generation-id'];
			$updated = $propagationStatus['updated'];
			$pending = $propagationStatus['pending'];
			$status = $propagationStatus['status'];
			
			// Output or use the propagation status values as needed
			echo "Generation ID: " . $generationId . "<br>";
			echo "Updated: " . $updated . "<br>";
			echo "Pending: " . $pending . "<br>";
			echo "Status: " . $status . "<br>";
			} 
			else {
				echo "No 'propagation-status' found in the JSON content.<br>";
				//echo $httpResponse;
				$result = $decodedJson['result'];
				$str = $result['Str'];
				$messages = $decodedJson['message'];
				 // Access the 'Str' value
				echo '<b><font color=red>';
				echo "Str: " . $str . "<br>";

				// Access the 'message' array
				echo "Messages: <br>";
				foreach ($messages as $message) {
					echo "- " . $message . "<br>";
					}
				echo '</font></b>';
			}		
		} 
		else {
			echo "No JSON found in the HTTP response.";	
			}

		header("Refresh:0,url=$pagename");
	}
	else
	{
		//remove rule 
		echo "we remove the rule";
		//first need to collect the rule again
		$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/'.$policy_PSM;
		$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
		$body = '{"username": "'.$usr_PSM.'","password": "'.$pwd_PSM.'","tenant": "default"}';
		$response = sendApiRequest($url,$headers,$body,'./cookie.txt','GET');
		// Your HTTP response containing JSON
		$httpResponse = $response;

		// Use regular expressions to extract JSON content from the response
		$pattern = '/\{.*\}/s'; // Pattern to match JSON data
		preg_match($pattern, $httpResponse, $matches);

		if (!empty($matches)) {
			$jsonContent = $matches[0];
			// Now $jsonContent contains the extracted JSON
			$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
			// Access and work with the extracted JSON data
			//var_dump($decodedJson);
		} else {
			echo "No JSON found in the HTTP response.";
		}
		// Assuming $jsonContent holds your extracted JSON
		// Decode JSON to PHP array
		$decodedJson = json_decode($jsonContent, true);
		if ($decodedJson && isset($decodedJson['spec']['rules'])) {
			$rules = $decodedJson['spec']['rules'];
			} else {
				echo "<br>No 'rules' found in the JSON content.";
					}
		//go through the table and delete the rule name autoblock_mqtt
		foreach ($decodedJson['spec']['rules'] as $key => $value) {
			if ($value["name"] == "autoblock_mysql") {
				// Supprimer l'élément du tableau
				echo "<br><b>we enter in unset</b><br>";
				//unset($decodedJson['spec']['rules'][$key]);
				array_splice($decodedJson['spec']['rules'], $key, 1);
			}
		//$decodedJson = array_values($decodedJson['spec']['rules']);
		}
		
		//Request to remove rule (update policy)
		// convert PHP table into JSON
		$updatedJson = json_encode($decodedJson, JSON_PRETTY_PRINT);
		$url = 'https://'.$ip_psm.'/configs/security/v1/tenant/default/networksecuritypolicies/'.$policy_PSM;
		$headers = ['Cookie'=> 'sid='.$cookies['sid'].'', 'Content-Type' => 'text/plain'];
		$body=$updatedJson;
		
		$httpResponse = sendApiRequest($url,$headers,$body,'./cookie.txt','PUT');
		//echo $response;
		
		//collect status of the answer
		$pattern = '/\{.*\}/s'; // Pattern to match JSON data
		preg_match($pattern, $httpResponse, $matches);

		if (!empty($matches)) {
			$jsonContent = $matches[0];
			// Now $jsonContent contains the extracted JSON
			$decodedJson = json_decode($jsonContent, true); // Decode JSON to PHP array
		// Check if the 'status' key and 'propagation-status' key exist
			if (isset($decodedJson['status']['propagation-status'])) {
				$propagationStatus = $decodedJson['status']['propagation-status'];
				
				// Accessing specific fields within propagation-status
				$generationId = $propagationStatus['generation-id'];
				$updated = $propagationStatus['updated'];
				$pending = $propagationStatus['pending'];
				$status = $propagationStatus['status'];
				
				// Output or use the propagation status values as needed
				echo "Generation ID: " . $generationId . "<br>";
				echo "Updated: " . $updated . "<br>";
				echo "Pending: " . $pending . "<br>";
				echo "Status: " . $status . "<br>";
				} 
				else {
					echo "No 'propagation-status' found in the JSON content.<br>";
					//echo $httpResponse;
					$result = $decodedJson['result'];
					$str = $result['Str'];
					$messages = $decodedJson['message'];
					 // Access the 'Str' value
					echo '<b><font color=red>';
					echo "Str: " . $str . "<br>";

					// Access the 'message' array
					echo "Messages: <br>";
					foreach ($messages as $message) {
						echo "- " . $message . "<br>";
						}
					echo '</font></b>';
				}		
			} 
			else {
				echo "No JSON found in the HTTP response.";	
				}
	header("Refresh:0,url=$pagename");
	}
}


?>
<!DOCTYPE html> 
<html>
	<head> 
	<title>automation PSM</title> 
	<meta charset="UTF-8" />
	<link rel="stylesheet" href="styles.css">
	</head>
<body>

<!-- Rounded switch -->
<table>
	<tr>
		<td>
		<form action="<?php echo $pagename; ?>?action=postMQTT" method="post">
		<label class="switch">
			<input type="checkbox" name='checkMQTT' <?php if($MqttInPlace == true) echo 'checked' ?>>
			<span class="slider round"></span>
		</label>
		</td>
		<td><font size='15'>block mqtt</font> </td>
		<td><input type="submit" style="height:50px;font-size: 30px;" value='submit'></td>
		</form>
	</tr>
	<tr>
		<td>
		<form action="<?php echo $pagename; ?>?action=postMYSQL" method="post">
		<label class="switch">
			<input type="checkbox" name='checkMYSQL' <?php if($MysqlInPlace == true) echo 'checked' ?>>
			<span class="slider round"></span>
		</label>
		</td>
		<td><font size='15'>block mysql</font> </td>
		<td><input type="submit" style="height:50px;font-size: 30px;" value='submit'></td>
		</form>
	</tr>
</table>

</body>
</html>

