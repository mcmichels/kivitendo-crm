<!-- $Id$ -->
<html>
	<head><title></title>
	<link type="text/css" REL="stylesheet" HREF="css/main.css"></link>
	<link type="text/css" REL="stylesheet" HREF="css/tabcontent.css"></link>
	{AJAXJS}
	<script language="JavaScript">
	<!--
	function vcard() {
			f1=open("vcard.php?src=F","vcard","width=350,height=200,left=100,top=100");
		}
	var last = 'tab2';
	function submenu(id) {
			document.getElementById(last).style.visibility='hidden';
			document.getElementById(id).style.visibility='visible';
			men='sub' + id; 
			document.getElementById('sub'+id).className="selected";
			document.getElementById('sub'+last).className="shadetabs";
			last=id;
		}
	function mkBuland(tab) {
		if (tab=="bland") {
			L=document.neueintrag.country.value
		} else {
			L=document.neueintrag.shiptocountry.value
		};
		xajax_Buland(L,tab);
	}
	function getShipadress() {
		x=document.neueintrag.shiptoadress.selectedIndex;
		if (x>0) {
			y=document.neueintrag.shiptoadress.options[x].value;
			xajax_getShipto(y)
		} else {
			document.neueintrag.shipto_id.value="";
			document.neueintrag.shiptoname.value="";
			document.neueintrag.shiptodepartment_1.value="";
			document.neueintrag.shiptodepartment_2.value="";
			document.neueintrag.shiptostreet.value="";
			document.neueintrag.shiptocountry.value="";
			document.neueintrag.shiptozipcode.value="";
			document.neueintrag.shiptocity.value="";
			document.neueintrag.shiptophone.value="";
			document.neueintrag.shiptofax.value="";
			document.neueintrag.shiptoemail.value="";
			document.neueintrag.shiptocontact.value="";
			document.neueintrag.shiptobland.options[0].selected=true;
		}
	}
	//-->
	</script>
<body onLoad="submenu('tab1'); document.neueintrag.name.focus();">

<p class="listtop"> Lieferant eingeben/editieren</p>

<!--span style="position:absolute; left:10px; top:47px; width:99%;"-->
<!-- Beginn Code ------------------------------------------->
<div style="position:absolute; top:3.5em; left:10px;  width:770px;">
	<ul id="maintab" class="shadetabs">
	<li id="subtab1" ><a href="#" onClick="submenu('tab1')">Rechnungsanschrift</a></li>
	<li id="subtab2" ><a href="#" onClick="submenu('tab2')">Lieferanschrift</a></li>
	<li id="subtab3" ><a href="#" onClick="submenu('tab3')">Sonstiges</a></li>
	<li>{Msg}
	</ul>
</div>
<form name="neueintrag" enctype='multipart/form-data' action="{action}" method="post">
<input type="hidden" name="id" value="{id}">
<input type="hidden" id="shipto_id" name="shipto_id" value="{shipto_id}">
<input type="hidden" name="vendornumber" value="{vendornumber}">
<input type="hidden" name="employee" value="{employee}">
<input type="hidden" name="grafik" value="{grafik}">
<span id="tab1" style="visibility:visible; position:absolute; text-align:left;width:90%; left:6px; top:6em; border:1px solid black;">
	<div class="zeile2">
		<span class="label">Anrede </span>
		<span class="feldxx"> <input type="text" name="greeting_" size="15" maxlength="75" value="{greeting_}" tabindex="1">
				<select name="greeting" tabindex="2">
					<option value="">
<!-- BEGIN anreden -->
					<option value="{ANREDE}" {ASEL}>{ANREDE}
<!-- END anreden -->
				</select>
		</span>
	</div>
	<div class="zeile2">
		<span class="label">Firmenname </span>
		<span class="feldxx"> <input type="text" name="name" size="35" maxlength="75" value="{name}" tabindex="3"></span>
	</div>
	<div class="zeile2">
		<span class="label">Abteilung 1</span>
		<span class="feldxx"><input type="text" name="department_1" size="35" maxlength="75" value="{department_1}" tabindex="4"></span>
	</div>
	<div class="zeile2">
		<span class="label">Abteilung 2</span>
		<span class="feldxx"><input type="text" name="department_2" size="35" maxlength="75" value="{department_2}" tabindex="5"></span>
	</div>
	<div class="zeile2">
		<span class="label">Strasse</span>
		<span class="feldxx"><input type="text" name="street" size="35" maxlength="75" value="{street}" tabindex="6"></span>
	</div>
	<div class="zeile2">
		<span class="label">Land / Plz</span>
		<span class="feldxx">
			<input type="text" name="country" size="2" maxlength="75" value="{country}" tabindex="7" onBlur="mkBuland('bland')">/
			<input type="text" name="zipcode" size="5" maxlength="10" value="{zipcode}" tabindex="8">
			<select name="bland" id="bland" tabindex="9" style="width:150px;">
				<option value=""></option>
