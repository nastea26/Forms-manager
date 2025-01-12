<?php
require_once 'connect.php';

class SqlEntity extends Connect
{
    public function mysqliSanitizeString($str): string
    {
        return mysqli_real_escape_string($this->conn, $str);
    }
    public function insertInto(String $table, array $columns, array $values, $returnId = False): bool|int
    {
        $placeholders = array_fill(0, count($values), '?'); //['?','?','?']

        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES(" . implode(', ', $placeholders) . ")";
        $stmt = $this->conn->prepare($sql);
        $types = ''; //wanna make these responsive
        foreach ($values as $value) {
            $types .= match (gettype($value)) {
                'string' => 's',
                'integer' => 'i',
                'double' => 'd',
                'boolean' => 'i',
                default => 'b',
            };
        }
        //this splat operator is crazy wtf https://www.hashbangcode.com/article/splat-operator-php
        $stmt->bind_param($types, ...$values);
        if (!$stmt->execute()) return False;
        if ($returnId) return $this->conn->insert_id;
        return True;
    }

    public function searchQuery(string $query, array $params = [], bool $count = false): mixed
    {
        $stmt = mysqli_prepare($this->conn, $query);
        if ($stmt === false) {
            return [false, "message" => "Prepare failed: " . mysqli_error($this->conn)];
        }

        // set datatpyes responsively (just like insert into)
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                $types .= match (gettype($param)) {
                    'string' => 's',
                    'integer' => 'i',
                    'double' => 'd',
                    default => 'b',
                };
            }

            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        if (!mysqli_stmt_execute($stmt)) {
            return [false, "message" => "Execution failed: " . mysqli_error($this->conn)];
        }

        $result = mysqli_stmt_get_result($stmt);

        // If $count is true, return the number of rows
        if ($count) {
            $row = mysqli_fetch_row($result);
            return [$row[0] > 0, $row[0]];
        }

        return $result ?: true;
    }

    public function sqlResponseToArray($res)
    {
        $rows = [];
        while ($row = mysqli_fetch_array($res)) {
            $rows[] = $row;
        }
        return $rows;
    }
}
