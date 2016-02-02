<?php
session_start();
require_once __DIR__.'/inc/version.php';
require_once __DIR__.'/inc/stdLib.php';
$git = @exec('git log -1',$out,$rc);
if ( $rc > 0 ) {
    $commit = '';
} else {
    foreach( $out as $row ) {
        if ( substr($row,0,1) == 'D' ) $date = substr($row,6);
    }
    $commit  = '<tr><td>Git: </td><td>'.substr($out[0],7,-34).'</td></tr>';
    $commit .= '<tr><td>Datum: </td><td>'.$date.'</td></tr>';
}
$rc = false;
/*if( varExist( $_GET['test'] == 'ja' ) ){
    $rs = $GLOBALS['dbh']->getOne("select * from crm order by  version DESC, datum DESC");
    printArray( $rs );
}*/


?>
<html>
<head><title>Kivitendo CRM Status</title>
<?php
    echo $menu['stylesheets'];
    echo $menu['javascripts'];
    echo $head['THEME'];
    echo $head['JQTABLE'];
?>
<script language="JavaScript" type="text/javascript">
    function chksrv() {
        $.get("jqhelp/logserver.php",function(data) {
            $("#SRV").append(data);
        });
    }
    $(document).ready(function() {
        $( 'button' ).button().css({ 'width': '130px', 'padding-left': '5px' });
        $( '#saveDB' ).click(function() {
            $( '#statusDialog' ).dialog({
                title: 'Datenbank sichern'
            });
            $("#statusDialog").html('sichern erfolgt, <br> bitte warten');
            $("#statusDialog").dialog('open');
            $.ajax({
                dataType: "json",
                url: "ajax/ajaxStatus.php?action=saveDBs",
                method: "GET",
                success : function (data){
                    $("#statusDialog").html(data);
                    $("#statusDialog").dialog('open');
                },
                error: function() {
                    $("#statusDialog").html('Datenbank sichern fehlgeschlagen');
                    $("#statusDialog").dialog('open');
                }
            });
        });;
        //$( '#showDB' ).button();
        //$( '#showErrorLog' ).button();
        //$( '#showPgLog' ).button();
        $( '#statusDialog' ).dialog({
            autoOpen: false,

        });
        $("#testDB").click(function() {
            $( '#statusDialog' ).dialog({
                title: 'Datenbank Test'
            });
            $.ajax({
                dataType: "json",
                url: "ajax/ajaxStatus.php?action=showVersion",
                method: "GET",
                success : function (data){
                    $("#statusDialog").html('Installierte Version: ' + data.version + '</br></br> vom: ' + data.datum + '</br></br> durch: ' + data.uid);
                    $("#statusDialog").dialog('open');
                },
                error: function() {
                    $("#statusDialog").html('Datenbankzugriff fehlgeschlagen');
                    $("#statusDialog").dialog('open');
                }
            });
        });
        $("#info").tablesorter();
    });
</script>

</head>
<body>
<?php
 echo $menu['pre_content'];
 echo $menu['start_content'];
?>
<div class="ui-widget-content" style="height:600px">
<p class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0.6em;">Status</p>
<center>
<?php
$db=false;
$prog=false;
$d = dir("log/");
while (false !== ($entry = $d->read())) {
    if (preg_match('/upd.*log/',$entry)) echo "<a href='log/$entry'>$entry</a><br>\n";
    if (preg_match('/instprog.log/',$entry)) $prog=true;
    if (preg_match('/install.log/',$entry)) $db=true;
}
$d->close();

printArray( $_SESSION['dbData']['dbname'    ]);
//if ($prog) { echo "<a href='log/instprog.log'>Programminstallation</a><br>"; } else { echo "Kein Logfile f&uuml;r Programminstallation<br>"; }
//if ($db) { echo "<a href='log/install.log'>Datenbankinstallation</a><br>"; } else { echo "Kein Logfile f&uuml;r Datenbankinstallation<br>"; }
?>
<table id="info" class="tablesorter" style="width:auto; font-size:1pt">
    <thead></thead>
    <tbody>
    <tr><td>ProgrammVersion</td><td><?php echo  $VERSION." ".$SUBVER ?></td></tr>
<?php echo $commit; ?>
    <tr><td>Auth-Datenbank:</td><td> <?php echo  varExist( $_SESSION['erpConfig']['authentication/database']['db'] )?></td></tr>
    <tr><td>Datenbank:</td><td> <?php echo  varExist( $_SESSION['dbData']['dbname'] )?></td></tr>
    <tr><td>db-Server:</td><td><?php echo  varExist( $_SESSION['dbData']['dbhost'] )?></td></tr>
    <tr><td>Mandant:</td><td><?php echo  varExist( $_SESSION['dbData']['mandant'] )?>:<?php echo  $_SESSION['dbData']['manid']?></td></tr>
    <tr><td>Benutzer:</td><td><?php echo  varExist( $_SESSION['userConfig']['name'] ).':'.varExist( $_SESSION['userConfig']['id'] )?></td></tr>
    <tr><td>Session-ID:</td><td><?php echo  session_id() ?></td></tr>
    <tr><td>PHP-Umgebung:</td><td><button onclick="window.location.href='info.php'">anzeigen</button></td></tr>
    <tr><td>Session:</td><td><button onclick="window.location.href='showsess.php'">anzeigen</button><button onclick="window.location.href='delsess.php'">löschen</button></td></tr>
    <tr><td>db-Zugriff:</td><td><button id="testDB">testen</button></td></tr>
    <tr><td>Updatecheck<a href="update/newdocdir.php?chk=1">:</a></td><td><button onclick="window.location.href='inc/update_neu.php'">durchführen</button></td></tr>
    <tr><td>Installationscheck:</td><td><button onclick="window.location.href='inc/install.php?check=1'">durchführen</button></td></tr>
    <tr><td>Benutzerfreundliche Links:</td><td><button onclick="window.location.href='links.php?all=1'">erzeugen</button></td></tr>
    <tr><td>Datenbanken:</td><td><button id="saveDB">Sichern</button><button id="showDB">Zeigen</button></td></tr>
    <tr><td>Logfiles:</td><td><button id="showErrorLog">Error Log</button><button id="showPgLog">PgSQL Log</button></td></tr>

</tbody>
</table>
<div id="statusDialog">
</div>

<?php
   /* if ($rs) {
        echo 'Datenbankzugriff erfolgreich!<br>';

        //foreach ($rc as $row) {
            echo 'Installierte Version: '.$rs["version"].' vom: '.$rs["datum"].' durch: '.$rs["uid"].'<br>';
        //}
    } */
?>
</center>
<?php echo $menu['end_content']; ?>
</body>

</html>
