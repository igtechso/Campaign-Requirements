<?php
$sname= "localhost";
$unmae= "allcmsdemo_trading-campaign";
$password = "V*50mJsZrsdf5aYY";
$db_name = "allcmsdemo_trading-campaign";

$conn = mysqli_connect($sname, $unmae, $password, $db_name);

if (!$conn) {

    echo "Connection failed!";

}