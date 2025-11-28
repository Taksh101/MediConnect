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
}
