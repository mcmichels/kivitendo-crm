<?
// $Id$

/****************************************************
* getAllCustomer
* in: sw = array(Art,suchwort)
* out: rs = array(Felder der db)
* hole alle Kunden
*****************************************************/
function getAllCustomer($sw,$Pre=true) {
global $db;
	if ($Pre) $Pre=$_SESSION["Pre"];
	$rechte=berechtigung();
	if (!$sw[0]) {
		 $where="phone like '$Pre".$sw[1]."%' "; 
	} else { 
		$where="upper(name) like '$Pre".$sw[1]."%' or ";
		$where.="upper(department_1) like '$Pre".$sw[1]."%' or ";
		$where.="upper(department_2) like '$Pre".$sw[1]."%'"; 
	}
	$sql="select *,'C' as tab from customer where ($where) and $rechte";
	$rs=$db->getAll($sql);
	if(!$rs) {
		$rs=false;
	};
	return $rs;
}

/****************************************************
* getFirmaStamm
* in: id = int, ws = boolean
* out: daten = array
* Stammdaten einer Firma holen
*****************************************************/
function getFirmaStamm($id,$ws=true) {
global $db;
	$sql="select sum(amount) from oe where customer_id=$id and quotation='f' and closed = 'f'";
	$rs=$db->getAll($sql);
	$oa=$rs[0]["sum"];
	$sql="select sum(amount) from ar where customer_id=$id and amount<>paid";
	$rs=$db->getAll($sql);
	$op=$rs[0]["sum"];
	$sql="select C.*,E.login,B.description as kdtyp,B.discount as typrabatt,P.pricegroup,L.lead as leadname from customer C ";
	$sql.="left join employee E on C.employee=E.id left join business B on B.id=C.business_id ";
	$sql.="left join pricegroup P on P.id=C.klass left join leads L on C.lead=L.id ";
	$sql.="where C.id=$id";
	$rs=$db->getAll($sql);  // Rechnungsanschrift
	if(!$rs) {
		return false;
	} else {
		$row=$rs[0];
		if ($row["grafik"]) {
			$image="./dokumente/".$_SESSION["mansel"]."/$id/logo.".$row["grafik"];
			$size=@getimagesize(trim($image));
			$row["size"]=$size[3];
		}
		$sql="select * from shipto where trans_id=$id";
		$rs2=$db->getAll($sql);  // abweichende Lieferanschrift
		if (!$rs2) {  // es ist keine abweichende Anschrift da
			if ($ws) {	// soll dann aber mit Re-Anschrift gef�llt werden
				$row2=Array(
					shiptoname => $row["name"],
					shiptodepartment_1 => $row["department_1"],
					shiptodepartment_2 => $row["department_2"],
					shiptostreet => $row["street"],
					shiptozipcode => $row["zipcode"],
					shiptocity => $row["city"],
					shiptocountry => $row["country"],
					shiptocontact => "",
					shiptophone => $row["phone"],
					shiptofax => $row["fax"],
					shiptoemail => $row["email"],
					shiptocountry => $row["country"]
				);
			} else {  // leeres Array bilden
				$row2=Array(
					shiptoname => "",
					shiptodepartment_1 => "",
					shiptodepartment_2 => "",
					shiptostreet => "",
					shiptozipcode => "",
					shiptocity => "",
					shiptocountry => "",
					shiptocontact => "",
					shiptophone => "",
					shiptofax => "",
					shiptoemail => "",
					shiptocountrycountry => ""
				);
			}
		} else {
			$row2 = $rs2[0];
		}
		$daten=array_merge($row,$row2);
	}
	$daten["op"]=$op;
	$daten["oa"]=$oa;
	return $daten;
};


/****************************************************
* getAllShipto
* in: id = int
* out: daten = array
* Alle abweichende Anschriften einer Firma holen
*****************************************************/
function getAllShipto($id) {
global $db;
	$sql="select A.id,S.* from shipto S left join  ar A on S.trans_id=A.id where A.customer_id=$id or S.trans_id=$id";
	$rs=$db->getAll($sql);  
	return $rs;
}

