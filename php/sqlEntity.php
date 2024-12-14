<?php
require_once __DIR__ . '/connect.php';
class SqlEntity extends Connect{
    public function mysqliSanitizeString($str): string{
        return mysqli_real_escape_string($this->conn,$str);
    }
    public function insertInto(String $table, Array $columns, Array $values, $returnId = False): bool|int{
        $placeholders = array_fill(0,count($values),'?'); //['?','?','?']

        $sql = "INSERT INTO $table (".implode(', ',$columns).") VALUES(".implode(', ',$placeholders).")";
        $stmt = $this->conn->prepare($sql);
        $types = ''; //wanna make these responsive
        foreach ($values as $value){
            $types .= match (gettype($value)) {
                'string' => 's',
                'integer' => 'i',
                'double' => 'd',
                default => 'b',
            };
        }
        //this splat operator is crazy wtf https://www.hashbangcode.com/article/splat-operator-php
        $stmt->bind_param($types,...$values);
        if( !$stmt->execute() )return False;
        if($returnId) return $this->conn->insert_id;
        return True;
    }
    public function searchQuery(string $query, array $params = [], bool $count = false) {
        $stmt = mysqli_prepare($this->conn, $query);
        if ($stmt === false) {
            return [False, "message"=>"Prepare failed: " . mysqli_error($this->conn)];
        }
    
        // Bind parameters dynamically if provided
        if (!empty($params)) {
            // Generate a string of types based on the parameters
            $types = str_repeat('s', count($params)); // Use 's' for string by default
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
    
        // Execute the query
        if (!mysqli_stmt_execute($stmt)) {
            return [False, "message"=>"Execution failed: " . mysqli_error($this->conn)];
        }
    
        $result = mysqli_stmt_get_result($stmt);
        if ($count) {
            $row = mysqli_fetch_row($result);
            return [$row[0] > 0, $row[0]];
        }
    
        return $result;
    }
    public function sqlResponseToArray($res){
        $rows = [];
        while($row = mysqli_fetch_array($res)){
            $rows[] = $row;
        }
        return $rows;
    }
}