<!-- BEGIN buland -->
				<option value="{BUVAL}" {BUSEL}>{BUTXT}</option>
<!-- END buland -->
			</select>
		</span>
	</div>
	<div class="zeile2">
		<span class="label">Ort</span>
		<span class="feldxx"><input type="text" name="city" size="35" maxlength="75" value="{city}" tabindex="10"></span>
	</div>
	<div class="zeile2">
		<span class="label">Telefon</span>
		<span class="feldxx"><input type="text" name="phone" size="35" maxlength="30" value="{phone}" tabindex="11"></span>
	</div>
	<div class="zeile2">
		<span class="label">Fax</span>
		<span class="feldxx"><input type="text" name="fax" size="35" maxlength="30" value="{fax}" tabindex="12"></span>
	</div>
	<div class="zeile2">
		<span class="label">eMail</span>
		<span class="feldxx"><input type="text" name="email" size="35" maxlength="125" value="{email}" tabindex="13"></span>
	</div>
	<div class="zeile2">
		<span class="label">Kontakt</span>
		<span class="feldxx"><input type="text" name="contact" size="35" maxlength="125" value="{contact}" tabindex="14"></span>
	</div>
	<div class="zeile2">
		Bemerkungen<br>
		<textarea name="notes" cols="70" rows="3" tabindex="15">{notes}</textarea><br />
	</div>
	<span style="position:absolute; left:35em; top:3em;text-align:left;">
		<div class="zeile2">
			<span class="labelxx">Logo</span>
			<span class="feldxx">
				<input type="file" name="Datei" size="20" maxlength="125" accept="Image/*" tabindex="16">
			</span><br><br>
			<span class="feldxx">
			{IMG}
			</span>
		</div>
	</span>

</span>
<!-- Ende tab1 -->
<span id="tab2" style="visibility:hidden;  position:absolute; text-align:left;width:90%; left:5px; top:6em; border:1px solid black;">
	<div class="zeile2">
		<span class="label"></span>
		<span class="feldxx"><select name="shiptoadress" style="width:190px;" tabindex="1" onChange="getShipadress();">
				<option value=""></option>
<!-- BEGIN shiptos -->
				<option value="{SHIPID}">{SHIPTO}</option>
<!-- END shiptos -->
		</select>
		</span>
	</div>
	<div class="zeile2">
		<span class="label">Firmenname</span>
		<span class="feldxx"><input type="text" id="shiptoname" name="shiptoname" size="35" maxlength="75" value="{shiptoname}" tabindex="2"></span>
	</div>
	<div class="zeile2">
		<span class="label">Abteilung 1</span>
		<span class="feldxx"><input type="text" id="shiptodepartment_1" name="shiptodepartment_1" size="35" maxlength="75" value="{shiptodepartment_1}" tabindex="3"></span>
	</div>
	<div class="zeile2">
		<span class="label">Abteilung 2</span>
		<span class="feldxx"><input type="text" id="shiptodepartment_2" name="shiptodepartment_2" size="35" maxlength="75" value="{shiptodepartment_2}" tabindex="4"></span
	</div>
	<div class="zeile2">
		<span class="label">Strasse</span>
		<span class="feldxx"><input type="text" id="shiptostreet" name="shiptostreet" size="35" maxlength="75" value="{shiptostreet}" tabindex="5"></span>
	</div>
	<div class="zeile2">
		<span class="label">Land / Plz</span>
		<span class="feldxx">
			<input type="text" id="shiptocountry" name="shiptocountry" size="2" value="{shiptocountry}" tabindex="6" onBlur="mkBuland('shiptobland'ch);">/
			<input type="text" id="shiptozipcode" name="shiptozipcode" size="5" maxlength="10" value="{shiptozipcode}" tabindex="7">
			<select id="shiptobland" name="shiptobland" tabindex="8" style="width:150px;">
				<option value=""></option>
<!-- BEGIN buland2 -->
				<option value="{SBUVAL}" {SBUSEL}>{SBUTXT}</option>
<!-- END buland2 -->
			</select>
		</span>
	</div>
	<div class="zeile2">
		<span class="label">Ort</span>
		<span class="feldxx"><input type="text" id="shiptocity" name="shiptocity" size="35" maxlength="75" value="{shiptocity}" tabindex="9"></span>
	</div>
	<div class="zeile2">
		<span class="label">Telefon</span>
		<span class="feldxx"><input type="text" id="shiptophone" name="shiptophone" size="35" maxlength="30" value="{shiptophone}" tabindex="10"></span>
	</div>
	<div class="zeile2">
		<span class="label">Fax</span>
		<span class="feldxx"><input type="text" id="shiptofax" name="shiptofax" size="35" maxlength="30" value="{shiptofax}" tabindex="11"></span>
	</div>
	<div class="zeile2">
		<span class="label">eMail</span>
		<span class="feldxx"><input type="text" id="shiptoemail" name="shiptoemail" size="35" maxlength="125" value="{shiptoemail}" tabindex="12"></span>
	</div>
	<div class="zeile2">
		<span class="label">Kontakt</span>
		<span class="feldxx"><input type="text" id="shiptocontact" name="shiptocontact" size="35" maxlength="75" value="{shiptocontact}" tabindex="13"></span>
	</div>
	<br><br>
	<br><br>
