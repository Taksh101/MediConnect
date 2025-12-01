<?php
class AdminModel {
    private $db;

    public function __construct(mysqli $mysqli) {
        $this->db = $mysqli;
    }

    public function getByEmail(string $email): ?array {
        $sql = "SELECT * FROM admin WHERE Email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) return null;
        return $res->fetch_assoc();
    }
    public function countPatients(): int {
        $sql = "SELECT COUNT(*) AS cnt FROM Patients";
        $res = $this->db->query($sql);
        return $res ? (int)$res->fetch_object()->cnt : 0;
    }

    public function countDoctors(): int {
        $sql = "SELECT COUNT(*) AS cnt FROM Doctors";
        $res = $this->db->query($sql);
        return $res ? (int)$res->fetch_object()->cnt : 0;
    }

    public function countSpecialities(): int {
        $sql = "SELECT COUNT(*) AS cnt FROM Specialities";
        $res = $this->db->query($sql);
        return $res ? (int)$res->fetch_object()->cnt : 0;
    }

    public function countTodaysAppointments(): int {
        $sql = "SELECT COUNT(*) AS cnt FROM Appointments WHERE Appointment_Date = CURDATE()";
        $res = $this->db->query($sql);
        return $res ? (int)$res->fetch_object()->cnt : 0;
    }

    /**
     * Get today's appointments with patient, doctor and speciality.
     * Returns array of associative rows.
     */
    public function getTodaysAppointments(): array {
        $sql = "SELECT a.Appointment_Id, a.Appointment_Date, a.Appointment_Time, a.Status,
                       p.Patient_Id, p.Name AS patient_name,
                       d.Doctor_Id, d.Name AS doctor_name,
                       s.Speciality_Id, s.Speciality_Name
                FROM Appointments a
                LEFT JOIN Patients p ON a.Patient_Id = p.Patient_Id
                LEFT JOIN Doctors d ON a.Doctor_Id = d.Doctor_Id
                LEFT JOIN Specialities s ON d.Speciality_Id = s.Speciality_Id
                WHERE a.Appointment_Date = CURDATE()
                ORDER BY a.Appointment_Time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }
}
