
<br>
<?php
//session_start();
//include("password.php");
include("config.php");
$_SESSION["datasql"] = file_get_contents("data.txt");
//$_SESSION["datasql"]="";
//read from database
$mysqli = new mysqli("$ip_mysql", "$user_mysql", "$pwd_mysql", "MQTT");
if ($mysqli->connect_errno) {
	echo "<font color='FF000000' size='14'><b>";
	echo "failed to connect to database :"; 
	echo $mysqli->connect_error;
	echo "</b></font><br>";
}
else {
	$query = "SELECT * from `mqtt-value`";
	if ($result = $mysqli->query($query)) {
		while($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$value="$row[value]";
		}
	}
	//echo 'data: '.$_SESSION['datasql'].'<br>';
	//echo 'value:'.$value.'<br>';
	if(isset($_SESSION["datasql"]) && $_SESSION["datasql"] == $value){
		echo "<font color='FF000000' size='20'><b>DB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: no new data</b></font><br>";
		}
		else {
			echo "<br><font size='20'><b> DB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: $value</b></font><br>"; 
			//$_SESSION["datasql"] = $value;
			file_put_contents("data.txt", $value, LOCK_EX);
			}
	}
echo "<br><font size='12'><b>server 1</b></font></br>";

$mysqli->close();
?>
