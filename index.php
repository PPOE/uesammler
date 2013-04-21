<?php

$h1 = '        <h1>Unterstütze die Piraten!</h1>
        <p class="lead">Um bei den nächsten Wahlen antreten zu können brauchen die Piraten deine Unterstützung!</p>';

require 'config.php';

function isValidEmail($email){
    return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $email);
}

if($_POST['submit'] != "true") {goto end;}

$plz = intval($_POST['plz']);
$mail = pg_escape_string($_POST['mail']);
$contact = pg_escape_string($_POST['contact']);
$newsletter = intval($_POST['newsletter']);
$count = intval($_POST['count']);

if (is_int(intval($count))) {
	if ($count == "" || $count <= 0) {
		$count = 1;
	} else {
		$count += 1;
	}
} else {
	$error = "Bitte eine richtige Unterstützeranzahl angeben!";
	goto end;
}

if(intval($plz)) {
	if($plz < 1000 || $plz > 9999) {$error = "Bitte korrekte PLZ eingeben!";
	goto end;}
} else {$error = "Bitte korrekte PLZ eingeben!";
	goto end;}

if(isValidEmail($mail)) {
} else {$error = "Bitte korrekte E-Mail-Adresse eingeben!";
	goto end;}

if($newsletter){
	$ch = curl_init();
	$post_key = "email=".$mail."&bund=bund&submit=true";
	$post_url = "https://mitglieder.piratenpartei.at/newsletter/register.php";
	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_key);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
	curl_exec($ch);
}
        $query = "INSERT INTO ues (plz, email, comment, count) VALUES ($plz, '$mail', '$contact', '$count');";
  
        $result = pg_query($dbconn, $query) or die('Einragung fehlgeschlagen.');//: ' . pg_last_error());

//$new_ue = mysql_query(
//        "INSERT INTO ues (plz, email, comment) VALUES ($plz, $mail, mysql_real_escape_string($contact));");

    pg_close($dbconn);

$h1 = '        <h1>Danke für deine Unterstützung!</h1>
        <p class="lead">Wir werden dich im Juli kontaktieren, wenn die heiße Phase beginnt. Dann bitten wir dich und deine Freunde auf eurem Gemeindeamt unsere Unterst&uuml;tzungserklärungen zu unterschreiben. Am besten gemeinsam.</p>';
$ausblenden = true;

end:
?>


<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <title>Unterstützungserklärungen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Piratenpartei Österreichs">

    <!-- Le styles -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">
    <style type="text/css">
      body {
	background-color: #4c2582;
        padding-top: 20px;
        padding-bottom: 40px;
      }

      /*Custom container by burnoutberni */
      #white-container {
	height: 100%;
	margin: 0 auto;
	max-width: 1000px;
	background-color: white;
	padding: 0px 10px;
      }

      /* Custom container */
      .container-narrow {
        margin: 0 auto;
        max-width: 700px;
      }
      .container-narrow > hr {
        margin: 30px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 60px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }

      <?if ($ausblenden) {
	echo ".ausblenden {display:none;}";
      }?>
    </style>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>

  <body>
    <div id="white-container">
    <div class="container-narrow">

      <hr>

      <div class="jumbotron">
	<?echo $h1;?>
      </div>

      <hr>

      <div class="row-fluid marketing ausblenden">
        <div class="span6">
          <h4>Wie kann ich die Piraten unterstützen?</h4>
          <p>Für einen Wahlantritt brauchen die Piraten eine gewisse Anzahl an Unterstützungserklärungen. Für die Abgabe dieser Erklärungen gibt es nur ein kurzes Zeitfenster vor der Nationalratswahl und um dieses effektiv nutzen zu können, bitten wir dich jetzt dich zu registrieren. Am Beginn des Zeitfensters schicken wir dir dann ein vorausgefülltes Formular und die genauen Schritte, die notwendig sind, um uns eine gültige Unterstützungerklärung zu kommen zu lassen.</p>
					<h4>Kann ich euch auch anders unterstützen?</h4>
					<p>Ja, du kannst auch direkt bei uns <a href="http://www.piratenpartei.at/mitmachen">mitmachen</a>, uns <a href="http://initiative.piratenpartei.at">deine Ideen zukommen lassen</a> oder uns <a href="https://www.piratenpartei.at/mitmachen/spenden">finanziell unterstützen</a>.</p>
        </div>

        <div class="span6" id="formular">
	  <?if(isset($error)){echo '<div class="alert alert-error">'.$error.'</div>';}?>
	<form action="index.php" method="post">
                <h5>Gib bitte deine PLZ an:</h5>
                <input type="text" name="plz" />
		<h5>Ich bringe noch ... weitere Unterstützungserklärungen aus meinem Bundesland:</h5>
		<input type="text" name="count"/>
                <h5>Deine E-Mail-Adresse:</h5>
                <input type="text" name="mail" />
                <h5>Sonstige Kontaktmöglichkeiten (optional):</h5>
                <input type="text" name="contact" />
								<label class="checkbox">
									<input type="checkbox" name="newsletter" checked="checked"> Newsletter abonnieren?
								</label>
            <p>
	    <input type="hidden" value="true" name="submit"/>
            <input class="btn btn-primary" type="submit" value="Senden" />
            <input class="btn" type="reset" value="Zurücksetzen" />
            </p>
        </form>
        </div>
      </div>

      <hr>
    </div> <!-- /container -->
<?php
include 'diagrams.php';
?>
    </div> <!-- #white-container -->

    <div class="container-narrow" style="color:white;">
      <div class="footer">
	<p>Piratenpartei Österreichs, Lange Gasse 1/4, 1080 Wien</p>
	<p>Für den Inhalt verantwortlich: bv@piratenpartei.at · bgf@piratenpartei.at</p>
      </div>
    </div>
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/bootstrap.js"></script>

  </body>
</html>

