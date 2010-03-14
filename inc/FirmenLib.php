<?php
// $Id: FirmenLib.php $

/****************************************************
* getShipStamm
* in: id = int
* out: rs = array(Felder der db)
* hole die abweichenden Lieferdaten
*****************************************************/
function getShipStamm($id,$tab="C",$complete=false) {
global $db;
    if ($complete) {
        $sql ="select shiptoname,COALESCE(shiptostreet,street) as shiptostreet,COALESCE(shiptocity,city) as shiptocity,";
        $sql.="COALESCE(shiptocountry,country) as shiptocountry,";
        $sql.="COALESCE(shiptozipcode,zipcode) as shiptozipcode,";
        $sql.="COALESCE(shiptodepartment_1,department_1) as shiptodepartment_1,";
        $sql.="COALESCE(shiptodepartment_2,department_2) as shiptodepartment_2,";
        $sql.="COALESCE(shiptocontact,contact) as shiptocontact from shipto left join ".(($tab=="C")?"customer":"vendor")." on trans_id=id ";
        $sql.="where shipto_id=$id";
    } else {
	    $sql="select S.*,BL.bundesland as shiptobundesland from shipto S left join bundesland BL on S.shiptobland=BL.id where S.shipto_id=$id ";
    }
	$rs2=$db->getAll($sql);
	if(!$rs2) {
		return false;
	} else {
		return $rs2[0];
	}
}

