<?php
class AppointmentModel {
    private mysqli $db;
    public function __construct(mysqli $mysqli) {
        $this->db = $mysqli;
    }

    // find appointment by id (returns assoc array or null)
    public function findById(int $id): ?array {
        $sql = "SELECT a.*, p.Name AS patient_name, p.Email AS patient_email,
                       d.Name AS doctor_name, d.Speciality_Id AS doctor_speciality,
                       s.Speciality_Name
                FROM Appointments a
                LEFT JOIN Patients p ON a.Patient_Id = p.Patient_Id
                LEFT JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                WHERE a.Appointment_Id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    // list appointments for a specific date (yyyy-mm-dd). If null, returns all (limit optional)
    public function listByDate(?string $date = null, int $limit = 100): array {
        if ($date) {
            $sql = "SELECT a.*, p.Name AS patient_name, d.Name AS doctor_name, s.Speciality_Name
                    FROM Appointments a
                    LEFT JOIN Patients p ON a.Patient_Id = p.Patient_Id
                    LEFT JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                    LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                    WHERE a.Appointment_Date = ?
                    ORDER BY a.Appointment_Time ASC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $date, $limit);
        } else {
            $sql = "SELECT a.*, p.Name AS patient_name, d.Name AS doctor_name, s.Speciality_Name
                    FROM Appointments a
                    LEFT JOIN Patients p ON a.Patient_Id = p.Patient_Id
                    LEFT JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                    LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                    ORDER BY a.Appointment_Date DESC, a.Appointment_Time ASC
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $limit);
        }
        if (!$stmt) return [];
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }

    // create manual appointment (admin). Returns inserted id or false
    public function createManual(int $patientId, int $doctorId, string $date, string $time, string $visitType, string $visitDesc, ?int $paymentId = null, string $status = 'Pending') {
        $sql = "INSERT INTO Appointments (Patient_Id, Doctor_Id, Appointment_Date, Appointment_Time, Visit_Type, Visit_Description, Payment_Id, Status, Created_At)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('iissssis', $patientId, $doctorId, $date, $time, $visitType, $visitDesc, $paymentId, $status);
        $ok = $stmt->execute();
        if (!$ok) {
            $stmt->close();
            return false;
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    // update appointment status
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE Appointments SET Status = ?, Updated_At = NOW() WHERE Appointment_Id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('si', $status, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // optional: count by date (used on dashboard)
    public function countByDate(string $date): int {
        $sql = "SELECT COUNT(*) AS cnt FROM Appointments WHERE Appointment_Date = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $res = $stmt->get_result();
        $cnt = $res->fetch_object()->cnt ?? 0;
        $stmt->close();
        return (int)$cnt;
    }

    public function getByPatientId($patientId, $limit = 50, $offset = 0) {
        $sql = "SELECT a.Appointment_Id, a.Patient_Id, a.Doctor_Id, a.Appointment_Date, a.Appointment_Time,
                       a.Visit_Type, a.Visit_Description, a.Status,
                       d.Name AS Doctor_Name, s.Speciality_Name,
                       p.Amount AS Payment_Amount, p.Status AS Payment_Status
                FROM Appointments a
                LEFT JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                LEFT JOIN Payments p ON a.Payment_Id = p.Payment_Id
                WHERE a.Patient_Id = ?
                ORDER BY a.Appointment_Date DESC, a.Appointment_Time DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $patientId, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    // full appointment with payment and consultation notes
    public function findByIdWithAll($appointmentId) {
        $sql = "SELECT a.*, 
                       d.Name AS Doctor_Name, d.Qualification, s.Speciality_Name,
                       pt.Name AS Patient_Name, pt.Email AS Patient_Email, pt.Phone AS Patient_Phone,
                       p.Amount AS Payment_Amount, p.Method AS Payment_Method, p.Status AS Payment_Status, p.Transaction_Id, p.Paid_At
                FROM Appointments a
                LEFT JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                LEFT JOIN Patients pt ON a.Patient_Id = pt.Patient_Id
                LEFT JOIN Payments p ON a.Payment_Id = p.Payment_Id
                WHERE a.Appointment_Id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $appointmentId);
        $stmt->execute();
        $appointment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$appointment) return null;

        // consultation notes (if any)
        $sql2 = "SELECT * FROM Consultation_Notes WHERE Appointment_Id = ? ORDER BY Created_At DESC LIMIT 1";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->bind_param("i", $appointmentId);
        $stmt2->execute();
        $notes = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();

        $appointment['notes'] = $notes ?: [];
        return $appointment;
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) as cnt FROM Appointments";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['cnt'];
    }

    public function paginateAll($limit, $offset) {
        $sql = "SELECT a.Appointment_Id, a.Appointment_Date, a.Appointment_Time, a.Status, a.Visit_Type,
                       pt.Name AS Patient_Name,
                       d.Name AS Doctor_Name, s.Speciality_Name
                FROM Appointments a
                LEFT JOIN Patients pt ON a.Patient_Id = pt.Patient_Id
                LEFT JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                ORDER BY a.Appointment_Date DESC, a.Appointment_Time DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }
    public function getAppointmentsByDoctorAndDate($doctorId, $date) {
        $sql = "SELECT * FROM Appointments 
                WHERE Doctor_Id = ? 
                AND Appointment_Date = ? 
                AND Status NOT IN ('Rejected', 'Cancelled')";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("is", $doctorId, $date);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    public function findByPatientId($patientId, $limit, $offset) {
        $sql = "SELECT a.*, 
                       p.Name as Patient_Name, 
                       d.Name as Doctor_Name, 
                       s.Speciality_Name
                FROM Appointments a
                JOIN Patients p ON a.Patient_Id = p.Patient_Id
                JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                WHERE a.Patient_Id = ?
                ORDER BY a.Appointment_Date DESC, a.Appointment_Time DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("iii", $patientId, $limit, $offset);
        $stmt->execute();
        
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    public function countByPatientId($patientId) {
        $sql = "SELECT COUNT(*) as total FROM Appointments WHERE Patient_Id = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? (int)$row['total'] : 0;
    }

    public function autoUpdateStatuses(?int $doctorId = null) {
        // Ensure timezone
        date_default_timezone_set('Asia/Kolkata');
        $currentDateTime = date('Y-m-d H:i:s');

        // 1. Auto-Reject Pending appointments that have passed
        $sqlReject = "UPDATE Appointments 
                      SET Status = 'Rejected', Updated_At = NOW()
                      WHERE Status = 'Pending' 
                      AND TIMESTAMP(Appointment_Date, Appointment_Time) < ?";
        
        if ($doctorId) {
            $sqlReject .= " AND Doctor_Id = ?";
        }
        
        $stmt = $this->db->prepare($sqlReject);
        if ($doctorId) {
            $stmt->bind_param('si', $currentDateTime, $doctorId);
        } else {
            $stmt->bind_param('s', $currentDateTime);
        }
        $stmt->execute();
        $stmt->close();

        // 2. Auto-Miss Approved appointments that have ended without notes
        // We use a multi-table update logic.
        $sqlMiss = "UPDATE Appointments a 
                    JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                    JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                    LEFT JOIN Consultation_Notes cn ON a.Appointment_Id = cn.Appointment_Id
                    SET a.Status = 'Missed', a.Updated_At = NOW()
                    WHERE a.Status = 'Approved' 
                    AND cn.Note_Id IS NULL
                    AND TIMESTAMP(a.Appointment_Date, a.Appointment_Time) + INTERVAL s.Consultation_Duration MINUTE < ?";
        
        if ($doctorId) {
            $sqlMiss .= " AND a.Doctor_Id = ?";
        }

        $stmt2 = $this->db->prepare($sqlMiss);
        if ($stmt2) {
            if ($doctorId) {
                $stmt2->bind_param('si', $currentDateTime, $doctorId);
            } else {
                $stmt2->bind_param('s', $currentDateTime);
            }
            $stmt2->execute();
            $stmt2->close();
        }
    }
}
