<?php
include "connection.php";
include "TableEditor.php";

//Debug - Uncomment if you want to debug
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

$TABLES = array("Personen", "Treffen", "Infektionen");
$table_editor = new TableEditor($TABLES, $mysqli);

/* All edits are sent via POST */
if(isset($_POST["tableeditordelete"])){
    $table = $_POST['tableeditordelete'];
    if(in_array($table, $table_editor->tables)){
        if(!$table_editor->deleteRow($table, $_POST)){
            echo "Error while updating table";
            exit();
        }
        else{
            echo "Updated record!";
        }
    }
}

if(isset($_POST['tableeditorupdate'])){
    $table = $_POST['tableeditorupdate'];
    if(in_array($table, $table_editor->tables)){
        if(!$table_editor->updateRow($table, $_POST)){
            echo "Error while updating table";
            exit();
        }
        else{
            echo "Updated record!";
        }
    }
}

if(isset($_POST["tableeditoradd"])){
    $table = $_POST['tableeditoradd'];
    if(in_array($table, $table_editor->tables)){
        if(!$table_editor->addRow($table, $_POST)){
            echo "Error while updating table";
            exit();
        }
        else{
            echo "Updated record!";
        }
    }
}
/* All edits are sent via POST */

$table_editor->printTables();
?>