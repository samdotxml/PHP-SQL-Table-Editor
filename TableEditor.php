<?php
class TableEditor {
    /**
     * Class for the Tableditor
     * @link       https://github.com/samdotxml/PHP-SQL-Table-Editor
    */
    public array $tables;

    private array $attributeRegex;
    private array $table_primary_keys;
    private mysqli $mysqli;

    function __construct(array $tables, mysqli $mysqli){
        $this->tables = $tables;
        $this->mysqli = $mysqli;

        if(empty($this->tables)){
            throw new Exception('Tables array is empty');
        }
        
        if($this->vaildateDateConnection() === False){
            throw new Exception('MySQLI Connection Failed.');
        }
        
        $this->getPrimaryKeys();
    }

    /**
    * Set a regular expression to a specific attribute in a table. These get checked when doing any action on the table via editor
    *
    * @param table   $table the table name in your database
    * @param attribute   $attribute the attribute name in your database
    * @param regex   $regex the regular expression
    * @author samdotxml <samdotxml@420blaze.it>
    * @return Boolean
    */
    public function addRegexToAttribute(string $table, string $attribute, $regex){
        if(array_key_exists($table, $this->$attributeRegex)){
            $this->$attributeRegex[$table] += array($attribute => $regex);
            return True;
        }
        else{
            $this->$attributeRegex[$table] = array($attribute => $regex);
            return True;
        }
        return False;
    }

    /**
    * $arr parameter gets set to the $attributeRegex field in the class
    *
    * @param arr   $arr an array
    * @author samdotxml <samdotxml@420blaze.it>
    * @return Boolean
    */
    public function setRegexAttributes(array $arr){
        $this->attributeRegex = $arr;
        return True;
    }

    /**
    * Echo's the table with the corresponding data. All rows are a form, with this you are able to do edits
    *
    * @author samdotxml <samdotxml@420blaze.it>
    */
    public function printTables(){
        foreach($this->tables as $t){
            echo "<h1>$t</h1>";
            echo "<table border='3'>";
            echo "<tr>";

            //Fetch Table Column Names
            $r1 = $this->mysqli->query("SHOW COLUMNS FROM $t");
            while($row = $r1->fetch_assoc()){
                $columns[] = $row['Field'];
            }

            //Echo Table Headers
            foreach($columns as $column){
                echo "<th>".strtoupper($column)."</th>";
            }
            echo "</tr>"; //Close Table-Header Row

            //Fetch * From Table
            //While lets us go through each row sequentially
            $rowIndex = 1;
            $r2 = $this->mysqli->query("SELECT * FROM $t");
            while($row = $r2->fetch_array(MYSQLI_NUM)){
                echo '<form action="" method="POST">'; //Create form for each row
                echo "<tr>";
                
                for($i = 0; $i < count($row); $i++){
                    $value = $row[$i];
                    $columnName = $columns[$i];

                    if($this->isPrimaryKey($t, $columnName) && $this->isAutoIncrement($t, $columnName)){
                        echo "<td>"."<input size='1' type='text' name='$columnName' value='$value' readonly>"."</td>";
                    }
                    else{
                        echo "<td>"."<input type='text' name='$columnName' value='$value' id='$i'>"."</td>";
                    }
                }
                echo "<td style='border:none;'><button id='$rowIndex' onclick=\"return confirm('Are you sure you want to delete this entry from the database?');\" style='vertical-align:bottom;' name='tableeditordelete' type='submit' value='$t'>Delete Row</button></td>";
                echo "<td style='border:none;'><button id='$rowIndex' style='vertical-align:bottom;' name='tableeditorupdate' type='submit' value='$t'>Submit Changes</button></td>";
                echo "</form></tr>";
                $rowIndex += 1;
            }
            
            //Add new field form
            echo '<form action="" method="POST">';
            echo "<tr>";
            for($x = 0; $x < count($columns); $x++){
                $columnName = $columns[$x];
                if($this->isPrimaryKey($t, $columnName) && $this->isAutoIncrement($t, $columnName)){
                    echo "<td>"."<input size='1' type='text' name='$columnName' value='' readonly>"."</td>";
                }
                else{
                    echo "<td>"."<input type='text' name='$columnName' value=''>"."</td>";
                }
            }
            echo "<td style='border:none;'><button style='vertical-align:bottom;' name='tableeditoradd' type='submit' value='$t'>Add Record</button></td>";
            echo "</form></tr>";
            
            
            //Colums array wird gecleared damit die header wieder sauber pro table gesetzt werden
            unset($columns);
            echo "</table>"; //Table wird geschlossen
            echo "<br><br>";
        }
    }

