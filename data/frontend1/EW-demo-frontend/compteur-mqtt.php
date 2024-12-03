<br>

<?php
//session_start();
$_SESSION['mqttData'] = '';
include("config.php");

//collect mqtt data using php-mqtt client
require('vendor/autoload.php');
use \PhpMqtt\Client\MqttClient;

$server   = $ip_mysql;
$port     = 1883;
$clientId = 'php-mqtt';
$username = $usr_mqtt;
$password = $pwd_mqtt;

$connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
  ->setUsername($username)
  ->setPassword($password)
  ->setKeepAliveInterval(60);
  
$mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
$mqtt->connect($connectionSettings, true);
$mqtt->subscribe('python/mqtt-pensando', function ($topic, $message, $retained, $matchedWildcards) use ($mqtt) {
	//echo sprintf("Received message on topic [%s]: %s\n", $topic, $message);
	//echo "<br>";
	$_SESSION['mqttData'] = $message;
	$mqtt->interrupt();
}, 0);

//stop the loop in case no new data coming after 3s	
$mqtt->registerLoopEventHandler(function (MqttClient $mqtt, float $elapsedTime) {
	if ($elapsedTime >= 3) {
		$mqtt->interrupt();
	}
});	$mqtt->loop(true);
$mqtt->disconnect();
//echo "session:$_SESSION['data'];";
$mqttData = $_SESSION['mqttData'];



//read from file
#$data = file_get_contents("data.txt");
if(isset($_SESSION["data"]) && $_SESSION["data"] == $mqttData){
	echo "<font color='FF000000' size='20'><b>MQTT : no new data</b></font><br>";
}
else {
	echo "<font size='20'><b>MQTT : $mqttData</b></font><br>";
}
$_SESSION["data"] = $mqttData;

?>
