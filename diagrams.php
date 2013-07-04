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

         
         ///Create array structure to write out to javascript
        /// name, wievielePledges, wievieleGesammelt?, wievieleBenötigt?
         $sPLZs .= "['".$where."', ".$score.", ".$score_count.", ".$collected.", ".$req."],";
//        echo $where . ": ";
//        echo $score;
//        echo "<br/>";
}
  
  $sPLZs = rtrim($sPLZs, ",");
    pg_close($dbconn);
?>

    <!--Load the AJAX API-->
    <script src="jquery.min.js"></script>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart', 'geochart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'PLZ');
        data.addColumn('number', 'eingetragen');
	data.addColumn('number', 'eingetragen und versprochen');
        data.addColumn('number', 'bei uns eingelangt');
        data.addColumn('number', 'benötigt');
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

    $(function() {
	$url = 'https://initiative.piratenpartei.at/uesammler/output.php';
	$.getJSON($url, function(data) {
		drawRegionsMap(data);
	});
    });

    function drawRegionsMap(json) {
	$.each(json, function(item, line) {
	    line[4] = Number(((line[1] / line[3]) * 100).toFixed(2)), line[1];
	    line[5] = line[1] + ' von ' + line[3] + ' (' + line[4] + '%)'
	});

	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Country');
	data.addColumn('number', 'Prozent');
	data.addColumn({type:'string', role:'tooltip'});
	data.addRows([
	    [{v: 'AT-1', f: 'Burgenland'}, json.Burgenland[4], json.Burgenland[5]],
	    [{v: 'AT-2', f: 'Kärnten'}, json.Kaernten[4], json.Kaernten[5]],
	    [{v: 'AT-3', f: 'Niederösterreich'}, json.Niederoesterreich[4], json.Niederoesterreich[5]],
	    [{v: 'AT-4', f: 'Oberösterreich'}, json.Oberoesterreich[4], json.Oberoesterreich[5]],
	    [{v: 'AT-5', f: 'Salzburg'}, json.Salzburg[4], json.Salzburg[5]],
	    [{v: 'AT-6', f: 'Steiermark'}, json.Steiermark[4], json.Steiermark[5]],
	    [{v: 'AT-7', f: 'Tirol'}, json.Tirol[4], json.Tirol[5]],
	    [{v: 'AT-8', f: 'Vorarlberg'}, json.Vorarlberg[4], json.Vorarlberg[5]],
	    [{v: 'AT-9', f: 'Wien'}, json.Wien[4], json.Wien[5]]
	]);

	var options = {
		region: 'AT',
		width: 800,
		height: 400,
		resolution: 'provinces',
		minValue: 0,
		maxValue: 100,
		values: [0, 100],
		colorAxis: {
		    colors: ['#c8bed9', '#4c2582'],
		    minValue: 0,
		    maxValue: 100
		},
		legend: 'none',
		datalessRegionColor: 'none',
		backgroundColor: 'none'
	}

        var chart = new google.visualization.GeoChart(document.getElementById('chart_div2'));
        chart.draw(data, options);
    }

    </script>
    <div id="chart_div2" style="margin-left: 100px;"></div>
    <div id="chart_div" style="margin-left: 100px"></div>
