<?
$dbconn = pg_connect("dbname=uesammler")
  or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());
?>