/****************************************************
* getAllFirmen
* in: sw = array(Art,suchwort)
* in: tab = string
* out: rs = array(Felder der db)
* hole alle Kunden
*****************************************************/
function getAllFirmen($sw,$Pre=true,$tab='C') {
global $db;
	if ($Pre) $Pre=$_SESSION["Pre"];
	$rechte=berechtigung();
	if (!$sw[0]) {
		 $where="phone like '$Pre".$sw[1]."%' "; 
	} else { 
		if ($sw[1]=="~") { 
			$where="upper(name) ~ '^\[^A-Z\].*$' or ";
			$where.="upper(department_1) ~ '^\[^A-Z\].*$' or ";
			$where.="upper(department_2) ~ '^\[^A-Z\].*$' "; 
		} else  {
			$where="upper(name) like '$Pre".$sw[1]."%' or ";
			$where.="upper(department_1) like '$Pre".$sw[1]."%' or ";
			$where.="upper(department_2) like '$Pre".$sw[1]."%'"; 
		}
	}
	if ($tab=="C") {
		$sql="select *,'C' as tab from customer where ($where) and $rechte order by name";
	} else if ($tab=="V") {
		$sql="select *,'V' as tab from vendor where ($where) and $rechte order by name";
	} else {
		return false;
	}
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
function getFirmenStamm($id,$ws=true,$tab='C') {
global $db;
	if ($tab=="C") {
        // Umsätze holen
		$sql="select sum(amount) from oe where customer_id=$id and quotation='f' and closed = 'f'";
		$rs=$db->getAll($sql);
		$oa=$rs[0]["sum"];
		$sql="select sum(amount) from ar where customer_id=$id and amount<>paid";
		$rs=$db->getAll($sql);
		$op=$rs[0]["sum"];
		$sql="select C.*,E.name as verkaeufer,B.description as kdtyp,B.discount as typrabatt,P.pricegroup,";
        $sql.="L.lead as leadname,BL.bundesland,T.terms_netto from customer C ";
		$sql.="left join employee E on C.salesman_id=E.id left join business B on B.id=C.business_id ";
		$sql.="left join bundesland BL on BL.id=C.bland left join payment_terms T on T.id=C.payment_id ";
		$sql.="left join pricegroup P on P.id=C.klass left join leads L on C.lead=L.id ";
		$sql.="where C.id=$id";
	} else if ($tab=="V") {
        // Umsätze holen
		$sql="select sum(amount) as summe from ap where vendor_id=$id and amount<>paid";
	    $rs=$db->getAll($sql);
        $op=$rs[0]["summe"];
		$sql="select sum(amount) from oe where vendor_id=$id and quotation='f' and closed = 'f'";
		$rs=$db->getAll($sql);
		$oa=$rs[0]["sum"];
		$sql="select C.*,E.name as verkaeufer,B.description as kdtyp,B.discount as typrabatt,BL.bundesland from vendor C ";
	    $sql.="left join employee E on C.salesman_id=E.id left join business B on B.id=C.business_id ";
		$sql.="left join bundesland BL on BL.id=C.bland ";
        $sql.="where C.id=$id";
	} else {
		return false;
	}
	$row=$db->getOne($sql);  // Rechnungsanschrift
	if(!$row) {
		return false;
	} else {
        /* history_erp wird wohl nicht richtig gepflegt, also erst einmal raus
        if ($row["mtime"]=="") {
            $sql = "select * from history_erp where trans_id = $id and snumbers like '%rnumber_%' order by itime desc limit 1";
    	    $rs2 = $db->getOne($sql);  // Rechnungsanschrift
            if ($rs2) if ($rs2["itime"]<>$row["itime"])
               $row["mtime"] = $rs2["itime"];
            $row["modemployee"] = $rs2["employee_id"];
        } else {
            $row["modemployee"] = $row["employee"];
        }*/
            $row["modemployee"] = $row["employee"];
		if ($row["konzern"]) {
			$sql="select name from %s where id = %d";
			if ($tab=="C") {
				$krs=$db->getAll(sprintf($sql,"customer",$row["konzern"]));
			} else {
				$krs=$db->getAll(sprintf($sql,"vendor",$row["konzern"]));
			}
			if ($krs) $row["konzernname"]=$krs[0]["name"];
		}
		if ($tab=="C") {
			$sql="select count(*) from customer where konzern = ".$id;
		} else {
			$sql="select count(*) from vendor where konzern = ".$id;
		}
		$knr=$db->getAll($sql);
		$row["konzernmember"]=$knr[0]["count"];
		if ($tab=="C") { $nummer=$row["customernumber"]; }
		else { $nummer=$row["vendornumber"]; };
		if ($row["grafik"]) {
			$DIR=$tab.$nummer;
			$image="./dokumente/".$_SESSION["mansel"]."/$DIR/logo.".$row["grafik"];
			if (file_exists($image)) {
				$size=@getimagesize($image);
				$row["size"]=$size[3];
				if ($size[1]>$size[0]) {
					$faktor=ceil($size[1]/70);
				} else {
					$faktor=ceil($size[0]/120);
				}
				$breite=floor($size[0]/$faktor);
				$hoehe=floor($size[1]/$faktor);
				$row["icon"]="width=\"$breite\" height=\"$hoehe\"";
			} else {
				$daten["name"]=getcwd()." $image: not found";
			}
		}
		$rs3=getAllShipto($id,$tab);
		$shipcnt=(count($rs3));
		$shipids=array();
		if ($shipcnt>0) {
			for ($sc=0; $sc<$shipcnt; $sc++) {
				$shipids[]="'".$rs3[$sc]["shipto_id"]."'";				
			}
			$shipids=implode(",",$shipids);
		} else {
			$shipids="";
		}
		if (!$rs3[0]) {  // es ist keine abweichende Anschrift da
			if ($ws) {	// soll dann aber mit Re-Anschrift gefüllt werden
				$row2=Array(
					'shiptoname' => $row["name"],
					'shiptodepartment_1' => $row["department_1"],
					'shiptodepartment_2' => $row["department_2"],
					'shiptostreet' => $row["street"],
					'shiptozipcode' => $row["zipcode"],
					'shiptocity' => $row["city"],
					'shiptocountry' => $row["country"],
					'shiptobundesland' => $row["bundesland"],
					'shiptocontact' => "",
					'shiptophone' => $row["phone"],
					'shiptofax' => $row["fax"],
					'shiptoemail' => $row["email"],
					'shiptocountry' => $row["country"],
					'shipto_id' => -1
				);
			} else {  // leeres Array bilden
				$row2=Array(
					'shiptoname' => "",
					'shiptodepartment_1' => "",
					'shiptodepartment_2' => "",
					'shiptostreet' => "",
					'shiptozipcode' => "",
					'shiptocity' => "",
					'shiptocountry' => "",
					'shiptobundesland' => "",
					'shiptocontact' => "",
					'shiptophone' => "",
					'shiptofax' => "",
					'shiptoemail' => "",
					'shiptocountrycountry' => "",
					'shipto_id' => ""
				);
			}
		} else {
			$row2 = $rs3[0];
		}
		$daten=array_merge($row2,$row);
	}
	$daten["shiptocnt"]=($shipcnt>0)?$shipcnt:0;
	$daten["shiptoids"]=$shipids;
	$daten["op"]=$op;
	$daten["oa"]=$oa;
	$daten["nummer"]=$nummer;
	return $daten;
};


/****************************************************
* getAllShipto
* in: id = int
* out: daten = array
* Alle abweichende Anschriften einer Firma holen
*****************************************************/
function getAllShipto($id,$tab="C") {
global $db;
    if (empty($id)) return false;
	//$sql="select distinct shiptoname,shiptodepartment_1,shiptodepartment_2,shiptostreet,shiptozipcode,";
	//$sql.="shiptocity,shiptocountry,shiptocontact,shiptophone,shiptofax,shiptoemail,shipto_id from shipto ";
	//$sql="select (module<>'CT') as vkdoc,* from shipto where trans_id=$id";
	$sql="select s.*,b.bundesland as shiptobundesland from shipto s left join bundesland b on s.shiptobland=b.id ";
	$sql.=" where trans_id=$id and module='CT' order by itime";
	$rs=$db->getAll($sql);  
	return $rs;
}

/****************************************************
* getPaymet
* in: id = int
* out: daten = array
* Alle Zahlungsbedngungen
*****************************************************/
function getPayment() {
global $db;
    $sqlpt = "select * from payment_terms";
    $rspt = $db->getAll($sqlpt);
    $leer=array(array("id"=>"","description"=>"----------"));
    return array_merge($leer,$rspt);
}

/****************************************************
* suchstr
* in: muster = string
* out: daten = array
* Suchstring über customer,shipto zusamensetzen
*****************************************************/
function suchstr($muster,$typ="C") {
	$kenz=array("C" => "K","V" => "L");
	$tab=array("C" => "customer","V" => "vendor");
	
	// Array zu jedem Formularfed: 0=String,2=Int
	$dbfld=array(name => 0, street => 0, zipcode => 0,
			city => 0, phone => 0, fax => 0,
			homepage => 0, email => 0, notes => 0,
			department_1 => 0, department_2 => 0,
			country => 0, typ => 2, sw => 0,
			language => 0, business_id => 2,
			ustid => 0, taxnumber => 0, lead => 2, leadsrc => 0,
			bank => 0, bank_code => 0, account_number => 0,
			vendornumber => 0, v_customer_id => 0,
			kundennummer => 0, customernumber => 0,
			employee => 2, branche => 0, headcount => 2);
	$dbfld2=array(name => "shiptoname", street=>"shiptostreet",zipcode=>"shiptozipcode",
			city=>"shiptocity",phone=>"shiptophone",fax=>"shiptofax",
			email=>"shiptoemail",department_1=>"shiptodepartment_1",
			department_2=>"shiptodepartment_2",country=>"shiptocountry");
	$fuzzy2=$muster["fuzzy"];
	$fuzzy1=($muster["pre"])?$_SESSION["Pre"]:"";
	$keys=array_keys($muster);
	$suchfld=array_keys($dbfld);
	$anzahl=count($keys);
	$tbl0=false;
	if ($muster["shipto"]){$tbl1=true;} else {$tbl1=false;}
	$tmp1=""; $tmp2="";
	for ($i=0; $i<$anzahl; $i++) {
		if (in_array($keys[$i],$suchfld) and $muster[$keys[$i]]<>"") {
            $suchwort=trim($muster[$keys[$i]]);
			$suchwort=strtr($suchwort,"*?","%_");
			if ($dbfld[$keys[$i]]==2) {
                $search=array();
                preg_match_all("/([<>]?[\d]+)/",$suchwort,$treffer);
                if ($treffer[0]) {
                    foreach ($treffer[0] as $val) {
                        if ($val[0] == ">" || $val[0] == "<") {
                            $search[] = $kenz[$typ].".".$keys[$i]." ".$val[0]." ".substr($val,1);
                        } else {
                            $search[] = $kenz[$typ].".".$keys[$i]." = ".$val;
                        }
                    }
                    $suchwort = "( ".implode(" and ",$search)." )";
			        $tmp1.="and ".$suchwort." ";
                }
			} else {
			    if ($tbl1 && $dbfld2[$keys[$i]]) {
				    $tmp1.="and (S.".$dbfld2[$keys[$i]]." ilike '$fuzzy1".$suchwort."$fuzzy2' ";
			        $tmp1.="or ".$kenz[$typ].".".$keys[$i]." ilike '$fuzzy1".$suchwort."$fuzzy2' ) ";
                } else {
			        $tmp1.="and ".$kenz[$typ].".".$keys[$i]." ilike '$fuzzy1".$suchwort."$fuzzy2' ";
                }
			}
		}
	}
	if ($muster["sonder"]) {
		foreach ($muster["sonder"] as $row) {
			$x+=$row;
		}
		$tmp1.="and (".$kenz[$typ].".sonder & $x) = $x";
	}
	if ($tbl1) {
		$tabs=$tab[$typ]." ".$kenz[$typ]." left join shipto S on ".$kenz[$typ].".id=S.trans_id";
	} else {
		$tabs=$tab[$typ]." ".$kenz[$typ];
	}
	if ($tmp1) $where=substr($tmp1,3);
	return array("where"=>$where,"tabs"=>$tabs); 
}

/****************************************************
* suchFirma
* in: muster = string
* out: daten = array
* KundenDaten suchen
*****************************************************/
function suchFirma($muster,$tab="C") {
global $db;
	$rechte=berechtigung();
	$tmp=suchstr($muster,$tab);
	$where=$tmp["where"]; 
	$tabs=$tmp["tabs"];
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
function getName($id,$typ="C") {
global $db;
	$tab=array("C" => "customer","V" => "vendor");
	$sql="select name from ".$tab[$typ]." where id = $id";
	$rs=$db->getAll($sql);
	if ($rs) {
		return $rs[0]["name"];
	} else {
		return false;
	}
}

function chkTimeStamp($tabelle,$id,$stamp,$begin=false) {
global $db;
	if ($tabelle=="contacts") {
		$sql = "select mtime from $tabelle where cp_id = $id";
	} else {
		$sql = "select mtime from $tabelle where id = $id";
	}
	$rs = $db->getOne($sql);
	if ($rs["mtime"]<=$stamp) {
		if ($begin) $db->begin();
		return true;
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
function saveFirmaStamm($daten,$datei,$typ="C",$neu=false) {
global $db;
	$kenz=array("C" => "K","V" => "L");
	$tab=array("C" => "customer","V" => "vendor");
	$tmp=0;
	if ($daten["sonder"]) foreach ($daten["sonder"] as $data) {
		$tmp+=$data;
	}
	$daten["sonder"]=$tmp;
	if (!empty($datei["Datei"]["name"])) {  		// eine Datei wird mitgeliefert
			$pictyp=array("gif","jpeg","png","jpg");
			$ext=substr($datei["Datei"]["name"],strrpos($datei["Datei"]["name"],".")+1);
			if (in_array($ext,$pictyp)) {
				$daten["grafik"]=$ext;
				$datei["Datei"]['name']="logo.$ext";
				$bildok=true;
			}
	};
	// Array zu jedem Formularfed: Tabelle (0=customer/vendor,1=shipto), require(0=nein,1=ja), Spaltenbezeichnung, Regel
	$dbfld=array(	name => array(0,1,1,"Name",75),			greeting => array(0,0,1,"Anrede",75),
	    department_1 => array(0,0,1,"Zusatzname",75),	department_2 => array(0,0,1,"Abteilung",75),
        country => array(0,0,8,"Land",25),	        	zipcode => array(0,1,2,"Plz",10),
        city => array(0,1,1,"Ort",75),		        	street => array(0,1,1,"Strasse",75),
        fax => array(0,0,3,"Fax",30),		        	phone => array(0,0,3,"Telefon",30),
        email => array(0,0,5,"eMail",0),	        	homepage =>array(0,0,4,"Homepage",0),
	    contact => array(0,0,1,"Kontakt",75),		    v_customer_id => array(0,0,1,"Kundennummer",50),
	    //vendornumber => array(0,0,0,"Lieferantennummer",20),
	    //customernumber => array(0,0,0,"Kundennummer",20),	
	    sw => array(0,0,1,"Stichwort",50),	        	notes => array(0,0,0,"Bemerkungen",0),
	    ustid => array(0,0,0,"UStId",0),	        	taxnumber => array(0,0,0,"Steuernummer",0),
	    bank => array(0,0,1,"Bankname",50),	        	bank_code => array(0,0,6,"Bankleitzahl",15),
	    iban => array(0,0,1,"IBAN",24),	        	    bic => array(0,0,1,"BIC",15),
	    account_number => array(0,0,6,"Kontonummer",15),
	    payment_id => array(0,0,6,"Zahlungsbedingungen",0),
	    branche => array(0,0,1,"Branche",25),	    	business_id => array(0,0,6,"Kundentyp",0),
	    owener => array(0,0,6,"CRM-User",0),	    	grafik => array(0,0,9,"Grafik",4),
	    lead => array(0,0,6,"Leadquelle",0),	    	leadsrc => array(0,0,1,"Leadquelle",15),
	    bland => array(0,0,6,"Bundesland",0),	    	taxzone_id => array(0,1,6,"Steuerzone",0),
	    sonder => array(0,0,10,"SonderFlag",0),	        salesman_id => array(0,0,6,"Vertriebler",0),
	    shiptoname => array(1,0,1,"Liefername",75), 	konzern	=> array(0,0,6,"Konzern",0),
	    shiptostreet => array(1,0,1,"Lieferstrasse",75),
	    shiptobland => array(1,0,6,"Liefer-Bundesland",0),
	    shiptocountry => array(1,0,8,"Lieferland",3),
	    shiptozipcode => array(1,0,2,"Liefer-Plz",10),
	    shiptocity => array(1,0,1,"Lieferort",75),
	    shiptocontact => array(1,0,1,"Kontakt",75),
	    shiptophone => array(1,0,3,"Liefer Telefon",30),
	    shiptofax => array(1,0,3,"Lieferfax",30),
	    shiptoemail => array(1,0,5,"Liefer-eMail",0),
	    shiptodepartment_1 => array(1,0,1,"Lieferzusatzname",75),
	    shiptodepartment_2 => array(1,0,1,"Lieferabteilung",75),
        headcount => array(0,0,6,"Anzahl Mitarbeiter",10));
	    $keys=array_keys($daten);
	$dbf=array_keys($dbfld);
	$anzahl=count($keys);
	$fid=$daten["id"];
	$fehler="ok";
	$ala=false;
	if ($daten["greeting_"]<>"") $daten["greeting"]=$daten["greeting_"];
	if ($daten["branche_"]<>"") $daten["branche"]=$daten["branche_"];
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
					if (in_array($dbfld[$keys[$i]][2],array(0,1,2,3,4,5,7,8,9))) { //Daten == Zeichenkette
                        if (empty($tmpval)) {
						    $query1.=$keys[$i]."=null,";
                        } else {
						    $query1.=$keys[$i]."='".$tmpval."',";
                        }
					} else {							//Daten == Zahl
                        if (empty($tmpval) && !$tmpval===0) {
						    $query1.=$keys[$i]."=null,";
                        } else {
						    $query1.=$keys[$i]."=".$tmpval.",";
                        }
					}
					if ($keys[$i]=="shiptophone"||$keys[$i]=="shiptofax") $tels2[]=$tmpval;
				}
			} else {			// select f�r Rechnungsanschrift bilden
				if (!chkFld($tmpval,$dbfld[$keys[$i]][1],$dbfld[$keys[$i]][2],$dbfld[$keys[$i]][4])) { 
					$fehler=$dbfld[$keys[$i]][3]; 
					$i=$anzahl; 
				} else {
					if (in_array($dbfld[$keys[$i]][2],array(0,1,2,3,4,5,7,8,9))) {
                        if (empty($tmpval)) {
						    $query0.=$keys[$i]."=null,";
                        } else {
						    $query0.=$keys[$i]."='".$tmpval."',";
                        }
					} else {							//Daten == Zahl
                        if (empty($tmpval) && !$tmpval===0) {
						    $query0.=$keys[$i]."=null,";
                        } else {
						    $query0.=$keys[$i]."=".$tmpval.",";
                        }
					}
					if ($keys[$i]=="phone"||$keys[$i]=="fax") $tels1[]=$tmpval;
				}
			}
		}
	}
	if ($daten["direct_debit"]=="t") {
		if (empty($daten["bank"]) or empty($daten["account_number"]) or empty($daten["bank_code"])) {
			$fehler="Lastschrift: Bankverbindung fehlt";
		} else {
			$query0.="direct_debit='t',";
		}
	} else {
			$query0.="direct_debit='f',";
	}	
	if ($fehler=="ok") {
		if ($daten["customernumber"]) {
			$query0=substr($query0,0,-1);
			$DIR="C".$daten["customernumber"];
		} else if ($daten["vendornumber"]) {
			$query0=substr($query0,0,-1);
			$DIR="V".$daten["vendornumber"];
		} else {
			$tmpnr=newnr($tab[$typ],$daten["business_id"]);
			if ($typ=="C") {
				$DIR="C".$tmpnr;
				$query0=$query0."customernumber='$tmpnr' ";
			} else {
				$DIR="V".$tmpnr;
				$query0=$query0."vendornumber='$tmpnr' ";
			}
		}
		$query1=substr($query1,0,-1)." ";
		$sql0="update ".$tab[$typ]." set $query0 where id=$fid";
		mkTelNummer($fid,$typ,$tels1);
		if ($bildok) {
			require_once("documents.php");
			$dbfile=new document();
			$dbfile->setDocData("descript","Firmenlogo von ".$daten["name"]);
			$dbfile->uploadDocument($datei,"/$DIR");
		}	
		$rc1=true;
		if ($ala) {
			if ($daten["shipto_id"]>0) {
				$sql1="update shipto set $query1 where shipto_id=".$daten["shipto_id"];
				$rc1=$db->query($sql1);
			} else {
				$sid=newShipto($fid);
				if ($sid) {
					$sql1="update shipto set $query1 where shipto_id=".$sid;
					$rc1=$db->query($sql1);
				}
			};
			if ($rc1) mkTelNummer($fid,"S",$tels2);
		}
		$rc0=$db->query($sql0);
		if ($rc0 and $rc1) { $rc=$fid; }
		else { $rc=-1; $fehler=".:unknown:."; };
		return array($rc,$fehler);
	} else {
		if ($daten["saveneu"]){
			$sql="delete from ".$tab[$typ]." where id=".$daten["id"];
			$rc0=$db->query($sql); 
		};
		return array(-1,$fehler);
	};
}

