<script language="JavaScript" type="text/javascript">

    var tabs = Array('C','V');

    function sende() {
        var tab = tabs[$( '#tabs' ).tabs('option','active')-1];
        var felder = '';
        $( '#firma option:selected' ).each(function() {
            felder += tab +'.' + $(this).val() + ',';
        });
        $( '#shipto option:selected' ).each(function() {
            felder += 'S.' + $(this).val() + ',';
        });
        $( '#contacts option:selected' ).each(function() {
            felder += 'P.' + $(this).val() + ',';
        });
        $( '#felder' ).val(felder);
        alert(felder);
        dialog_report();
        $( 'suchbutton_{Q}' ).click();
    }
    function holeTabellen() {
        var tab = tabs[$( '#tabs' ).tabs('option','active')-1];
        $.ajax({
            type : 'GET',
            url  : 'jqhelp/getReportTables.php?tab='+tab,
            dataType: 'json',
            success: function(data){
                 $.each(data.tables, function(k , v) {
                      $.each( data.tables[k] , function( key, value ) {
                         $("<option/>").val(value).text(value).appendTo("#" + k);
                      });
                 })
            }
        });
    }

    function dialog_report() {
            if ( !first ) {
                holeTabellen();
                //first = true;
            }
            if ( $( '#dialog_report' ).dialog('isOpen') ) {
                $( '#dialog_report' ).dialog('close');
            } else {
                $( '#dialog_report' ).dialog('open');
            }
    }
    $(document).ready(function() {
        $("#dialog_report" ).dialog({
            autoOpen: false,
            modal: true,
            width: 800,
            position: [100,300]
        });
        $( "#geo_{Q}" ).button().click(function() {
            if ({GEODB}) {
                fuzzy=(document.erwsuche.fuzzy.checked==true)?1:0;
                plz=document.erwsuche.zipcode.value;
                ort=document.erwsuche.city.value;
                tel=document.erwsuche.phone.value;
                F1=open("surfgeodb.php?ao=and&plz="+plz+"&ort="+ort+"&tel="+tel+"&fuzzy="+fuzzy,"GEO","width=550, height=350, left=100, top=50, scrollbars=yes");
           }
           else alert(".:noGEOdb:.");
           return false;
        });

        $( "#report_{Q}" ).button().click(function(event) {
            event.preventDefault();
            dialog_report();
          });
          $( ".fett_{Q}" ).click(function() {
            if ( $(this).html() == '#' ) first = '~';
            else first = $(this).html();
            $.ajax({
                type: "POST",
                data: 'first=' + first + '&Q={Q}',
                url: "jqhelp/getCompanies1.php",
                success: function(res) {
                    $( "#dialog_keine, #dialog_viele, #dialog_no_sw" ).dialog( "close" );
                    if ( !res ) $( "#dialog_keine" ).dialog( "open" );
                    else {
                        $( "#suchfelder_{Q}" ).hide();
                        $( "#companyResults_{Q}").html(res);
                        $( "#companyResults_{Q}").show();
                    }
                }
            });
            return false;
        });
        $( "#suchbutton_{Q}" ).button().click(function() {
            $.ajax({
                type: "POST",
                data: $("#erwsuche_{Q}").serialize() + '&suche=suche',
                url: "jqhelp/getCompanies1.php",
                success: function(res) {
                    $( "#dialog_keine, #dialog_viele, #dialog_no_sw" ).dialog( "close" );
                    if ( !res ) $( "#dialog_keine" ).dialog( "open" );
                    else {
                        $( "#suchfelder_{Q}" ).hide();
                        $( "#companyResults_{Q}" ).html(res);
                        $( "#companyResults_{Q}" ).show();
                    }
                }
            });
            return false;
        });
        $( "#reset_{Q}" ).button().click(function() {
            $( "#dialog_keine, #dialog_viele, #dialog_no_sw" ).dialog( "close" );
            $( "#erwsuche_{Q}" ).find(':input').each(function() {
                switch(this.type) {
                    case 'text':
                        $(this).val('');
                    break;
                    case 'checkbox':
                    case 'radio':
                        this.checked = false
                }
            });
            $( "#andor{Q}, #shipto{Q}, #fuzzy{Q}, #pre{Q}, #obsolete{Q}" ).click();
            $( "#name{Q}" ).focus();
            return false;
        });

        $( "#name{Q}" ).focus();
    });
</script>
<script type='text/javascript' src='inc/help.js'></script>

<body>