/****************************************************
* suchstr
* in: muster = string
* out: daten = array
* Suchstring �ber customer,shipto zusamensetzen
*****************************************************/
function suchstr($muster) {
	// Array zu jedem Formularfed: Tabelle (0=cust,1=ship), TabName, toUpper
	$dbfld=array(name => array(0,1),street => array(0,1),zipcode => array(0,0),
			city => array(0,1),phone => array(0,0),fax => array(0,0),
			homepage =>array(0,1),email => array(0,1),notes => array(0,1),
			department_1 => array(0,1),department_2 => array(0,1),
			country => array(0,1),typ => array(0,0),sw => array(0,1),
			language => array(0,0), business_id => array(0,0),
			ustid => array(0,1), taxnumber => array(0,0), lead => array(0,0),leadsrc => array(0,1),
			bank => array(0,1), bank_code => array(0,0), account_number => array(0,0));
	$dbfld2=array(name => "shiptoname", street=>"shiptostreet",ziptocode=>"shiptozipcode",
			city=>"shiptocity",phone=>"shiptophone",fax=>"shiptofax",
			email=>"shiptoemail",department_1=>"shiptodepartment_1",
			department_2=>"shiptodepartment_2",country=>"shiptocountry");
	$fuzzy=$muster["fuzzy"];
	$keys=array_keys($muster);
	$suchfld=array_keys($dbfld);
	$anzahl=count($keys);
	$tbl0=false;
	if ($muster["shipto"]){$tbl1=true;} else {$tbl1=false;}
	$tmp1=""; $tmp2="";
	for ($i=0; $i<$anzahl; $i++) {
		if (in_array($keys[$i],$suchfld) and $muster[$keys[$i]]<>"") {
			if ($dbfld[$keys[$i]][1]==1) {
				$case1="upper("; $case2=")";
				$suchwort=strtoupper(trim($muster[$keys[$i]]));
			} else {
				$case1=""; $case2="";
				$suchwort=trim($muster[$keys[$i]]);
			}
			$suchwort=strtr($suchwort,"*?","%_");
			$tmp1.="and $case1 K.".$keys[$i]." $case2 like '".$suchwort."$fuzzy' ";
			if ($tbl1 && $dbfld2[$keys[$i]]) 
				$tmp2.="and $case1 S.".$dbfld2[$keys[$i]]." $case2 like '".$suchwort."$fuzzy' ";
		}
	}
	if ($tbl1) {
		$tabs="customer K left join shipto S on K.id=S.trans_id";
		if ($tmp1) $where="(".substr($tmp1,3). ") or ( ";
		if ($tmp2) { 
			$where.=substr($tmp2,3).")"; 
		} else { 
			$where.=" 1)"; 
		}
	} else {
		$tabs="customer K";
		if ($tmp1) $where=substr($tmp1,3);
	}
	return array("where"=>$where,"tabs"=>$tabs); 
}

