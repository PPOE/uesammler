<?php
header('Access-Control-Allow-Origin: *');

/* db.config defines 
 * $con to set DB connection string
 */
include 'config.php';
$dbconn = pg_connect("dbname=uesammler")
  or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());

//if (!$dbcon)
//  {
//  die('Could not connect: ' . mysql_error());
//  }
  
  $sPLZs = "";
  for ($i=1; $i<10; $i++)
  {
         switch ($i)
        {
            case 1:
                $where = "Wien";
                $qwhere = "WIE";
              break;
            case 2:
                $where = "Oberoesterreich";
                $qwhere = "OOE";
                break;
            case 3:
                $where = "Niederoesterreich";
                $qwhere = "NOE";
                break;
            case 4:
                $where = "Burgenland";
                $qwhere = "BGL";
                        break;
            case 5:
                $where = "Kaernten";
                $qwhere = "KNT";
                        break;
            case 6:
                $where = "Steiermark";
                $qwhere = "STM";
                        break;
            case 7:
                $where = "Tirol";
                $qwhere = "TIR";
                    break;
            case 8:
                $where = "Vorarlberg";
                $qwhere = "VBG";
                break;
            case 9:
                $where = "Salzburg";
                $qwhere = "SBG";
                break;
            default:
                die('Zuordnung zu Bundesländern fehlgeschlagen'.$i);
        }
        $query = "select count(*) from 
( select ues.plz, plzlut.bereich  from ues, plzlut where plzlut.plz = ues.plz and plzlut.bereich = '$qwhere') as newtable;";
        $result = pg_query($query) or die('Abfrage in plzlut fehlgeschlagen: ' . pg_last_error());
        $line = pg_fetch_array($result);

         $score = $line[0];

        $query = "select sum(count) from 
( select ues.plz, plzlut.bereich, ues.count from ues, plzlut where plzlut.plz = ues.plz and plzlut.bereich = '$qwhere') as newtable;";
        $result = pg_query($query) or die('Abfrage in plzlut fehlgeschlagen: ' . pg_last_error());
        $line = pg_fetch_array($result);

	if ($line[0] != "") {
		$score_count = $line[0];
	} else {
		$score_count = 0;
	}

                 $query = "select collected, req from uestat where bereich='$qwhere';";
        $result = pg_query($query) or die('Abfrage in uestat fehlgeschlagen: ' . pg_last_error());
        $line = pg_fetch_array($result);
        $collected = $line[0];
        $req = $line[1];

	$line = array($score,$score_count,$collected,$req);

	$output[$where] = $line;

         ///Create array structure to write out to javascript
        /// name, wievielePledges, wievieleGesammelt?, wievieleBenötigt?
         $sPLZs .= "['".$where."', ".$score.", ".$score_count.", ".$collected.", ".$req."],";
//        echo $where . ": ";
//        echo $score;
//        echo "<br/>";
}
  
	echo json_encode($output);
  $sPLZs = rtrim($sPLZs, ",");
    pg_close($dbconn);
?>