</span>
<!-- Ende tab2 -->
<span id="tab3" style="visibility:hidden;  position:absolute; text-align:left;width:90%; left:5px; top:6em; border:1px solid black; display:inline;">
	<div class="zeile2">
		<span class="label">Branche</span>
		<span class="feldxx"><input type="text" name="branche_" size="15" maxlength="25" value="{branche_}" tabindex="1">
				<select name="branche" tabindex="2" style="width:150px;">
					<option value="">
<!-- BEGIN branchen -->
					<option value="{BRANCHE}" {BSEL}>{BRANCHE}
<!-- END branchen -->
				</select>
		</span>
	</div>
	<div class="zeile2">
		<span class="label">Stichwort</span>
		<span class="feldxx"><input type="text" name="sw" size="35" value="{sw}" maxlength="50" tabindex="3"></span>
	</div>
	<div class="zeile2">
		<span class="label">Homepage</span>
		<span class="feldxx"><input type="text" name="homepage" size="35" maxlength="75" value="{homepage}" tabindex="4"></span>
	</div>
	<div class="zeile2">
		<span class="label">UStId</span>
		<span class="feldxx"><input type="text" name="ustid" size="35" maxlength="15" value="{ustid}" tabindex="5"></span>
	</div>
	<div class="zeile2">
		<span class="label">Steuernr.</span>
		<span class="feldxx"><input type="text" name="taxnumber" size="35" maxlength="35" value="{taxnumber}" tabindex="6"></span>
	</div>
	<div class="zeile2">
		<span class="label">Bank</span>
		<span class="feldxx"><input type="text" name="bank" size="35" maxlength="55" value="{bank}" tabindex="7"></span>
	</div>
	<div class="zeile2">
		<span class="label">Blz</span>
		<span class="feldxx"><input type="text" name="bank_code" size="35" maxlength="10" value="{bank_code}" tabindex="8"></span>
	</div>
	<div class="zeile2">
		<span class="label">Konto-Nr</span>
		<span class="feldxx"><input type="text" name="account_number" size="35" maxlength="15" value="{account_number}" tabindex="9"></span>
	</div>
	<div class="zeile2">
		<span class="label">Kundenr.</span>
			<input type="text" name="v_customer_id" size="35" maxlength="55" value="{v_customer_id}" tabindex="11">
		</span>
	</div>
	<div class="zeile2">
		<span class="label">Kundentyp</span>
		<span class="feldxx">
			<select name="business_id" tabindex="12">
<!-- BEGIN TypListe -->
				<option value="{Bid}" {Bsel}>{Btype}</option>
<!-- END TypListe -->
			</select>
		</span>
	</div>
	<div class="zeile2">
		<span class="label">Steuerzone</span>
		<span class="feldxx">
			<select name="taxzone_id" tabindex="13">
				<option value="0" {txid0}>Inland
				<option value="1" {txid1}>EU mit UStID
				<option value="2" {txid2}>EU ohne UStID
				<option value="3" {txid3}>Ausland
			</select> 
		</span>
	</div>

	<div class="zeile2">
		<span class="label">Berechtig.</span>
		<span class="feldxx">
			<select name="owener" tabindex="14">
<!-- BEGIN OwenerListe -->
				<option value="{grpid}" {Gsel}>{Gname}</option>
<!-- END OwenerListe -->
			</select> &nbsp; {init}
		</span>
	</div>
	<div class="zeile2">
<!-- BEGIN sonder -->
	<input type="checkbox" name="sonder[]" value="{sonder_id}" {sonder_sel} tabindex="15">{sonder_name} 
<!-- END sonder -->	
	</div>
</span>
<span id="tab4" style="position:absolute; text-align:left;width:48%; left:5px; top:39em;"> 			
			{Btn1} &nbsp;{Btn2} &nbsp; 
			<input type="submit" name="saveneu" value="sichern neu" tabindex="37"> &nbsp;
			<input type="submit" name="reset" value="clear" tabindex="38"> &nbsp;
			<input type="button" name="" value="VCard" onClick="vcard()" tabindex="39">
</span>
<!-- End Code ------------------------------------------->
</span>
</form>
</body>
</html>
			
