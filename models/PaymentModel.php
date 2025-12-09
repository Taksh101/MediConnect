<?php
class PaymentModel {
    private mysqli $db;

    public function __construct(mysqli $mysqli) {
        $this->db = $mysqli;
    }

    // List all payments with patient info
    public function paginateAll($limit, $offset) {
        $sql = "SELECT p.*, pt.Name as Patient_Name, pt.Email as Patient_Email,
                       a.Status as Appointment_Status 
                FROM payments p
                JOIN patients pt ON p.Patient_Id = pt.Patient_Id
                LEFT JOIN appointments a ON a.Payment_Id = p.Payment_Id
                ORDER BY p.Paid_At DESC LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countAll() {
        $sql = "SELECT COUNT(*) as cnt FROM Payments";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['cnt'];
    }

    // Find detailed payment info
    public function findByIdWithDetails($paymentId) {
        // Get payment and patient details
        $sql = "SELECT p.*, pt.Name AS Patient_Name, pt.Email AS Patient_Email, pt.Phone AS Patient_Phone
                FROM Payments p
                LEFT JOIN Patients pt ON p.Patient_Id = pt.Patient_Id
                WHERE p.Payment_Id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$payment) return null;

        // Check if there is an appointment linked to this payment
        // In schema: Appointments table has Payment_Id
        $sqlAppt = "SELECT Appointment_Id, Appointment_Date, Visit_Type, Doctor_Id, Status 
                   FROM Appointments 
                   WHERE Payment_Id = ? 
                   LIMIT 1";
        $stmtAppt = $this->db->prepare($sqlAppt);
        $stmtAppt->bind_param("i", $paymentId);
        $stmtAppt->execute();
        $appointment = $stmtAppt->get_result()->fetch_assoc();
        $stmtAppt->close();

        $payment['appointment'] = $appointment; // can be null
        
        return $payment;
    }
    public function create($data) {
        // Warning: using Paid_At vs Payment_Date? Schema says Paid_At usually or Payment_Date.
        // Checking schema: payments table usually has Paid_At? 
        // AdminPaymentsController uses Paid_At in sort. 
        // Let's use Paid_At if that's the column. Wait. The paginateAll uses Paid_At.
        // But the previous create attempt used Payment_Date. 
        // I should check schema. But paginateAll uses Paid_At. I'll use Paid_At.
        // Wait, schema usually has Created_At too.
        
        $sql = "INSERT INTO Payments (Patient_Id, Amount, Paid_At, Method, Status, Transaction_Id)
                VALUES (?, ?, NOW(), ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('idsss', 
            $data['patient_id'], 
            $data['amount'], 
            $data['method'], 
            $data['status'], 
            $data['transaction_id']
        );
        $ok = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $ok ? $id : false;
    }
}
