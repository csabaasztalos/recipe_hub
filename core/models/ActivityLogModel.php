<?php
class ActivityLogModel {
    private DBHandler $db;

    public function __construct() {
        $this->db = Container::Get('db');
    }

    public function ActivityLog(string $tableName, string|int $record_id, string $action, int $changedBy, string $description = ''): bool|mysqli_result
    {
        return $this->db->RunQuery("INSERT INTO `activity_log` (`table_name`, `record_id`, `action`, `changed_by`, `description`) VALUES (?, ?, ?, ?, ?);",
            [
                new DBParam(DBTypes::STRING, $tableName),
                new DBParam(DBTypes::STRING, (string) $record_id),
                new DBParam(DBTypes::STRING, $action),
                new DBParam(DBTypes::INT, $changedBy),
                new DBParam(DBTypes::STRING, $description)
            ]);
    }

    public function GetActivitiesWithUsername(): bool|mysqli_result
    {
    return $this->db->RunQuery(
    "SELECT activity_log.*, users.username
     FROM `activity_log` JOIN users ON activity_log.changed_by = users.id
     ORDER BY activity_log.id DESC
     LIMIT 30");
    }
}
