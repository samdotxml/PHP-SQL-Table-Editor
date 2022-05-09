<?php
include "TableEditor.php";

//Debug - Uncomment if you want to debug
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

//Define your Tables in this Array
$TABLES = array("Table1", "Table2");
$table_editor = new TableEditor($TABLES, $mysqli);

//Handle input validation with set array
/*
$validates = array(
    "Table" => array(
        "prename" => "REGEX",
        "name" => "REGEX",
        "street" => "REGEX",
    )
);
$table_editor->setRegexAttributes($validates);
*/

//Handle input validation attribute per attribute
/*
$table_editor->addRegexToAttribute("Table", "prename", "REGEX");
*/

/* All edits are sent via POST */
if(isset($_POST["tableeditordelete"])){
    $table = $_POST['tableeditordelete'];
    $table_editor->validateInput($_POST, $table);

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
    $table_editor->validateInput($_POST, $table);

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
    $table_editor->validateInput($_POST, $table);

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