<?php
// $Id$
	require_once("inc/stdLib.php");
	include("inc/template.inc");
	include("inc/crmLib.php");
	include("inc/UserLib.php");

	$user=getUserStamm($_SESSION["loginCRM"]);
	$MailSign=ereg_replace("\n","<br>",$user["MailSign"]);
	$MailSign=ereg_replace("\r","",$MailSign);

	if ($_POST) {
		$Subject=preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $_POST["Subject"]);
		$BodyText=preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $_POST["BodyText"]);
		$okC=true;
		if ($_POST["CC"]<>"") { 
			$CC=preg_replace( "/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)]/im", "", $_POST["CC"]);
			$CC=preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $CC);
			$rc=chkMailAdr($CC); 
			if($rc<>"ok") { 
				$okC=false; $msg.=" CC:".$rc; 
			} else {
				insertCSVData(array("CC",$CC,"","","","","","",$CC,""));
			}
		};
		if ($okC) {
			$anh="";
			if ($_FILES["Datei"]["name"]<>"") {
				move_uploaded_file($_FILES["Datei"]["tmp_name"],"tmp/".$_SESSION["loginCRM"].".file");
				$dateiname=$_FILES["Datei"]["name"];
				$type=$_FILES["Datei"]["type"];
			}
			$limit=50;
			$abs=$user["Name"]." <".$user["eMail"].">";
		 	$headers['Replay-To']=$abs;
			$headers['From']=$abs;
			$headers['Return-Path']=$abs;
			$headers['X-Mailer']='PHP/'.phpversion();
			//$headers['Content-Type']='text/plain; charset=utf-8';
			//$headers['Content-Type']='text/plain; charset=iso-8859-1';
			$headers['Subject']=$Subject;
			$_SESSION["abs"]=$abs;
			$_SESSION["headers"]=$headers;
			$_SESSION["subject"]=$Subject;
			$_SESSION["bodytxt"]=$BodyText;
			$_SESSION["dateiname"]=$dateiname;
			$_SESSION["type"]=$type;
			$_SESSION["limit"]=$limit;

			$sendtxt="Es &ouml;ffnet sich nun ein extra Fenster.<br>";
			$sendtxt.="Bitte schlie&szlig;en sie es nur wenn sie dazu aufgefordert werden,<br>";
			$sendtxt.="da sonst der Mailversand beendet wird.<br><br>";
			$sendtxt.="Sie k&ouml;nnen aber ganz normal mit anderen Programmteilen arbeiten.";
			$sendtxt.="<script language='JavaScript'>fx=open('sendsermail.php?first=1$anh','sendmail','width=200,height=100');</script>";
			$sendtxt.="<pre>$BodyText</pre>";
		}
	}  else {
		$BodyText=" \n".ereg_replace("\r","",$user["MailSign"]);
	}
	
		$t = new Template($base);
		$t->set_file(array("mail" => "sermail.tpl"));
		$t->set_var(array(
				Msg	 => $msg,
				CC	 => $CC,
				Subject  => $Subject,
				BodyText => $BodyText,
				Sign 	 => $MailSign,
				SENDTXT  => $sendtxt
		));
		$t->pparse("out",array("mail"));
			
?>