/****************************************************
* suchFirma
* in: muster = string
* out: daten = array
* KundenDaten suchen
*****************************************************/
function suchFirma($muster) {
global $db;
	$rechte=berechtigung();
	$tmp=suchstr($muster);
	$where=$tmp["where"]; $tabs=$tmp["tabs"];
	if ($where<>"") {
		$sql="select * from $tabs where ($where) and $rechte";
		$rs=$db->getAll($sql);
		if(!$rs) {
			$daten=false;
		} else {
			$daten=$rs;
		}
	}
	return $daten;
}
function getName($id) {
global $db;
	$sql="select name from customer where id = $id";
	$rs=$db->getAll($sql);
	if ($rs) {
		return $rs[0]["name"];
	} else {
		return false;
	}
}
/****************************************************
* saveFirmaStamm
* in: daten = array
* out: rc = int
* KundenDaten sichern ( update )
*****************************************************/
function saveFirmaStamm($daten,$datei,$neu=false) {
global $db;
	if (!empty($datei["Datei"]["name"])) {  		// eine Datei wird mitgeliefert
			$typ=array(1=>"gif",2=>"jpeg",3=>"png",4=>false);
			$imagesize=getimagesize($datei["Datei"]['tmp_name'],&$info);
			if ($imagesize[2]>0 && $imagesize[2]<4) {
				$bildok=chkdir($_SESSION["mansel"]."/".$daten["id"]);
				$daten["grafik"]=$typ[$imagesize[2]];
			}
	};
	// Array zu jedem Formularfed: Tabelle (0=cust,1=ship), TabName, require(0=nein,1=ja), Regel
	$dbfld=array(name => array(0,1,1,"Name",75),street => array(0,1,1,"Strasse",75),
			country => array(0,0,8,"Land",3),	zipcode => array(0,1,2,"Plz",10),
			city => array(0,1,1,"Ort",75),		phone => array(0,0,3,"Telefon",30),
			fax => array(0,0,3,"Fax",30),		homepage =>array(0,0,4,"Homepage",0),
			email => array(0,0,5,"eMail",0),	notes => array(0,0,0,"Bemerkungen",0),
			contact => array(0,0,1,"Kontakt",75),	ustid => array(0,0,0,"UStId",0),
			department_1 => array(0,0,1,"Zusatzname",75),
			department_2 => array(0,0,1,"Abteilung",75),
			sw => array(0,0,1,"Stichwort",50),	taxnumber => array(0,0,0,"Steuernummer",0),
			bank => array(0,0,1,"Bankname",50),
			bank_code => array(0,0,6,"Bankleitzahl",15),
			account_number => array(0,0,6,"Kontonummer",15),
			branche => array(0,0,1,"Branche",25),	business_id => array(0,0,6,"Kundentyp",0),
			owener => array(0,0,6,"CRM-User",0),	grafik => array(0,0,9,"Grafik",4),
			lead => array(0,0,6,"Leadquelle",0),	leadsrc => array(0,0,1,"Leadquelle",15),
			shiptoname => array(1,0,1,"Liefername",75), 
			shiptostreet => array(1,0,1,"Lieferstrasse",75),
			shiptocountry => array(1,0,8,"Lieferland",3),
			shiptozipcode => array(1,0,2,"Liefer-Plz",10),
			shiptocity => array(1,0,1,"Lieferort",75),
			shiptocontact => array(1,0,1,"Kontakt",75),
			shiptophone => array(1,0,3,"Liefer Telefon",30),
			shiptofax => array(1,0,3,"Lieferfax",30),
			shiptoemail => array(1,0,5,"Liefer-eMail",0),
			shiptodepartment_1 => array(1,0,1,"Lieferzusatzname",75),
			shiptodepartment_2 => array(1,0,1,"Lieferabteilung",75));
	$keys=array_keys($daten);
	$dbf=array_keys($dbfld);
	$anzahl=count($keys);
	$fid=$daten["id"];
	$fehler="ok";
	$ala=false;
	$tels1=array();$tels2=array();
	for ($i=0; $i<$anzahl; $i++) {
		if (in_array($keys[$i],$dbf)) {
			$tmpval=trim($daten[$keys[$i]]);
			if ($dbfld[$keys[$i]][0]==1) {  // select f�r Lieferanschrift bilden
				if ($tmpval) $ala=true;
				if (!chkFld($tmpval,$dbfld[$keys[$i]][1],$dbfld[$keys[$i]][2],$dbfld[$keys[$i]][4])) { 
					$fehler=$dbfld[$keys[$i]][3]; 
					$i=$anzahl; 
				} else {
					if (in_array($dbfld[$keys[$i]][2],array(0,1,2,3,4,5,7,8,9))) {
						$query1.=$keys[$i]."='".$tmpval."',";
					} else {
						$query1.=$keys[$i]."=".$tmpval.",";
					}
					if ($keys[$i]=="Ltel"||$keys[$i]=="Lfax") $tels2[]=$tmpval;
				}
			} else {			// select f�r Rechnungsanschrift bilden
				if (!chkFld($tmpval,$dbfld[$keys[$i]][1],$dbfld[$keys[$i]][2],$dbfld[$keys[$i]][4])) { 
					$fehler=$dbfld[$keys[$i]][3]; 
					$i=$anzahl; 
				} else {
					if (in_array($dbfld[$keys[$i]][2],array(0,1,2,3,4,5,7,8,9))) {
						$query0.=$keys[$i]."='".$tmpval."',";
					} else {
						$query0.=$keys[$i]."=".$tmpval.",";
					}
					if ($keys[$i]=="Rtel"||$keys[$i]=="Rfax") $tels1[]=$tmpval;
				}
			}
		}
	}
	if ($fehler=="ok") {
		if ($daten["customernumber"]) {
			$query0=substr($query0,0,-1);
		} else {
			$query0=$query0."customernumber='".newcustnr()."' ";
		}
		$query1=substr($query1,0,-1)." ";
		$sql0="update customer set $query0 where id=$fid";
		mkTelNummer($fid,"C",$tels1);
		if ($bildok) {
			$typ=array(1=>"gif",2=>"jpeg",3=>"png",4=>false);
			$dir="./dokumente/".$_SESSION["mansel"]."/".$fid;
			$imagesize=getimagesize($datei["Datei"]['tmp_name'],&$info);
			$dest=$dir."/logo.".$typ[$imagesize[2]];
			move_uploaded_file($datei["Datei"]["tmp_name"],"$dest");
			if (($imagesize[1]/$imagesize[0]) > 2.4) {
				$hoehe=ceil($imagesize[1]/$imagesize[0]*120);
				$breite=120;
			} else {
				$breite=ceil($imagesize[0]/$imagesize[1]*80);
				$hoehe=80;
			}
			$image1 = imagecreatetruecolor($breite,$hoehe);
			$tue="\$image=imagecreatefrom".$typ[$imagesize[2]]."('$dest');";
			eval($tue);
			imagecopyresized($image1, $image, 0,0, 0,0,$breite,$hoehe,$imagesize[0],$imagesize[1]);
			$tue="image".$typ[$imagesize[2]]."(\$image1,'$dest');";
			eval($tue);
		}	
		$rc1=true;
		if ($ala) {
			$sql1q="select count(*) from shipto where trans_id=$fid"; //gibt es schon eine Lieferanschrift
			$x=$db->getAll($sql1q);
			if ($x[0]["count"]==0) {
				$sql1a="insert into shipto (trans_id) values ($fid)";
				$rc1=$db->query($sql1a);
			}
			$sql1="update shipto set $query1 where trans_id=$fid";
			$rc1=$db->query($sql1);
			mkTelNummer($fid,"S",$tels2);
		} else {
			$sql1q="select count(*) from shipto where trans_id=$fid"; //gibt es schon eine Lieferanschrift
			$x=$db->getAll($sql1q);
			if ($x[0]["count"]>0) {
				$sql="delete from shipto where trans_id=$fid";
				$rc=$db->query($sql);
			}
		}
		$rc0=$db->query($sql0);
		if ($rc0 and $rc1) { $rc=$fid; }
		else { $rc=-1; $fehler="unbekannt"; };
		return array($rc,$fehler);
	} else {
		if ($daten["saveneu"]){
			$sql="delete from customer where id=".$daten["id"];
			$rc0=$db->query($sql); 
		};
		return array(-1,$fehler);
	};
}