    #MySQL Methods
    public function deleteRow($table, $arr){
        $primaryKeyName = $this->table_primary_keys[$table][0];
        $primaryKeyValue = $arr[$primaryKeyName];
        unset($arr[$primaryKeyName]);

        $sql = 'DELETE FROM ' . $this->mysqli->real_escape_string($table) . ' WHERE '. $this->mysqli->real_escape_string($primaryKeyName) . '=' . $this->mysqli->real_escape_string($primaryKeyValue);
        if($this->mysqli->query($sql) === TRUE){
            return True;
        }
        else{
            return False;
        }
    }

    public function updateRow($table, $arr){
        $primaryKeyName = $this->table_primary_keys[$table][0];
        $primaryKeyValue = $arr[$primaryKeyName];
        unset($arr[$primaryKeyName]);
        unset($arr['tableeditorupdate']);

        $sql_values = '';
        $sep = '';
        foreach ($arr as $key => $val) {
            $sql_values .= $sep . $key . '= "' . $this->mysqli->real_escape_string($val) . '"';
            $sep = ',';
        }

        $sql = 'UPDATE '. $this->mysqli->real_escape_string($table) .' SET ' . $sql_values .' WHERE ' . $this->mysqli->real_escape_string($primaryKeyName) . '=' . $this->mysqli->real_escape_string($primaryKeyValue);
        if($this->mysqli->query($sql) === TRUE){
            return True;
        }
        else{
            return False;
        }
    }

    public function addRow($table, $arr){
        $primaryKeyName = $this->table_primary_keys[$table][0];
        $primaryKeyValue = $arr[$primaryKeyName];
        unset($arr[$primaryKeyName]);
        unset($arr['tableeditoradd']);

        $sql_values = '';
        $sql_attributes = '';
        $sep = '';
        foreach ($arr as $key => $val) {
            $sql_attributes .= $sep . $key;
            $sql_values .= $sep . '"' . $this->mysqli->real_escape_string($val) . '"';
    
            $sep = ',';
        }

        $sql = 'INSERT INTO '.$this->mysqli->real_escape_string($table).' ('.$this->mysqli->real_escape_string($sql_attributes).') VALUES ('.$sql_values.')';
        echo "<br>";
        echo $sql;
        echo "<br>";
        if($this->mysqli->query($sql) === TRUE){
            return True;
        }
        else{
            return False;
        }
    }

    private function vaildateDateConnection(){
        if ($this->mysqli->connect_errno) {
            return False;
        }
    }

    private function getPrimaryKeys(){
        try{
            foreach($this->tables as $t){
                $sql = "SHOW KEYS FROM $t WHERE Key_name = 'PRIMARY'";
                $result = $this->mysqli->query($sql);
                if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $primaryKeyName = $row["Column_name"];
                        if($this->checkPrimaryKeyAutoIncrement($t, $primaryKeyName)){
                            //Primary key is auto increment
                            $this->table_primary_keys[$t] = array($primaryKeyName, True);
                        }
                        else{
                            //Primary key is not auto increment
                            $this->table_primary_keys[$t] = array($primaryKeyName, False);
                        }
                    }
                }
                else{
                    throw new Exception('No Primarykey. You must have 1 Primarykey per Table');
                }
            }
        }
        catch(Exception $e){
            echo $e;
        }
    }

    private function checkPrimaryKeyAutoIncrement($table, $primaryKeyName){
        $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$primaryKeyName' AND EXTRA like '%auto_increment%'";
        $result = $this->mysqli->query($sql);
        if($result->num_rows > 0){
            return True;
        }
        else{
            return False;
        }
    }

    private function isPrimaryKey($table, $name){
        if($this->table_primary_keys[$table][0] === $name){
            return True;
        }
        else{
            return False;
        }
    }

    private function isAutoIncrement($table, $name){
        if($this->table_primary_keys[$table][1] === True){
            return True;
        }
        else{
            return False;
        }
    }
}
?>