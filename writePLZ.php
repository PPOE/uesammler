<?php
    require 'config.php';
    $fin = fopen('plzlut.csv','r') or die('cant open file');

    $query="truncate plzlut;";
    $result = pg_query($dbconn, $query) or die('recreating table failes.');//: ' . pg_last_error());

    
    while (($data=fgetcsv($fin,150,","))!==FALSE) {
        $data[2] = pg_escape_string($data[2]);
        $data[1] = pg_escape_string($data[1]);
        $query = "INSERT INTO plzlut (plz, bereich, gemeinde) VALUES (".intval($data[0]).", '{$data[1]}', '{$data[2]}');";
        $result = pg_query($dbconn, $query) or die('Eintragung fehlgeschlagen.');//: ' . pg_last_error());
        echo "<br/>Record updated: <br />\n".intval($data[0]).", ".$data[1].", ".$data[2];
        }
fclose($fin);
    pg_close($dbconn);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