/****************************************************
* newcustnr
* out: id = string
* eine Kundennummer erzeugen 
*****************************************************/
function newcustnr() {
global $db;
	$rc=$db->query("BEGIN");
	$rs=$db->getAll("select customernumber from defaults");
	preg_match("/([^0-9]*)([0-9]+)/",$rs[0]["customernumber"],$t);
	if (count($t)==3) { $y=$t[2]+1; $pre=$t[1]; }
	else { $y=$t[1]+1; $pre=""; };
	$newnr=$pre.$y;
	$rc=$db->query("update defaults set customernumber='$newnr'");
	if ($rc) { $db->query("COMMIT"); }
	else { $db->query("ROLLBACK"); $newnr=""; };
	return $newnr;
}

/****************************************************
* mknewCustomer
* in: id = int
* out: id = int
* Kundensatz erzeugen ( insert )
*****************************************************/
function mknewCustomer($id) {
global $db;
	$newID=uniqid (rand());
	if (!$id) {$uid='null';} else {$uid=$id;};
	$sql="insert into customer (name,employee) values ('$newID',$uid)";
	$rc=$db->query($sql);
	if ($rc) {
		$sql="select id from customer where name = '$newID'";
		$rs=$db->getAll($sql);
		if ($rs) {
			$id=$rs[0]["id"];
		} else {
			$id=false;
		}
	} else {
		$id=false;
	}
return $id;
}


