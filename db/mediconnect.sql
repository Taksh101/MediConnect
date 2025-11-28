CREATE DATABASE IF NOT EXISTS mediconnect
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE mediconnect;

-- ==========================
-- TABLE: Patients
-- ==========================
CREATE TABLE patients (
  Patient_Id INT AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(100) NOT NULL,
  Email VARCHAR(100) UNIQUE,
  Password VARCHAR(50) NOT NULL,
  Phone VARCHAR(10) NOT NULL,
  Gender ENUM('MALE','FEMALE','OTHER') NOT NULL,
  DOB DATE NOT NULL,
  Address VARCHAR(255),
  Created_At DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- TABLE: Patient Medical Profile
-- ==========================
CREATE TABLE patient_medical_profile (
  Profile_Id INT AUTO_INCREMENT PRIMARY KEY,
  Patient_Id INT NOT NULL,
  Blood_Group ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-','Unknown') NOT NULL,
  Diabetes ENUM('Yes','No') NOT NULL,
  Blood_Pressure ENUM('Normal','Low','High') NOT NULL,
  Heart_Conditions ENUM('Yes','No') NOT NULL,
  Respiratory_Issues ENUM('Yes','No') NOT NULL,
  Allergies VARCHAR(255),
  Ongoing_Medication VARCHAR(255),
  Past_Surgeries VARCHAR(255),
  Chronic_Illnesses VARCHAR(255),
  Smoker ENUM('YES','NO','FORMER') NOT NULL,
  Alcohol_Consumption ENUM('YES','NO','Occasional') NOT NULL,
  Height_CM DECIMAL(5,2),
  Weight_KG DECIMAL(5,2),
  BMI DECIMAL(5,2),
  Updated_At DATETIME,
  FOREIGN KEY (Patient_Id) REFERENCES patients(Patient_Id) ON DELETE CASCADE
);

-- ==========================
-- TABLE: Specialities
-- ==========================
CREATE TABLE specialities (
  Speciality_Id INT AUTO_INCREMENT PRIMARY KEY,
  Speciality_Name VARCHAR(100) NOT NULL,
  Description VARCHAR(255),
  Consultation_Duration INT NOT NULL,
  Consultation_Fee DECIMAL(10,2) NOT NULL
);

-- ==========================
-- TABLE: Doctors
-- ==========================
CREATE TABLE doctors (
  Doctor_Id INT AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(100) NOT NULL,
  Email VARCHAR(100) UNIQUE,
  Password VARCHAR(50) NOT NULL,
  Phone VARCHAR(15) NOT NULL,
  Speciality_Id INT,
  Qualification VARCHAR(150) NOT NULL,
  Experience_Years INT NOT NULL,
  Bio VARCHAR(255),
  Status ENUM('AVAILABLE','UNAVAILABLE'),
  Created_At DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (Speciality_Id) REFERENCES specialities(Speciality_Id) ON DELETE SET NULL
);

-- ==========================
-- TABLE: Doctor Availability
-- ==========================
CREATE TABLE doctor_availability (
  Availability_Id INT AUTO_INCREMENT PRIMARY KEY,
  Doctor_Id INT NOT NULL,
  Available_Day ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  Start_Time TIME NOT NULL,
  End_Time TIME NOT NULL,
  FOREIGN KEY (Doctor_Id) REFERENCES doctors(Doctor_Id) ON DELETE CASCADE
);

-- ==========================
-- TABLE: Payments
-- ==========================
CREATE TABLE payments (
  Payment_Id INT AUTO_INCREMENT PRIMARY KEY,
  Patient_Id INT,
  Amount DECIMAL(10,2) NOT NULL,
  Method ENUM('UPI','Card','NetBanking','Wallet') NOT NULL,
  Status ENUM('APPROVED','PENDING','REJECTED'),
  Transaction_Id VARCHAR(100) NOT NULL,
  Paid_At DATETIME,
  FOREIGN KEY (Patient_Id) REFERENCES patients(Patient_Id) ON DELETE SET NULL
);

-- ==========================
-- TABLE: Appointments
-- ==========================
CREATE TABLE appointments (
  Appointment_Id INT AUTO_INCREMENT PRIMARY KEY,
  Patient_Id INT NOT NULL,
  Doctor_Id INT NOT NULL,
  Appointment_Date DATE NOT NULL,
  Appointment_Time TIME NOT NULL,
  Visit_Type VARCHAR(100) NOT NULL,
  Visit_Description VARCHAR(255) NOT NULL,
  Payment_Id INT,
  Status ENUM('Pending','Approved','Rejected','Completed'),
  Created_At DATETIME DEFAULT CURRENT_TIMESTAMP,
  Updated_At DATETIME ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (Patient_Id) REFERENCES patients(Patient_Id) ON DELETE CASCADE,
  FOREIGN KEY (Doctor_Id) REFERENCES doctors(Doctor_Id) ON DELETE CASCADE,
  FOREIGN KEY (Payment_Id) REFERENCES payments(Payment_Id) ON DELETE SET NULL
);

-- ==========================
-- TABLE: Consultation Notes
-- ==========================
CREATE TABLE consultation_notes (
  Note_Id INT AUTO_INCREMENT PRIMARY KEY,
  Appointment_Id INT NOT NULL,
  Doctor_Id INT,
  Symptoms VARCHAR(255),
  Diagnosis VARCHAR(255),
  Advice VARCHAR(255),
  Created_At DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (Appointment_Id) REFERENCES appointments(Appointment_Id) ON DELETE CASCADE,
  FOREIGN KEY (Doctor_Id) REFERENCES doctors(Doctor_Id) ON DELETE SET NULL
);

-- ==========================
-- TABLE: Admin
-- ==========================
CREATE TABLE admin (
  Admin_Id INT AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(100) NOT NULL,
  Email VARCHAR(100) UNIQUE,
  Password VARCHAR(50) NOT NULL
);
