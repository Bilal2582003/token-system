<?php
require_once __DIR__ . '/../config/env.php';

class DB {
    protected $conn;
    private static $instance = null;

    private function __construct() {
        try {
            $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // 🔹 INSERT
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($sql);

        foreach ($data as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }

        return $stmt->execute();
    }

    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }

    // 🔹 UPDATE with custom WHERE conditions
    public function update($table, $conditions = [], $data = []) {
        if (empty($conditions) || empty($data)) return false;

        // Build SET
        $set = implode(', ', array_map(fn($k) => "$k = :set_$k", array_keys($data)));

        // Build WHERE
        $where = implode(' AND ', array_map(fn($k) => "$k = :cond_$k", array_keys($conditions)));

        $sql = "UPDATE $table SET $set WHERE $where";
        $stmt = $this->conn->prepare($sql);

        foreach ($data as $key => $val) {
            $stmt->bindValue(":set_$key", $val);
        }
        foreach ($conditions as $key => $val) {
            $stmt->bindValue(":cond_$key", $val);
        }

        return $stmt->execute();
    }

    // 🔹 DELETE with flexible conditions
    public function delete($table, $conditions = []) {
        if (empty($conditions)) return false;

        $where = implode(' AND ', array_map(fn($k) => "$k = :$k", array_keys($conditions)));
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->conn->prepare($sql);

        foreach ($conditions as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }

        return $stmt->execute();
    }

    // 🔹 GET ALL with optional conditions, order, limit
    public function getAll($table, $conditions = [], $orderBy = '', $limit = '', $joins = [], $columns = '*') {
        $joinStr = '';
        foreach ($joins as $join) {
            $type = strtoupper($join['type'] ?? 'INNER');
            $joinStr .= " $type JOIN {$join['table']} ON {$join['on']}";
        }

        $whereClauses = [];
        $bindParams = [];

        foreach ($conditions as $col => $val) {
            $param = (strpos($col, '.') !== false) 
                ? str_replace('.', '_', $col) 
                : $col;

            if (is_null($val)) {
                $whereClauses[] = "$col IS NULL";
            } elseif (is_array($val) && isset($val['operator'], $val['value'])) {
                $operator = strtoupper(trim($val['operator']));
                $param = preg_replace('/[^a-zA-Z0-9_]/', '', $col);
                
                // Handle IN operator specially
                if ($operator === 'IN' && is_array($val['value'])) {
                    $placeholders = [];
                    $i = 0;
                    foreach ($val['value'] as $inVal) {
                        $inParam = $param . '_' . $i++;
                        $placeholders[] = ":$inParam";
                        $bindParams[$inParam] = $inVal;
                    }
                    $whereClauses[] = "$col IN (" . implode(', ', $placeholders) . ")";
                } else {
                    $whereClauses[] = "$col $operator :$param";
                    $bindParams[$param] = $val['value'];
                }
            } else {
                $whereClauses[] = "$col = :$param";
                $bindParams[$param] = $val;
            }
        }

        $where = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        $order = $orderBy ? "ORDER BY $orderBy" : '';
        $limitStr = $limit ? "LIMIT $limit" : '';

        $sql = "SELECT $columns FROM $table $joinStr $where $order $limitStr";
        $stmt = $this->conn->prepare($sql);

        // Bind all parameters
        foreach ($bindParams as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 GET ONE
    public function getOne($table, $conditions = [], $joins = [], $columns = '*', $order = "") {
        $results = $this->getAll($table, $conditions, $order, 1, $joins, $columns);
        return $results[0] ?? null;
    }

    // 🔹 Raw Query (optional utility)
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMainConn(){
        return $this->conn;
    }
}