/****************************************************
* saveNeuFirmaStamm
* in: daten = array
* out: rc = int
* KundenDaten sichern ( insert )
*****************************************************/
function saveNeuFirmaStamm($daten) {
	$daten["id"]=mknewCustomer($_SESSION["loginCRM"]);
	$rs=saveFirmaStamm($daten,true);
	return $rs;
}

/****************************************************
* doReportC
* in: data = array
* out: rc = int
* Einen Report �ber Kunden,abweichende Lieferanschrift
* und Kontakte erzeugen
*****************************************************/
function doReportC($data) {
global $db;
	$loginCRM=$_SESSION["loginCRM"];
	$felder=substr($data,0,-1);
	$grp=getGrp($loginCRM);
	$tmp=suchstr($data);
	$where=$tmp["where"]; $tabs=$tmp["tabs"]; 
	$felder=substr($data["felder"],0,-1);
	if (!ereg("P.",$felder)) {
		$rechte="(K.owener=$loginCRM or K.owener is null) ";
		if ($grp) $rechte.=" or K.owener in $grp";
		$where=($where=="")?"":"and $where";
		if (eregi("shipto",$tabs) or ereg("S.",$felder)) {
			$sql="select $felder from customer K left join shipto S ";
			$sql.="on S.trans_id=K.id where ($rechte) $where order by K.name";
		} else {
			$sql="select $felder from customer K where ($rechte) $where order by K.name";
		}
	} else {
		$rechte="((K.owener=$loginCRM or K.owener is null) and ";
		$rechte.="(P.cp_owener=$loginCRM or P.cp_owener is null))";
		if ($grp) $rechte.=" or ((K.owener in $grp) and (P.cp_owener in $grp))";
		$where=($where=="")?"":"and $where";
		if (eregi("shipto",$tabs) or ereg("S.",$felder)) {
			$sql="select $felder from  customer K left join shipto S ";
			$sql.="on S.trans_id=K.id left join contacts P on K.id=P.cp_cv_id ";
			$sql.="where ($rechte) $where order by K.name,P.cp_name";
		} else {
			$sql="select $felder from  customer K left join contacts P ";
			$sql.="on K.id=P.cp_cv_id where ($rechte) $where order by K.name,P.cp_name";
		}
	}
	$rc=$db->getAll($sql);
	$f=fopen("tmp/report_$loginCRM.csv","w");
	fputs($f,$felder."\n");
	if ($rc) {
		foreach ($rc as $row) {
			$tmp="";
			foreach($row as $fld) {
				$tmp.="$fld,";	
			}
			fputs($f,substr($tmp,0,-1)."\n");
		};
		fclose($f);
		return true;
	} else {
		fputs($f,"Keine Treffer.\n");
		fclose($f);
		return false;
	} 
}

