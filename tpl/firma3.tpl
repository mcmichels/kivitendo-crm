<html>
	<head><title></title>
{STYLESHEETS}
{CRMCSS}
{JQUERY}
{JQUERYUI}
{THEME}
{JQTABLE}
{JAVASCRIPTS}
	<script language="JavaScript">
	<!--
	function showM (month) {
		uri="firma3.php?Q={Q}&jahr={JAHR}&monat=" + month + "&fid=" + {FID};
		location.href=uri;
	}
    $(function(){
         $('button')
          .button()
          .click( function(event) { event.preventDefault();  document.location.href=this.getAttribute('name'); });
    });
	//-->
	</script>
    <script>
    $(document).ready(
        function(){
            $("#ums").tablesorter({widthFixed: true, widgets: ['zebra'], headers: { 
                0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }, 3: { sorter: false }, 4: { sorter: false } } 
            });
        })
	</script>
<body>
{PRE_CONTENT}
{START_CONTENT}
<p class="listtop">.:detailview:. {FAART}</p>
<div id="menubox2">
    <button name="{Link1}">.:Custombase:.</button>
    <button name="{Link2}">.:Contacts:.</button>
    <button name="{Link3}">.:Sales:.</button>
    <button name="{Link4}">.:Documents:.</button>
</div>
<span id='contentbox' >
<!-- Hier beginnt die Karte  ------------------------------------------->
<div style="position:absolute; left:0px; top:1.5em; width:35em; border:1px solid black">
	<span class="fett">{Name} &nbsp; {kdnr}</span><br />
	{Plz} {Ort}
</div>
<span style="position:absolute; left:38em; top:2.1em;">[<a href="opportunity.php?Q={Q}&fid={FID}">.:Opportunitys:.</a>]</span>
<div style="position:absolute; left:1em; top:5em; width:99%;text-align:center;" class="normal">
	<div style="float:left; width:23em; text-align:left; " >
		<table id="ums" class="tablesorter" style="width:100%;">
			<thead><tr>
				<th >.:Month:.</th>
				<th></th><th>.:Sales:.</th>
				<th>.:Quotation:.</th><th></th>
			</tr></thead><tbody>
<!-- BEGIN Liste -->
			<tr onClick="showM('{Month}');">
				<td >{Month}</td>
				<td >{Rcount}</td><td >{RSumme}</td>
				<td >{ASumme}</td><td >&nbsp;{Curr}</td>
			</tr>
<!-- END Liste -->
		</tbody></table>
	</div>
	<div style="float:left; text-align:right; width:520px;" class="fett">
	<center>.:Netto sales over 12 Month:. 
	[<a href='firma3.php?Q={Q}&fid={FID}&jahr={JAHRZ}'>.:earlier:.</a>] [<a href='firma3.php?Q={Q}&fid={FID}&jahr={JAHRV}'>{JAHRVTXT}</a>]</center>
		<img src="{IMG}" width="500" height="280" title="Netto sales over 12 Month"><br /><br />
	</div>
</div>
<!-- Hier endet die Karte ------------------------------------------->
</span>
{END_CONTENT}
</body>
</html>