function newShipto($fid) {
global $db;
	$rc=$db->query("BEGIN");
	$newID=uniqid (rand());
	$sql="insert into shipto (trans_id,shiptoname,module) values ($fid,'$newID','CT')";
	$rc=$db->query($sql);
	$sql="select shipto_id from shipto where shiptoname='$newID'";
	$rs=$db->getAll($sql);
	if ($rs[0]["shipto_id"]) { 
		$db->query("COMMIT");
		return $rs[0]["shipto_id"];
	} else {
		$db->query("ROLLBACK");
		return false;
	}
}

/****************************************************
* newcustnr
* out: id = string
* eine Kundennummer erzeugen 
*****************************************************/
function newnr($typ,$bid=0) {
global $db;
	$rc=$db->query("BEGIN");
	if ($bid>0) {
		$rs=$db->getAll("select customernumberinit  as ".$typ."number from business where id = $bid");
	} else {
		$rs=$db->getAll("select ".$typ."number from defaults");
	};
	preg_match("/([^0-9]*)([0-9]+)/",$rs[0][$typ."number"],$t);
	if (count($t)==3) { $y=$t[2]+1; $pre=$t[1]; }
	else { $y=$t[1]+1; $pre=""; };
	$newnr=$pre.$y;
	if ($bid>0) {
		$rc=$db->query("update business set customernumberinit='$newnr' where id = $bid");
	} else {
		$rc=$db->query("update defaults set ".$typ."number='$newnr'");
	}
	if ($rc) { $db->query("COMMIT"); }
	else { $db->query("ROLLBACK"); $newnr=""; };
	return $newnr;
}