<div id="dialog_report" title="Report">
    <form name="report" id="formreport" onSubmit='return false;'>
    <table width='300'><tr><th>Firma</th><th>Shipto</th><th>Kontakte</th></tr><tr>
    <td><select id='firma'    name='firma'    size='10' multiple width='90'></select></td>
    <td><select id='shipto'   name='shipto'   size='10' multiple width='90'></select></td>
    <td><select id='contacts' name='contacts' size='10' multiple width='90'></select></td>
    </tr></table>
    <button onClick='sende();'>ok</button>  <button onClick='dialog_report();'>.:close:.</button>
    </form>
</div>



<p class="ui-state-highlight ui-corner-all tools" style="margin-top: 20px; padding: 0.6em;">
<button class="fett_{Q}">A</button>
<button class="fett_{Q}">B</button>
<button class="fett_{Q}">C</button>
<button class="fett_{Q}">D</button>
<button class="fett_{Q}">E</button>
<button class="fett_{Q}">F</button>
<button class="fett_{Q}">G</button>
<button class="fett_{Q}">H</button>
<button class="fett_{Q}">I</button>
<button class="fett_{Q}">J</button>
<button class="fett_{Q}">K</button>
<button class="fett_{Q}">L</button>
<button class="fett_{Q}">M</button>
<button class="fett_{Q}">N</button>
<button class="fett_{Q}">O</button>
<button class="fett_{Q}">P</button>
<button class="fett_{Q}">Q</button>
<button class="fett_{Q}">R</button>
<button class="fett_{Q}">S</button>
<button class="fett_{Q}">T</button>
<button class="fett_{Q}">U</button>
<button class="fett_{Q}">V</button>
<button class="fett_{Q}">W</button>
<button class="fett_{Q}">X</button>
<button class="fett_{Q}">Y</button>
<button class="fett_{Q}">Z</button>
<button class="fett_{Q}">#</button>
</p>

<form name="erwsuche" id="erwsuche_{Q}" enctype='multipart/form-data' action="#" method="post">
<input type="hidden" id='felder' name="felder" value="">
<input type="hidden" id='Q' name="Q" value="{Q}">

<!-- Beginn Code ------------------------------------------>

    <div class="zeile">
        <span class="label">.:KdNr:.</span>
        <span class="leftfeld"><input type="text" name="customernumber" size="27" maxlength="15" value="{customernumber}" tabindex="1"></span>
        <span class="label">.:Contact:.</span>
        <span class="leftfeld"><input type="text" name="contact" size="27" maxlength="25" value="{contact}" tabindex="21"></span>
    </div>
    <div class="zeile">
        <span class="label">{FAART2}</span>
        <span class="leftfeld"><input type="text" name="name" id="name{Q}" size="27" maxlength="75" value="{name}" tabindex="1"></span>
        <span class="label">.:Industry:.</span>
        <span class="leftfeld"><input type="text" name="branche" size="27" maxlength="25" value="{branche}" tabindex="21"></span>
    </div>
    <div class="zeile">
        <span class="label">.:department:.</span>
        <span class="leftfeld"><input type="text" name="department_1" size="27" maxlength="75" value="{department_1}" tabindex="2"></span>
        <span class="label">.:Catchword:.</span>
        <span class="leftfeld"><input type="text" name="sw" size="27" maxlength="125" value="{sw}" tabindex="22"></span>
    </div>
    <div class="zeile">
        <span class="label">.:street:.</span>
        <span class="leftfeld"><input type="text" name="street" size="27" maxlength="75" value="{street}" tabindex="3"></span>
        <span class="label">.:Remarks:.</span>
        <span class="leftfeld"><input type="text" name="notes" size="27" maxlength="125" value="{notes}" tabindex="23"></span>
    </div>
    <div class="zeile">
        <span class="label">.:country:. / .:zipcode:.</span>
        <span class="leftfeld"><input type="text" name="country" size="2" maxlength="5" value="{country}" tabindex="4"> /
                    <input type="text" name="zipcode" size="7" maxlength="15" value="{zipcode}" tabindex="5"></span>
        <span class="label">.:bankname:.</span>
        <span class="leftfeld"><input type="text" name="bank" size="27" maxlength="50" value="{bank}" tabindex="24"></span>
    </div>
    <div class="zeile">
        <span class="label">.:city:.</span>
        <span class="leftfeld"><input type="text" name="city" size="27" maxlength="75" value="{city}" tabindex="6"></span>
        <span class="label">.:bankcode:.</span>
        <span class="leftfeld"><input type="text" name="bank_code" size="27" maxlength="25" value="{bank_code}" tabindex="26"></span>
    </div>
    <div class="zeile">
        <span class="label">.:phone:.</span>
        <span class="leftfeld"><input type="text" name="phone" size="27" maxlength="75" value="{phone}" tabindex="7"></span>
        <span class="label">.:account:.</span>
        <span class="leftfeld"><input type="text" name="account_number" size="27" maxlength="25" value="{account_number}" tabindex="27"></span>
    </div>
    <div class="zeile">
        <span class="label">.:fax:.</span>
        <span class="leftfeld"><input type="text" name="fax" size="27" maxlength="125" value="{fax}" tabindex="8"></span>
        <span class="label">UStID</span>
        <span class="leftfeld"><input type="text" name="ustid" size="27" maxlength="12" value="{ustid}" tabindex="28"></span>
    </div>
    <div class="zeile">
        <span class="label">.:email:.</span>
        <span class="leftfeld"><input type="text" name="email" size="27" maxlength="125" value="{email}" tabindex="9"></span>
        <span class="label">www</span>
        <span class="leftfeld"><input type="text" name="homepage" size="27" maxlength="125" value="{homepage}" tabindex="29"></span>
    </div>
    <div class="zeile">
        <span class="label">.:{Q}Business:.</span>
        <span class="leftfeld">
            <select name="business_id" tabindex="10">
