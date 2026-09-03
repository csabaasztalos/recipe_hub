<?php

class DBHandler
{
    private mysqli $conn;

    /**
     * @throws DBException
     */
    public function __construct(string $host, string $user, string $pass, string $db_name, string $port) {
        $driver = new mysqli_driver();
        $driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

        try {
            $this->conn = new mysqli($host, $user, $pass, $db_name, $port);
        } catch (mysqli_sql_exception $e) {
            throw new DBException("Could not connect to the database: " . $e->getMessage());
        }
    }

    /**
     *  @throws DBException
     */
    public function Disconnect(): void
    {
        try {
            $this->conn->close();
        } catch (mysqli_sql_exception $e) {
            throw new DBException("Could not close the database: " . $e->getMessage());
        }
    }


    /**
     *  @throws DBException
     */
    public function RunQuery(string $sqlCommand, array $params = []): mysqli_result|bool
    {
        try {
            $stmt = $this->conn->prepare($sqlCommand);

            if (count($params) > 0) {
                $types = '';
                $values = [];

                foreach ($params as $param) {
                    $types .= $param->getType()->value;
                    $values[] = $param->getParam();
                }
                $stmt->bind_param($types, ...$values);
            }

            $stmt->execute();

            if (stripos($sqlCommand, 'SELECT') === 0) {
                $result = $stmt->get_result();
                $stmt->close();
                return $result;

            } else {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                return $affected_rows > 0;
            }
        } catch (mysqli_sql_exception $e) {
            throw new DBException("Could not run query: " . $e->getMessage());
        }

    }

    public function GetLastInsertId(): int
    {
        return $this->conn->insert_id;
    }

}