/****************************************************
* mknewFirma
* in: id = int
* out: id = int
* Kundensatz erzeugen ( insert )
*****************************************************/
function mknewFirma($id,$typ) {
global $db;
	$tab=array("C" => "customer","V" => "vendor");
	$newID=uniqid (rand());
	if (!$id) {$uid='null';} else {$uid=$id;};
	$sql="insert into ".$tab[$typ]." (name,employee) values ('$newID',$uid)";
	$rc=$db->query($sql);
	if ($rc) {
		$sql="select id from ".$tab[$typ]." where name = '$newID'";
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
function saveNeuFirmaStamm($daten,$files,$typ="C") {
	$daten["id"]=mknewFirma($_SESSION["loginCRM"],$typ);
	$rs=saveFirmaStamm($daten,$files,$typ);
	return $rs;
}


function getKonzerne($fid,$Q,$typ="T") {
global $db;
	if ($Q=="C") $tab="customer";
	else $tab="vendor";
	if ($typ=="T") {
		if ($Q=="C") $sql="select id,name,zipcode,city,country,customernumber as number,konzern from customer where konzern = $fid";
		else $sql="select id,name,zipcode,city,country,vendornumber as number,konzern from $tab where konzern = $fid";
	} else {
		if ($Q=="C") $sql="select id,name,zipcode,city,country,customernumber as number,konzern from customer where id = $fid";
		else $sql="select id,name,zipcode,city,country,vendornumber as number,konzern from $tab where id = $fid";
	}
	$rs=$db->getAll($sql);
	return $rs;
}
function getCustTermin($id,$tab,$day) {
global $db;
    if ($tab=="P") {
        $sql="select * from  termine T left join terminmember M on T.id=M.termin where M.member = $id ";
    } else {
        $sql="select T.*,C.cp_name,X.id as cid from  termine T left join terminmember M on T.id=M.termin ";
        $sql.="left join contacts C on C.cp_id=M.Member left join telcall X on X.termin_id=T.id ";
        $sql.="where M.member in ";
        $sql.="($id,(select cp_id from contacts where cp_cv_id = 473))";
    }
    if ($day) {
        $sql.=" and start = '$day 00:00:00'";
    } else {
        $day=date("Y-m-d 00:00:00");
        $sql.= " and start >= '$day' order by start limit 5 ";
    }
    $rs = $db->getAll($sql);
    return $rs;
}
/****************************************************
* doReportC
* in: data = array
* out: rc = int
* Einen Report �ber Kunden,abweichende Lieferanschrift
* und Kontakte erzeugen
*****************************************************/
function doReport($data,$typ="C") {
global $db;
	$kenz=array("C" => "K","V" => "L");
	$tab=array("C" => "customer","V" => "vendor");
	$loginCRM=$_SESSION["loginCRM"];
	$felder=substr($data['felder'],0,-1);
	$tmp=suchstr($data,$typ);
	$where=$tmp["where"]; $tabs=$tmp["tabs"]; 
	if ($typ=="C") {
		$rechte="(".berechtigung("K.").")";
	} else {
		$rechte="true";
	}
	if (!ereg("P.",$felder)) {
		$where=($where=="")?"":"and $where";
		if (eregi("shipto",$tabs) or ereg("S.",$felder)) {
			$sql="select $felder from ".$tab[$typ]." ".$kenz[$typ]." left join shipto S ";
			$sql.="on S.trans_id=".$kenz[$typ].".id where (S.module='CT' or S.module is null) and $rechte $where order by ".$kenz[$typ].".name";
		} else {
			$sql="select $felder from ".$tab[$typ]." ".$kenz[$typ]." where $rechte $where order by ".$kenz[$typ].".name";
		}
	} else {
		$rechte.=(($rechte)?" and (":"(").berechtigung("P.cp_").")";
		$where=($where=="")?"":"and $where";
		if (eregi("shipto",$tabs) or ereg("S.",$felder)) {
			$sql="select $felder from ".$tab[$typ]." ".$kenz[$typ]." left join shipto S ";
			$sql.="on S.trans_id=".$kenz[$typ].".id left join contacts P on ".$kenz[$typ].".id=P.cp_cv_id ";
			$sql.="where (S.module='CT' or S.module is null)  and $rechte $where order by ".$kenz[$typ].".name,P.cp_name";
		} else {
			$sql="select $felder from  ".$tab[$typ]." ".$kenz[$typ]." left join contacts P ";
			$sql.="on ".$kenz[$typ].".id=P.cp_cv_id where $rechte $where order by ".$kenz[$typ].".name,P.cp_name";
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
function getAnreden() {
global $db;
	$sql="select distinct (greeting) from customer";
	$rs=$db->getAll($sql);
	return $rs;
}
function getBranchen() {
global $db;
	$sql="select distinct (branche) from customer";
	$rs=$db->getAll($sql);
	return $rs;
}
function getVariablen($id) {
global $db;
	if (!($id>0)) return false;
	$sql="select C.name,C.description,V.text_Value from  ";
	$sql.="custom_variables V left join custom_variable_configs C on V.config_id=C.id ";
	$sql.="where trans_id = $id";
	$rs=$db->getAll($sql);
	return $rs;
}
function leertpl (&$t,$tpl,$typ,$msg="",$suchmaske=false) {
global $xajax,$GEODB,$BLZDB;
        $cp_sonder = getSonder(False);
		$kdtyp=getBusiness();
		$bundesland=getBundesland(false);
		$lead=getLeads();
		$t->set_file(array("fa1" => "firmen".$tpl.".tpl"));
		$t->set_var(array(
			AJAXJS	=> $xajax->printJavascript(XajaxPath),
			FAART => ($typ=="C")?".:Customer:.":".:Vendor:.",
			Q => $typ,
			Btn1 => "",
			Btn2 => "",
			Msg =>	$msg,
			action => "firmen".$tpl.".php?Q=$typ",
			id 	=> "",
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
			branche_	=> "",
			vendornumber    => "",
			customernumber  => "",
			kdnr	=> "",
            v_customer_id   => "",
			ustid	=> "",
			taxnumber => "",
			contact => "",
			leadsrc => "",
			notes	=> "",
			bank	=> "",
			bank_code	=> "",
            iban    => "",
            bic     => "",
            headcount => "",
			account_number	=> "",
			direct_debitf   => "checked",
			terms		=> "",
			kreditlim	=> "",
			op		=> "",
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
			GEODB           => ($GEODB)?'1==1':'1>2',
			GEOS		=> ($GEODB)?"visible":"hidden",
			GEO1		=> ($GEODB)?"":"!--",
			GEO2		=> ($GEODB)?"":"--",
			BLZ1		=> ($BLZDB)?"":"!--",
			BLZ2		=> ($BLZDB)?"":"--",
			employee => $_SESSION["loginCRM"],
			Radio   => "&nbsp;alle<input type='radio' name='Typ' value='' checked>",
			init	=> $_SESSION["employee"],
			txid0 => "selected",
			variablen => "" 
			));
		$t->set_block("fa1","TypListe","BlockT");
		if ($kdtyp) foreach ($kdtyp as $row) {
			$t->set_var(array(
				Bid => $row["id"],
				Bsel => '', //($row["id"]==$daten["business_id"])?"selected":"",
				Btype => $row["description"]
			));
			$t->parse("BlockT","TypListe",true);
		}
		if ($typ=="C") {
            $lead=getLeads();
            $t->set_block("fa1","LeadListe","BlockL");
            if ($lead) foreach ($lead as $row) {
                    $t->set_var(array(
                            Lid => $row["id"],
                            Lsel => ($row["id"]==$daten["lead"])?"selected":"",
                            Lead => $row["lead"],
                    ));
                    $t->parse("BlockL","LeadListe",true);
            }
        }
        $t->set_block("fa1","sonder","BlockF");
        if ($cp_sonder) foreach ($cp_sonder as $row) {
            $t->set_var(array(
                sonder_id => $row["svalue"],
                sonder_key => $row["skey"]
            ));
            $t->parse("BlockF","sonder",true);
        }

		$anreden=getAnreden();
		$t->set_block("fa1","anreden","BlockA");
		if ($anreden) foreach ($anreden as $anrede) {
			$t->set_var(array(
				ANREDE	=> $anrede["greeting"],
				ASEL	=> ($anrede["greeting"]==$daten["greeting"])?"selected":"",
			));
			$t->parse("BlockA","anreden",true);
		}
		$payment=getPayment();
		$t->set_block("fa1","payment","BlockP");
		if ($payment) foreach ($payment as $pt) {
			$t->set_var(array(
				PAYMENT	=> $pt["description"],
				Pid 	=> $pt["id"],
				PSEL	=> ($pt["id"]==$daten["payment_id"])?"selected":"",
			));
			$t->parse("BlockP","payment",true);
		}
		$branchen=getBranchen();
		$t->set_block("fa1","branchen","BlockR");
		if ($branchen) foreach ($branchen as $branche) {
			$t->set_var(array(
				BRANCHE	=> $anrede["branche"],
				BSEL	=> ($anrede["branche"]==$daten["branche"])?"selected":"",
			));
			$t->parse("BlockR","anchen",true);
		}
		if (!$suchmaske) {
      if (isset($daten["id"])){		//beim Neuanlegen nicht überprüfen ...
        $shiptos=getAllShipto($daten["id"],$tpl);
      }
			$t->set_block("fa1","shiptos","BlockST");
			if ($shiptos) foreach ($shiptos as $ship) {
				$t->set_var(array(
					SHIPTO  => $ship["shiptoname"]." ".$ship["shiptodepartment_1"],
					SHIPID  => $ship["shipto_id"],
				));
				$t->parse("BlockST","shiptos",true);
			}
		}
		$bundesland=getBundesland(strtoupper($daten["country"]));
		$t->set_block("fa1","buland","BlockB");
                if ($bundesland) foreach ($bundesland as $bland) {
                        $t->set_var(array(
                                BUVAL => $bland["id"],
                                BUTXT => $bland["bundesland"],
                                BUSEL => ($bland["id"]==$daten["bland"])?"selected":""
                        ));
                        $t->parse("BlockB","buland",true);
                }
		if (!$suchmaske) {
			$bundesland=getBundesland(strtoupper($daten["shiptocountry"]));
			$t->set_block("fa1","buland2","BlockBS");
			if ($bundesland) foreach ($bundesland as $bland) {
				$t->set_var(array(
					SBUVAL => $bland["id"],
					SBUTXT => $bland["bundesland"],
					SBUSEL => ($bland["id"]==$daten["shiptobland"])?"selected":""
				));
				$t->parse("BlockBS","buland2",true);
			}
			$employees=getAllUser(array(0=>true,1=>"%"));
			$t->set_block("fa1","SalesmanListe","BlockBV");
			if ($employees) foreach ($employees as $vk) {
				$t->set_var(array(
					salesmanid => $vk["id"],
					Salesman => $vk["name"],
					Ssel => ($vk["id"]==$daten["salesman_id"])?"selected":""
				));
				$t->parse("BlockBV","SalesmanListe",true);
			}
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
		$first[]=array("grpid"=>"","rechte"=>"w","grpname"=>"public");
		$first[]=array("grpid"=>$_SESSION["loginCRM"],"rechte"=>"w","grpname"=>"private");
		$tmp=getGruppen();
		if ($tmp) { $user=array_merge($first,$tmp); }
		else { $user=$first; };
		if ($user) foreach($user as $zeile) {
			$t->set_var(array(
				grpid => $zeile["grpid"],
				Gsel => "",
				Gname => $zeile["grpname"],
			));
			$t->parse("Block","OwenerListe",true);
		}
} // leertpl

function vartpl (&$t,$daten,$typ,$msg,$btn1,$btn2,$tpl,$suchmaske=false) {
global $xajax,$GEODB,$BLZDB;
        $cp_sonder = getSonder(False);
		if ($daten["grafik"]) {
			if ($typ=="C") { $DIR="C".$daten["customernumber"]; }
			else { $DIR="V".$daten["vendornumber"]; };
			if (file_exists("dokumente/".$_SESSION["mansel"]."/$DIR/logo.".$daten["grafik"])) {
				$Image="<img src='dokumente/".$_SESSION["mansel"]."/$DIR/logo.".$daten["grafik"]."' ".$daten["icon"].">";
			} else {
				$Image="Bild ($DIR/logo.".$daten["grafik"].") nicht<br>im Verzeichnis";
			}
		}
		$kdtyp=getBusiness();
		if (!$suchmaske) $tmp=getVariablen($daten["id"]);
		$varablen=($tmp>0)?"$tmp Variablen":"";
		$t->set_file(array("fa1" => "firmen".$tpl.".tpl"));
		$t->set_var(array(
				AJAXJS	=> $xajax->printJavascript(XajaxPath),
			    FAART => ($typ=="C")?".:Customer:.":".:Vendor:.",
				mtime	=> $daten["mtime"],
				Q => $typ,
				Btn1	=> $btn1,
				Btn2	=> $btn2,
				Msg	=> $msg,
				action	=> "firmen".$tpl.".php?Q=$typ",
				id	=> $daten["id"],
                customernumber  => $daten["customernumber"],
                vendornumber    => $daten["vendornumber"],
				kdnr	=>  $daten["nummer"],
				v_customer_id   => $daten["v_customer_id"],
				name 	=> $daten["name"],
				greeting_ 	=> $daten["greeting_"],
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
				konzern => $daten["konzern"],
				konzernname => $daten["konzernname"],
				branche_	=> $daten["branche_"],
				ustid	=> $daten["ustid"],
				taxnumber => $daten["taxnumber"],
				contact	=> $daten["contact"],
				leadsrc => $daten["leadsrc"],
				notes	=> $daten["notes"],
				bank	=> $daten["bank"],
				bank_code	=> $daten["bank_code"],
				iban	=> $daten["iban"],
				bic	    => $daten["bic"],
                headcount => $daten["headcount"],
				direct_debit.$daten["direct_debit"] => "checked",
				account_number	=> $daten["account_number"],
				terms		=> $daten["terms"],
				kreditlim	=> $daten["creditlimit"],
				op		=> $daten["op"],
				preisgrp	=> $daten["preisgroup"],
			/*	shipto_id	=> $daten["shipto_id"],
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
				shiptocontact	=> $daten["shiptocontact"], */
				IMG		=> $Image,
				grafik	=> $daten["grafik"],
				Radio 	=> "",
				T1	=> ($daten["typ"]=="1")?"checked":"",
				T2	=> ($daten["typ"]=="2")?"checked":"",
				T3	=> ($daten["typ"]=="3")?"checked":"",
				init	=> ($daten["employee"])?$daten["employee"]:"ERP ".$daten["modemployee"],
				login	=> $_SESSION{"login"},
				employee => $_SESSION["loginCRM"],
				password	=> $_SESSION["password"],
				txid.$daten["taxzone_id"] => "selected",
				GEODB           => ($GEODB)?'1==1':'1>2',
				GEOS		=> ($GEODB)?"visible":"hidden",
				GEO1		=> ($GEODB)?"":"!--",
				GEO2		=> ($GEODB)?"":"--",
				BLZ1		=> ($BLZDB)?"":"!--",
				BLZ2		=> ($BLZDB)?"":"--",
				variablen => $varablen
		));
		$t->set_block("fa1","TypListe","BlockT");
		if ($kdtyp) foreach ($kdtyp as $row) {
			$t->set_var(array(
				Bid => $row["id"],
				Bsel => ($row["id"]==$daten["business_id"])?"selected":"",
				Btype => $row["description"],
			));
			$t->parse("BlockT","TypListe",true);
		}
		if ($typ=="C") {
			$lead=getLeads();
			$t->set_block("fa1","LeadListe","BlockL");
			if ($lead) foreach ($lead as $row) {
				$t->set_var(array(
					Lid => $row["id"],
					Lsel => ($row["id"]==$daten["lead"])?"selected":"",
					Lead => $row["lead"],
				));
				$t->parse("BlockL","LeadListe",true);
			}
		}
        $t->set_block("fa1","sonder","BlockF");
        if ($cp_sonder) foreach ($cp_sonder as $row) {
            $t->set_var(array(
                sonder_sel => ($daten["sonder"] & $row["svalue"])?"checked":"",
                sonder_id => $row["svalue"],
                sonder_key => $row["skey"]
            ));
            $t->parse("BlockF","sonder",true);
        }
        $shiptos=getAllShipto($daten["id"],$typ);
		$t->set_block("fa1","shiptos","BlockS");
		if ($shiptos) foreach ($shiptos as $ship) {
			$t->set_var(array(
                SHIPID  => $ship["shipto_id"],
                SHIPTO  => $ship["shiptoname"].", ".$ship["shiptostreet"].", ".$ship["shiptocity"]
			));
			$t->parse("BlockS","shiptos",true);
		}
		$anreden=getAnreden();
		$t->set_block("fa1","anreden","BlockA");
		if ($anreden) foreach ($anreden as $anrede) {
			$t->set_var(array(
				ANREDE	=> $anrede["greeting"],
				ASEL	=> ($anrede["greeting"]==$daten["greeting"])?"selected":"",
			));
			$t->parse("BlockA","anreden",true);
		}
		$payment=getPayment();
		$t->set_block("fa1","payment","BlockP");
		if ($payment) foreach ($payment as $pt) {
			$t->set_var(array(
				PAYMENT	=> $pt["description"],
				Pid 	=> $pt["id"],
				PSEL	=> ($pt["id"]==$daten["payment_id"])?"selected":"",
			));
			$t->parse("BlockP","payment",true);
		}
		$branchen=getBranchen();
		$t->set_block("fa1","branchen","BlockR");
		if ($branchen) foreach ($branchen as $branche) {
			$t->set_var(array(
				BRANCHE	=> $branche["branche"],
				BSEL	=> ($branche["branche"]==$daten["branche"])?"selected":"",
			));
			$t->parse("BlockR","branchen",true);
		}
		$bundesland=getBundesland(strtoupper($daten["country"]));
		$t->set_block("fa1","buland","BlockB");
		if ($bundesland) foreach ($bundesland as $bland) {
			$t->set_var(array(
				BUVAL => $bland["id"],
				BUTXT => $bland["bundesland"],
				BUSEL => ($bland["id"]==$daten["bland"])?"selected":""
			));
			$t->parse("BlockB","buland",true);
		}
		if (!$suchmaske) {
			$bundesland=getBundesland(strtoupper($daten["shiptocountry"]));
			$t->set_block("fa1","buland2","BlockBS");
			if ($bundesland) foreach ($bundesland as $bland) {
				$t->set_var(array(
					SBUVAL => $bland["id"],
					SBUTXT => $bland["bundesland"],
					SBUSEL => ($bland["id"]==$daten["shiptobland"])?"selected":""
				));
				$t->parse("BlockBS","buland2",true);
			}
			$employees=getAllUser(array(0=>true,1=>"%"));
			$t->set_block("fa1","SalesmanListe","BlockBV");
			if ($employees) foreach ($employees as $vk) {
				$t->set_var(array(
					salesmanid => $vk["id"],
					Salesman => $vk["name"],
					Ssel => ($vk["id"]==$daten["salesman_id"])?"selected":""
				));
				$t->parse("BlockBV","SalesmanListe",true);
			}
            /* Check if the user is allowed to change the access group - Behaviour changed by DO: 
                Let (all) users change the group if none is set yet */
			if (!isset($daten["employee"]) || $daten["employee"]==$_SESSION["loginCRM"] || $daten["modemployee"]==$_SESSION["loginCRM"] ) {
					$t->set_block("fa1","OwenerListe","Block");
					$first[]=array("grpid"=>"","rechte"=>"w","grpname"=>"Alle");
					$first[]=array("grpid"=>$_SESSION["loginCRM"],"rechte"=>"w","grpname"=>".:personal:.");
					$grps=getGruppen();
					if ($grps) {
						$user=array_merge($first,getGruppen());
					} else {
						$user=$first;
					}
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
						Gname => ($daten["owener"])?getOneGrp($daten["owener"]):".:public:.",
					));
					$t->parse("Block","OwenerListe",true);
			}
		} //if (!$suchmaske)
} // vartpl

?>