function leertpl (&$t,$tpl,$msg="") {
		$typ=getBusiness();
		$lead=getLeads();
		$t->set_file(array("fa1" => "firmen".$tpl.".tpl"));
		$t->set_var(array(
			Btn1 => "",
			Btn2 => "",
			Msg =>	$msg,
			action => "firmen".$tpl.".php",
			ID 	=> "",
			KDNR	=> "",
			name 	=> "",
			department_1	=> "",
			department_2	=> "",
			street	=> "",
			country	=> "",
			zipcode	=> "",
			city	=> "",
			phone	=> "",
			fax	=> "",
			email	=> "",
			homepage => "",
			sw	=> "",
			branche	=> "",
			ustid	=> "",
			taxnumber => "",
			contact => "",
			leadsrc => "",
			notes	=> "",
			bank	=> "",
			bank_code	=> "",
			account_number	=> "",
			terms	=> "",
			kreditlim	=> "",
			op	=> "",
			preisgrp	=> "",
			shiptoname		=> "",
			shiptodepartment_1	=> "",
			shiptodepartment_2	=> "",
			shiptostreet	=> "",
			shiptocountry	=> "",
			shiptozipcode	=> "",
			shiptocity	=> "",
			shiptophone	=> "",
			shiptofax	=> "",
			shiptoemail	=> "",
			shiptocontact	=> "",
			T1		=> " checked",
			T2		=> "",
			T3		=> "",
			Radio   => "&nbsp;alle<input type='radio' name='Typ' value='' checked>",
			init	=> $_SESSION["employee"]
			));
		$t->set_block("fa1","TypListe","BlockT");
		if ($typ) foreach ($typ as $row) {
			$t->set_var(array(
				Bid => $row["id"],
				Bsel => ($row["id"]==$daten["business_id"])?"selected":"",
				Btype => $row["description"]
			));
			$t->parse("BlockT","TypListe",true);
		}
		$t->set_block("fa1","LeadListe","BlockL");
		if ($lead) foreach ($lead as $row) {
			$t->set_var(array(
				Lid => $row["id"],
				Lsel => ($row["id"]==$daten["lead"])?"selected":"",
				Lead => $row["lead"]
			));
			$t->parse("BlockL","LeadListe",true);
		}
		$t->set_block("fa1","OwenerListe","Block");
		$first[]=array("grpid"=>"","rechte"=>"w","grpname"=>"Alle");
		$first[]=array("grpid"=>$_SESSION["loginCRM"],"rechte"=>"w","grpname"=>"Pers&ouml;nlich");
		$tmp=getGruppen();
		if ($mtp) { $user=array_merge($first,getGruppen()); }
		else { $user=$first; };
		$selectOwen=1;
		if ($user) foreach($user as $zeile) {
			if ($zeile["grpid"]==$selectOwen) {
				$sel="selected";
			} else {
				$sel="";
			}
			$t->set_var(array(
				grpid => $zeile["grpid"],
				Gsel => $sel,
				Gname => $zeile["grpname"],
			));
			$t->parse("Block","OwenerListe",true);
		}
} // leertpl
function vartpl (&$t,$daten,$msg,$btn1,$btn2,$tpl) {
		if ($daten["grafik"]) {
			$Image="<img src='dokumente/".$_SESSION["mansel"]."/".$daten["id"]."/logo.".$daten["grafik"]."' ".$daten["size"].">";
		}
		$typ=getBusiness();
		$lead=getLeads();
		$t->set_file(array("fa1" => "firmen".$tpl.".tpl"));
		$t->set_var(array(
				Btn1	=> $btn1,
				Btn2	=> $btn2,
				Msg	=> $msg,
				action	=> "firmen".$tpl.".php",
				ID	=> $daten["id"],
				KDNR	=> $daten["customernumber"],
				name 	=> $daten["name"],
				department_1	=> $daten["department_1"],
				department_2	=> $daten["department_2"],
				street	=> $daten["street"],
				country	=> $daten["country"],
				zipcode	=> $daten["zipcode"],
				city	=> $daten["city"],
				phone	=> $daten["phone"],
				fax	=> $daten["fax"],
				email	=> $daten["email"],
				homepage => $daten["homepage"],
				sw	=> $daten["sw"],
				branche	=> $daten["branche"],
				ustid	=> $daten["ustid"],
				taxnumber => $daten["taxnumber"],
				contact	=> $daten["contact"],
				leadsrc => $daten["leadsrc"],
				notes	=> $daten["notes"],
				bank	=> $daten["bank"],
				bank_code	=> $daten["bank_code"],
				account_number	=> $daten["account_number"],
				terms	=> $daten["terms"],
				kreditlim	=> $daten["creditlimit"],
				op	=> $daten["op"],
				preisgrp	=> $daten["preisgroup"],
				shiptoname	=> $daten["shiptoname"],
				shiptodepartment_1	=> $daten["shiptodepartment_1"],
				shiptodepartment_2	=> $daten["shiptodepartment_2"],
				shiptostreet	=> $daten["shiptostreet"],
				shiptocountry	=> $daten["shiptocountry"],
				shiptozipcode	=> $daten["shiptozipcode"],
				shiptocity	=> $daten["shiptocity"],
				shiptophone	=> $daten["shiptophone"],
				shiptofax	=> $daten["shiptofax"],
				shiptoemail	=> $daten["shiptoemail"],
				shiptocontact	=> $daten["shiptocontact"],
				IMG		=> $Image,
				grafik	=> $daten["grafik"],
				Radio 	=> "",
				T1	=> ($daten["typ"]=="1")?"checked":"",
				T2	=> ($daten["typ"]=="2")?"checked":"",
				T3	=> ($daten["typ"]=="3")?"checked":"",
				init	=> ($daten["employee"])?$daten["employee"]:"ERP",
				login	=> $_SESSION{"login"},
				password	=> $_SESSION{"password"}
		));
		$t->set_block("fa1","TypListe","BlockT");
		if ($typ) foreach ($typ as $row) {
			$t->set_var(array(
				Bid => $row["id"],
				Bsel => ($row["id"]==$daten["business_id"])?"selected":"",
				Btype => $row["description"],
			));
			$t->parse("BlockT","TypListe",true);
		}
		$t->set_block("fa1","LeadListe","BlockL");
		if ($lead) foreach ($lead as $row) {
			$t->set_var(array(
				Lid => $row["id"],
				Lsel => ($row["id"]==$daten["lead"])?"selected":"",
				Lead => $row["lead"],
			));
			$t->parse("BlockL","LeadListe",true);
		}
		if ($daten["employee"]==$_SESSION["loginCRM"]) {
			$t->set_block("fa1","OwenerListe","Block");
			$first[]=array("grpid"=>"","rechte"=>"w","grpname"=>"Alle");
			$first[]=array("grpid"=>$_SESSION["loginCRM"],"rechte"=>"w","grpname"=>"Pers&ouml;nlich");
			$user=array_merge($first,getGruppen());
			$selectOwen=$daten["owener"];
			if ($user) foreach($user as $zeile) {
				if ($zeile["grpid"]==$selectOwen) {
					$sel="selected";
				} else {
					$sel="";
				}
				$t->set_var(array(
					grpid => $zeile["grpid"],
					Gsel => $sel,
					Gname => $zeile["grpname"],
				));
				$t->parse("Block","OwenerListe",true);
			}
		} else {
			$t->set_var(array(
				grpid => $daten["owener"],
				Gsel => "selected",
				Gname => ($daten["owener"])?getOneGrp($daten["owener"]):"&ouml;ffentlich",
			));
			$t->parse("Block","OwenerListe",true);
		}
} // vartpl

?>
