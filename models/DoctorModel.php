<?php
class DoctorModel {
    private $db;

    public function __construct(mysqli $mysqli) {
        $this->db = $mysqli;
    }
    public function countAll() {
    $sql = "SELECT COUNT(*) as c FROM Doctors";
    $res = $this->db->query($sql);
    $row = $res->fetch_assoc();
    return (int)($row['c'] ?? 0);
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
     public function paginate($limit, $offset) {
        $sql = "SELECT d.*, s.Speciality_Name
                FROM Doctors d
                LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                ORDER BY d.Created_At DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function create($name, $email, $password, $phone, $speciality, $qualification, $experience, $bio, $status) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO Doctors (Name, Email, Password, Phone, Speciality_Id, Qualification, Experience_Years, Bio, Status, Created_At)
            VALUES (?,?,?,?,?,?,?,?,?, NOW())";
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) return false;
    $stmt->bind_param('ssssissss', $name, $email, $hash, $phone, $speciality, $qualification, $experience, $bio, $status);
    return $stmt->execute();
}


    public function existsByEmail($email, $exceptId = 0) {
        if ($exceptId) {
            $sql = "SELECT COUNT(*) as c FROM Doctors WHERE Email = ? AND Doctor_Id <> ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $email, $exceptId);
        } else {
            $sql = "SELECT COUNT(*) as c FROM Doctors WHERE Email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $email);
        }
        $stmt->execute();
        $r = $stmt->get_result();
        $row = $r->fetch_assoc();
        return ((int)$row['c']) > 0;
    }

    public function find($id) {
        $sql = "SELECT d.*, s.Speciality_Name FROM Doctors d LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id WHERE d.Doctor_Id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result();
        return $r->fetch_assoc();
    }
    
    public function update($id, $name, $email, $password, $phone, $speciality, $qualification, $experience, $bio, $status) {
    // Build dynamic update (password optional)
    $fields = [];
    $types = '';
    $values = [];

    $fields[] = 'Name=?'; $types .= 's'; $values[] = $name;
    $fields[] = 'Email=?'; $types .= 's'; $values[] = $email;
    if ($password !== '') { $fields[] = 'Password=?'; $types .= 's'; $values[] = password_hash($password, PASSWORD_DEFAULT); }
    $fields[] = 'Phone=?'; $types .= 's'; $values[] = $phone;
    $fields[] = 'Speciality_Id=?'; $types .= 'i'; $values[] = $speciality;
    $fields[] = 'Qualification=?'; $types .= 's'; $values[] = $qualification;
    $fields[] = 'Experience_Years=?'; $types .= 'i'; $values[] = $experience;
    $fields[] = 'Bio=?'; $types .= 's'; $values[] = $bio;
    $fields[] = 'Status=?'; $types .= 's'; $values[] = $status;

    $sql = "UPDATE Doctors SET " . implode(',', $fields) . " WHERE Doctor_Id = ?";
    $types .= 'i';
    $values[] = $id;

    $stmt = $this->db->prepare($sql);
    if ($stmt === false) return false;
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
}


    public function delete($id) {
        $sql = "DELETE FROM doctors WHERE Doctor_Id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // --- Availability methods ---
    // --- Availability methods (canonicalized to Doctor_Availability / Availability_Id) ---
// --- Availability methods (robust auto-detection for table/columns) ---
// --- Availability methods (robust INFORMATION_SCHEMA lookup) ---
private $avail_meta = null;

protected function resolveAvailabilityMeta() {
    if ($this->avail_meta !== null) return $this->avail_meta;

    // Use information_schema to find any table name containing 'avail' (case-insensitive)
    $schema = $this->db->real_escape_string($this->db->query("SELECT DATABASE()")->fetch_row()[0] ?? '');
    $sql = "SELECT TABLE_NAME FROM information_schema.tables
            WHERE TABLE_SCHEMA = ? AND LOWER(TABLE_NAME) LIKE '%avail%'";
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) { error_log("resolveAvailabilityMeta prepare failed: ".$this->db->error); $this->avail_meta = null; return null; }
    $stmt->bind_param('s', $schema);
    $stmt->execute();
    $res = $stmt->get_result();
    $tables = $res->fetch_all(MYSQLI_NUM);
    $stmt->close();

    if (empty($tables)) {
        // no candidate table found
        error_log("resolveAvailabilityMeta: no availability-like table found in DB '{$schema}'");
        $this->avail_meta = null;
        return null;
    }

    // expected logical columns and common variants (lowercase)
    $expected = [
        'Availability_Id' => ['availability_id','availabilityid','availability','id','Availability_Id'],
        'Doctor_Id'       => ['doctor_id','doctorid','doctorid','Doctor_Id'],
        'Available_Day'   => ['available_day','availableday','day','day_of_week','Available_Day'],
        'Start_Time'      => ['start_time','starttime','start','Start_Time'],
        'End_Time'        => ['end_time','endtime','end','End_Time']
    ];

    // inspect each candidate table until we find one that contains all required columns (case-insensitive)
    foreach ($tables as $row) {
        $tbl = $row[0];
        // fetch columns for table
        $colsRes = $this->db->query("SHOW COLUMNS FROM `".$this->db->real_escape_string($tbl)."`");
        if (!$colsRes) continue;
        $cols = [];
        while ($c = $colsRes->fetch_assoc()) $cols[] = $c['Field'];

        // build lowercase map from actual columns -> actual column name
        $lowerToActual = [];
        foreach ($cols as $c) $lowerToActual[strtolower($c)] = $c;

        $map = ['table' => $tbl];
        $ok = true;
        foreach ($expected as $logical => $variants) {
            $found = null;
            foreach ($variants as $v) {
                $vlow = strtolower($v);
                if (isset($lowerToActual[$vlow])) { $found = $lowerToActual[$vlow]; break; }
            }
            if ($found === null) { $ok = false; break; }
            $map[$logical] = $found;
        }

        if ($ok) {
            // cache normalized meta and return
            $this->avail_meta = $map;
            return $this->avail_meta;
        }
    }

    // nothing matched fully
    error_log("resolveAvailabilityMeta: found tables but none had required availability columns");
    $this->avail_meta = null;
    return null;
}

public function getAvailability(int $doctorId) {
    // Query only the columns that exist in the table
    $sql = "SELECT Availability_Id, Doctor_Id, Available_Day, Start_Time, End_Time
            FROM doctor_availability
            WHERE Doctor_Id = ?
            ORDER BY FIELD(Available_Day,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), Start_Time";
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) {
        // Log DB error for quick debugging
        error_log("DoctorModel::getAvailability prepare failed: " . $this->db->error);
        return [];
    }
    $stmt->bind_param('i', $doctorId);
    if (!$stmt->execute()) {
        error_log("DoctorModel::getAvailability execute failed: " . $stmt->error);
        $stmt->close();
        return [];
    }
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}


