
<html>
<head>
<meta charset='utf-8' />
<?php
    require_once("inc/stdLib.php");
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    echo $menu['stylesheets'];
    echo $menu['javascripts'];
    echo $head['FULLCALCSS'];
    echo $head['BOXCSS'];
    echo $head['COLORPICKERCSS'];
    echo $head['JQUERY'];
    echo $head['JQUERYUI'];
    echo $head['THEME'];
    echo $head['FULLCALJS'];
    echo $head['JQBOX'];
    echo $head['COLORPICKERJS'];
    echo $head['TRANSLATION'];
    //print_r( $_SESSION );

?>
<script language="javascript" type="text/javascript" src="translation/all.lng"></script>
<script>
    $( document ).ready( function(){
        var language = "<?php echo $_SESSION['countrycode'] ? $_SESSION['countrycode'] : 'en';?>";
        $( ".lang" ).each( function(){
            var key = $( this ).attr( "data-lang" );
            $( this ).text( typeof( langData[language][key] ) != 'undefined' ? langData[language][key] : 'LNG ERR'  );
        });
        $( "#save" ).button().click( function(){
            var dataArr  = $("#myform").serializeArray()
            var newColor = dataArr.pop();
            var newCat   = dataArr.pop();
            if( newCat.value ){
                $.ajax({
                    url: 'jqhelp/event_category.php',
                    data: { task:  'newCategory', newCat: newCat.value, newColor: newColor.value },
                    type: "POST",
                });
                var newMax = parseInt( $( "#tmp" ).data( "max" ) ) + 1;
                $( "#tmp" ).data( "max", newMax );
                $( "input[name='new_color']" ).attr( "name", "color_" + newMax );
                $( "input[name='new_cat']" ).attr( "name", "cat_" + newMax );
                $( "input:last[name='del']" ).attr( "value",  newMax );
                $( ".new" ).toggleClass( "new remove_" + newMax );
                $( "#sortable" ).append("<li class='ui-state-default new'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><input type='text' class='ui-widget-content ui-corner-all left lang' autocomplete='off' name='new_cat'>"
                                      + "<input type='text' class='ui-widget-content ui-corner-all middle lang' autocomplete='off' name='new_color' maxlength='7'> </li>");
                $( ".remove_" + newMax ).append( "<input type='checkbox' class='right' value='" + newMax + "' name='del' data-lang='DELETE' title='" + langData[language]['DELETE'] + "' tabindex='-1'>");
                focusOut();
            }
            //console.log( JSON.stringify( dataArr ) );
            $.each( dataArr, function(i, v) {
                if( v.name == "del" ){
                    $.ajax({
                        url: 'jqhelp/event_category.php',
                        data: { task:  'deleteCategory', delCat: v.value },
                        type: "POST",
                        success: function(){
                            $(".remove_" + v.value ).remove();
                       }
                    });

                }
            });
            $.ajax({
                url: 'jqhelp/event_category.php',
                data: { task:  'updateCategories', updateData: dataArr },
                type: "POST",
            });
        });
        $( "#calendar" ).button().click( function(){
            window.location.href = "calendar.phtml";
        });
        var focusOut = function(){
            $( ".left, .middle" ).focusout( function(){
                $( "#save" ).click();
            })
        };
        focusOut(); //ToDo: vereinfachen??
        $( "#sortable" ).sortable({
            update: function(){
                $( "#save" ).click();
            }
        });


        $.ajax({
            url: 'jqhelp/event_category.php',
            data: { task:  'getCategories'  },
            type: "POST",
            success: function( json ) {
                var obj = $.parseJSON( json.trim() ) ;
                var max = 0;
                console.log(json);
                $.each( obj, function( i, val ){
                    if( val.id > max ) max = val.id;
                    $( "#sortable" ).prepend("<li class='ui-state-default remove_" + val.id + "'><span class='ui-icon ui-icon-arrowthick-2-n-s'></span><input type='text' class='ui-widget-content ui-corner-all left lang' autocomplete='off' data-lang='DOUBLE_KLICKx' value='" + val.label + "' name='cat_" + val.id + "'>"
                                         + "<input type='text' class='ui-widget-content ui-corner-all middle lang' autocomplete='off' value='" + val.color + "' name='color_" + val.id + "' maxlength='7'><input type='checkbox' class='right' name='del' value='" + val.id + "' title='" + langData[language]['DELETE'] + "' tabindex='-1'></li>");
                });
                $(".right").tooltip({ position: { my: "center bottom-10", at: "center top" } } );
                $( "#tmp" ).data( "max", max );
            },
            error: function () {
                alert('Ajax Error');
            }
        });
    });
</script>
<style>
    #sortable, #head { list-style-type: none; margin: 0; padding: 0;padding-left: 2.5em; width: 400px; }
    #sortable li, #head li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
    #sortable li span { position: absolute; margin-left: -1.3em; }
    .left {  position:absolute;   width: 180px;}
    .middle {  position:absolute;  left:280px; width: 90px; color: 666;}
    .right {  position:absolute;  left:350px; width: 90px; color: 666;}
    #buttons { padding-left: 2.5em; padding-top: 1em;    }
</style>
</head>
<body>
<?php
    echo $menu['pre_content'];
    echo $menu['start_content'];
?>
<div class="ui-widget-content" >
    <div id="tmp"></div>
    <div>
        <p class="tools ui-state-highlight ui-corner-all lang " style="margin-top: 20px; padding: 0.6em;" data-lang='HEADLINE'></p>
    </div>
    <ul id="head">
        <li class="ui-state-active"><span class="left lang" data-lang="CATEGORY"></span><span class="middle lang" data-lang="COLOR"></span></li>
    </ul>

    <form id="myform">
        <ul id="sortable">
            <li class="ui-state-default new"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
                <input type="text" class="ui-widget-content ui-corner-all left lang"  autocomplete="off" name="new_cat">
                <input type="text" class="ui-widget-content ui-corner-all middle lang" autocomplete="off" name="new_color" maxlength="7">

            </li>
        </ul>
    </form>
    <div id="buttons">
        <button id="save" class="lang" data-lang="SAVE"></button>
        <button id="calendar" class="lang" data-lang="CALENDAR"></button>
   </div>
</div>
<?php echo $menu['end_content']; ?>
<?php echo $head['TOOLS']; ?>
</body>
</html>
