<?php
// MediConnect/models/PatientModel.php
class PatientModel {
    private $db;

    public function __construct(mysqli $mysqli) {
        $this->db = $mysqli;
    }

    public function emailExists(string $email): bool {
        $sql = "SELECT 1 FROM patients WHERE Email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

     public function createPatient(array $data): ?int {
        
        $sql = "INSERT INTO patients (Name, Email, Password, Phone, Gender, DOB, Address, Created_At) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed (createPatient): " . $this->db->error);
            return null;
        }

        $now = date('Y-m-d H:i:s');

        // Ensure keys exist to avoid undefined index notices
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password_hash = $data['password_hash'] ?? '';
        $phone = $data['phone'] ?? '';
        $gender = $data['gender'] ?? '';
        $dob = $data['dob'] ?? null; // should be YYYY-MM-DD or null
        $address = $data['address'] ?? '';

        // Bind parameters; use 's' for string and allow empty strings
        $stmt->bind_param('ssssssss',
            $name,
            $email,
            $password_hash,
            $phone,
            $gender,
            $dob,
            $address,
            $now
        );

        if (!$stmt->execute()) {
            error_log("Patient insert error: " . $stmt->error);
            $stmt->close();
            return null;
        }

        $insertId = $stmt->insert_id;
        $stmt->close();
        return (int)$insertId;
    }
    // MediConnect/models/PatientModel.php

// ... other methods ...

public function getByEmail(string $email): ?array {
    // 1. Explicitly select the columns needed by the login controller.
    $sql = "SELECT 
                Patient_Id, 
                Password, 
                is_profile_complete
            FROM patients 
            WHERE Email = ? 
            LIMIT 1";
    
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        // Log preparation error if needed
        error_log("Prepare failed (getByEmail): " . $this->db->error);
        return null;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    return $user;
}

public function paginate($limit, $offset) {
        $sql = "SELECT Patient_Id, Name, Email, Phone, Gender, DOB FROM Patients ORDER BY Name ASC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $res;
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) as cnt FROM Patients";
        $res = $this->db->query($sql);
        $row = $res->fetch_assoc();
        return (int)$row['cnt'];
    }

    public function findByIdWithProfile($patientId) {
        // fetch basic patient
        $sql = "SELECT * FROM Patients WHERE Patient_Id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$patient) return null;

        // fetch medical profile
        $sql2 = "SELECT * FROM Patient_Medical_Profile WHERE Patient_Id = ?";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->bind_param("i", $patientId);
        $stmt2->execute();
        $profile = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();

        $patient['medical_profile'] = $profile ?: [];
        return $patient;
    }




    public function findById($patientId) {
        $sql = "SELECT * FROM Patients WHERE Patient_Id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $patient;
    }
    public function updateBasicInfo($patientId, $name, $address) {
        $sql = "UPDATE Patients SET Name = ?, Address = ? WHERE Patient_Id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $name, $address, $patientId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
