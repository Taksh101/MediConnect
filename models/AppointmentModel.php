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
        $stmt->bind_param('iisssiss', $patientId, $doctorId, $date, $time, $visitType, $visitDesc, $paymentId, $status);
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
}