<!-- BEGIN TypListe -->
                <option value="{BTid}" {BTsel}>{BTtext}</option>
<!-- END TypListe -->
            </select>
        </span>
        <span class="label">.:lang:.</span>
        <span class="leftfeld">    <select name="language_id" tabindex="30">
                <option value="">
<!-- BEGIN LAnguage -->
                <option value="{LAid}" {LAsel}>{LAtext}
<!-- END LAnguage -->
            </select>
        </span>
    </div>
    <div class="zeile">
        <span class="label">.:leadsource:.</span>
        <span class="leftfeld">
            <select name="lead" tabindex="11" style="width:110px;">
<!-- BEGIN LeadListe -->
                <option value="{LLid}" {LLsel}>{LLtext}</option>
<!-- END LeadListe -->
            </select>
            <input type="text" name="leadsrc" size="5" value="{leadsrc}" tabindex="12">
        </span>
        <span class="label">.:headcount:.</span>
        <span class="leftfeld"><input type="text" name="headcount" size="7" maxlength="7" value="{headcount}" tabindex="32"></span>
    </div>
    <div class="zeile">
        <span class="label">.:sales volume:.</span>
        <span class="leftfeld"><input type="text" name="umsatz" size="7" maxlength="25" value="{umsatz}" tabindex="32"> .:year:.
            <select name="year" tabindex="11" >
<!-- BEGIN YearListe -->
                <option value="{YLid}" {YLsel}>{YLtext}</option>
<!-- END YearListe -->
            </select></span>
    </div>
<!-- BEGIN cvarListe -->
    <div class="zeile">
        <span class="label">{varlable1}</span>
        <span class="leftfeld">{varfld1}</span>
        <span class="label">{varlable2}</span>
        <span class="leftfeld">{varfld2}</span>
    </div>
<!-- END cvarListe -->
    <div class="zeile">
                        <br>

            .:search:. <input type="radio" name="andor"  id="andor{Q}" value="and" checked tabindex="40">.:all:. <input type="radio" name="andor" value="or" tabindex="40">.:some:.<br>
            <input type="checkbox" name="shipto" id="shipto{Q}" value="1" checked tabindex="40">.:also in:. .:shipto:.<br>
            <input type="checkbox" name="fuzzy" id="fuzzy{Q}" value="%" checked tabindex="41">.:fuzzy search:. <input type="checkbox" name="pre" id="pre{Q}" value="1" {preon}>.:with prefix:.<br>
            <input type="checkbox" name="employee" value="{employee}" tabindex="42">.:only by own:.<br>
            .:obsolete:. <input type="radio" name="obsolete" value="t" >.:yes:. <input type="radio" name="obsolete" value="f" >.:no:.  <input type="radio" name="obsolete" id="obsolete{Q}" value="" checked >.:equal:.<br>
            <button id="suchbutton_{Q}" tabindex="43">.:search:.</button>&nbsp;
            <button id="reset_{Q}" tabindex="44">.:clear:.</button> &nbsp;
            <button id="report_{Q}"  tabindex="45">Report</button> &nbsp;
            <button id="geo_{Q}"  tabindex="46" {showGeo}>GeoDB</button> &nbsp;
            <a href="extrafelder.php?owner={Q}0"><img src="image/extra.png" alt="Extras" title="Extras" border="0" /></a>
            <br>
            {report}
    </div>
</form>
{TOOLS}