public function findAvailability(int $availabilityId) {
    $sql = "SELECT Availability_Id, Doctor_Id, Available_Day, Start_Time, End_Time
            FROM doctor_availability
            WHERE Availability_Id = ? LIMIT 1";
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) { error_log("findAvailability prepare failed: ".$this->db->error); return null; }
    $stmt->bind_param('i', $availabilityId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

public function addAvailability(int $doctorId, string $day, string $start, string $end) {
    if (!$doctorId || !$day || !$start || !$end) return ['ok'=>false,'error'=>'Invalid input'];
    if ($start >= $end) return ['ok'=>false,'error'=>'Start must be before end'];
    if ($this->overlaps($doctorId, $day, $start, $end)) return ['ok'=>false,'error'=>'Overlaps existing availability'];

    $sql = "INSERT INTO doctor_availability (Doctor_Id, Available_Day, Start_Time, End_Time)
            VALUES (?,?,?,?)";
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) return ['ok'=>false,'error'=>'DB prepare failed'];
    $stmt->bind_param('isss', $doctorId, $day, $start, $end);
    $ok = $stmt->execute();
    $stmt->close();
    return ['ok' => (bool)$ok];
}

public function updateAvailability(int $availabilityId, int $doctorId, string $day, string $start, string $end, int $isActive = 1) {
    if ($start >= $end) return false;
    if ($this->overlaps($doctorId, $day, $start, $end, $availabilityId)) return false;

    $sql = "UPDATE doctor_availability SET Doctor_Id = ?, Available_Day = ?, Start_Time = ?, End_Time = ? WHERE Availability_Id = ?";
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) { error_log("updateAvailability prepare failed: ".$this->db->error); return false; }
    $stmt->bind_param('isssi', $doctorId, $day, $start, $end, $availabilityId);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool)$ok;
}

public function deleteAvailability(int $availabilityId, int $doctorId) {
    $sql = "DELETE FROM doctor_availability WHERE Availability_Id = ? AND Doctor_Id = ?";
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) { error_log("deleteAvailability prepare failed: ".$this->db->error); return false; }
    $stmt->bind_param('ii', $availabilityId, $doctorId);
    $ok = $stmt->execute();
    $stmt->close();
    return (bool)$ok;
}

/**
 * Check if a given time range overlaps existing availability for the same doctor and day.
 * If $excludeAvailabilityId is provided, that row is excluded from the check (useful for updates).
 */
public function overlaps(int $doctorId, string $day, string $start, string $end, int $excludeAvailabilityId = 0): bool {
    // Overlap condition: NOT (existing.End_Time <= new.Start_Time OR existing.Start_Time >= new.End_Time)
    $sql = "SELECT COUNT(*) AS c FROM doctor_availability WHERE Doctor_Id = ? AND Available_Day = ? AND NOT (End_Time <= ? OR Start_Time >= ?)";
    if ($excludeAvailabilityId) {
        $sql .= " AND Availability_Id <> ?";
    }
    $stmt = $this->db->prepare($sql);
    if ($stmt === false) { error_log("overlaps prepare failed: " . $this->db->error); return false; }
    if ($excludeAvailabilityId) {
        $stmt->bind_param('isssi', $doctorId, $day, $start, $end, $excludeAvailabilityId);
    } else {
        $stmt->bind_param('isss', $doctorId, $day, $start, $end);
    }
    if (!$stmt->execute()) { error_log("overlaps execute failed: " . $stmt->error); $stmt->close(); return false; }
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return ((int)($row['c'] ?? 0)) > 0;
}


}