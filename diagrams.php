<?php

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
                $where = "Oberösterreich";
                $qwhere = "OOE";
                break;
            case 3:
                $where = "Niederösterreich";
                $qwhere = "NOE";
                break;
            case 4:
                $where = "Burgenland";
                $qwhere = "BGL";
                        break;
            case 5:
                $where = "Kärnten";
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
( select ues.plz, plzlut.bereich from ues, plzlut where plzlut.plz = ues.plz and plzlut.bereich = '$qwhere') as newtable;";
        $result = pg_query($query) or die('Abfrage in plzlut fehlgeschlagen: ' . pg_last_error());
        $line = pg_fetch_array($result);

         $score = $line[0];
         
                 $query = "select collected, req from uestat where bereich='$qwhere';";
        $result = pg_query($query) or die('Abfrage in uestat fehlgeschlagen: ' . pg_last_error());
        $line = pg_fetch_array($result);
        $collected = $line[0];
        $req = $line[1];

         
         ///Create array structure to write out to javascript
        /// name, wievielePledges, wievieleGesammelt?, wievieleBenötigt?
         $sPLZs .= "['".$where."', ".$score.", ".$collected.", ".$req."],";
//        echo $where . ": ";
//        echo $score;
//        echo "<br/>";
}
  
  $sPLZs = rtrim($sPLZs, ",");
    pg_close($dbconn);
?>

    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'PLZ');
        data.addColumn('number', 'registered');
        data.addColumn('number', 'collected');
        data.addColumn('number', 'required');
        data.addRows([
            <?php //
            echo $sPLZs;
            ?>
        ]);

        // Set chart options
        var options = {'title':'Unterstützungserklärungen pro Bundesland',
                       'width':800,
                       'height':400};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>