<?php
// models/SpecialityModel.php
class SpecialityModel {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function all(): array {
        $sql = "SELECT Speciality_Id, Speciality_Name, Description, Consultation_Duration, Consultation_Fee
                FROM Specialities
                ORDER BY Speciality_Name ASC";
        $res = $this->db->query($sql);
        $rows = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) $rows[] = $r;
            $res->free();
        }
        return $rows;
    }

    public function find(int $id): ?array {
        $sql = "SELECT Speciality_Id, Speciality_Name, Description, Consultation_Duration, Consultation_Fee
                FROM Specialities WHERE Speciality_Id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    public function create(string $name, ?string $desc, int $duration, float $fee): bool {
        // Correct types: name (s), description (s), duration (i), fee (d)
        $sql = "INSERT INTO Specialities (Speciality_Name, Description, Consultation_Duration, Consultation_Fee)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssid', $name, $desc, $duration, $fee);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update(int $id, string $name, ?string $desc, int $duration, float $fee): bool {
        // Correct types: name (s), description (s), duration (i), fee (d), id (i)
        $sql = "UPDATE Specialities
                SET Speciality_Name = ?, Description = ?, Consultation_Duration = ?, Consultation_Fee = ?
                WHERE Speciality_Id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssidi', $name, $desc, $duration, $fee, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function delete(int $id): bool {
        $sql = "DELETE FROM Specialities WHERE Speciality_Id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function existsByName(string $name, ?int $excludeId = null): bool {
        if ($excludeId) {
            $sql = "SELECT 1 FROM Specialities WHERE Speciality_Name = ? AND Speciality_Id <> ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('si', $name, $excludeId);
        } else {
            $sql = "SELECT 1 FROM Specialities WHERE Speciality_Name = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('s', $name);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = ($res && $res->num_rows > 0);
        $stmt->close();
        return $exists;
    }
    // inside SpecialityModel class

public function countAll(): int {
    $sql = "SELECT COUNT(*) AS cnt FROM Specialities";
    $res = $this->db->query($sql);
    return $res ? (int)$res->fetch_object()->cnt : 0;
}

public function paginate(int $limit, int $offset): array {
    $sql = "SELECT Speciality_Id, Speciality_Name, Description, Consultation_Duration, Consultation_Fee
            FROM Specialities
            ORDER BY Speciality_Name ASC
            LIMIT ? OFFSET ?";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $stmt->close();
    return $rows;
}

}
