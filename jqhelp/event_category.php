<?php
    require_once("../inc/stdLib.php"); 
    require_once("../inc/crmLib.php");  
    $task     = array_shift( $_POST );
    $newCat   = $_POST['newCat'];
    $newColor = $_POST['newColor'];
    $delCat   = $_POST['delCat'];   
    
    switch( $task ){
        case "newCategory":
            $sql="INSERT INTO event_category ( label, color, cat_order ) VALUES ( '$newCat', '$newColor', ( SELECT max( cat_order ) + 1 AS cat_order FROM event_category) )";
            $rc=$_SESSION['db']->query($sql); 
        break;
        case "getCategories":
            if ( $_SESSION['db']->JVer ) {
                $sql = "SELECT json_agg( json_category ) FROM ( SELECT id, label, TRIM( color ) AS color FROM event_category ORDER BY cat_order DESC ) AS json_category ;";
                $rs = $_SESSION['db']->getOne( $sql );
                echo $rs['json_agg'];   
            } else {
                $sql = "SELECT id , label, TRIM( color ) AS color FROM event_category ORDER BY cat_order DESC";
                $rs = $_SESSION['db']->getJson( $sql );
                echo $rs;  
            }
        break; 
        case "updateCategories":
            if ( $_POST['updateData']) { 
                $sql = "WITH new_data (id, label, color, cat_order) AS ( VALUES ";
                foreach( $_POST['updateData'] as $key => $value ){
                    $order = ( int ) ( $key / 2 );
                    if( $key % 2 ) $sql .= ", '".$value['value']."', ".$order." )";//\r\n
                    else $sql .= ($key ? ',' :'' )."( ".substr($value['name'], 4).", '".$value['value']."'";        
                }
                $sql .= " ) UPDATE event_category SET label = d.label, color = d.color, cat_order = d.cat_order FROM new_data d WHERE d.id = event_category.id";
                $rs = $_SESSION['db']->getOne( $sql );
            }      
        break;
        case "deleteCategory":
            $sql="DELETE FROM event_category WHERE id = $delCat";
            $rc=$_SESSION['db']->query($sql); 
        break;
     }
 ?>
