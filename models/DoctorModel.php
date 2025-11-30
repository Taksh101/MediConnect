<?php
class DoctorModel {
    private $db;

    public function __construct(mysqli $mysqli) {
        $this->db = $mysqli;
    }

    public function getByEmail(string $email): ?array {
        $sql = "SELECT * FROM doctors WHERE Email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) return null;
        return $res->fetch_assoc();
    }
}
