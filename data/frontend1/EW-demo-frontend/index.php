<!DOCTYPE html> 
<html>
	<head> 
	<title>AMD Pensando Demo 1</title> 
	<meta charset="UTF-8" />
	<!-- <link rel="stylesheet" href="styles.css"> -->
	<script type="text/javascript" src="jquery/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="jquery/jquery.mobile-1.3.2.min.js"></script> 
	<script language="JavaScript" type="text/JavaScript">
		//On va actualiser le div #temp_pi avec le contenu de la page temp.php et on répête le script toute les 5 secondes.
		$(document).ready(function() {
		$("#compteur-file").load("compteur-mqtt.php");
		var refreshId = setInterval(function() {
			$("#compteur-file").load("compteur-mqtt.php");
			}, 5000);
			$.ajaxSetup({ cache: false })
			});
		$(document).ready(function() {
		$("#compteur-sql").load("compteur-sql.php");
		var refreshId2 = setInterval(function() {
			$("#compteur-sql").load("compteur-sql.php");
			}, 5000);
			$.ajaxSetup({ cache: false })
			});
	</script>
	</head>
<body>
<img src="images/amd_pensando_logo.jpg" width="220">
<img src="images/hpe-aruba.png" width="220">

<!-- header: affichage du compteur (compteur.php) -->
<div id="compteur-file"></div>
<div id="compteur-sql"></div>
	<p></p>

<img src="images/diagram.jpg">
<br>
<iframe src="api-test.php" title="description" width="500"></iframe>
<?php 
//include('api-test.php');
//phpinfo();
?>
</body>
</html>
