-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 27, 2025 at 05:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mediconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `Admin_Id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `Appointment_Id` int(11) NOT NULL,
  `Patient_Id` int(11) NOT NULL,
  `Doctor_Id` int(11) NOT NULL,
  `Appointment_Date` date NOT NULL,
  `Appointment_Time` time NOT NULL,
  `Visit_Type` varchar(100) NOT NULL,
  `Visit_Description` varchar(255) NOT NULL,
  `Payment_Id` int(11) DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp(),
  `Updated_At` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consultation_notes`
--

CREATE TABLE `consultation_notes` (
  `Note_Id` int(11) NOT NULL,
  `Appointment_Id` int(11) NOT NULL,
  `Doctor_Id` int(11) DEFAULT NULL,
  `Symptoms` varchar(255) DEFAULT NULL,
  `Diagnosis` varchar(255) DEFAULT NULL,
  `Advice` varchar(255) DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `Doctor_Id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Speciality_Id` int(11) DEFAULT NULL,
  `Qualification` varchar(150) NOT NULL,
  `Experience_Years` int(11) NOT NULL,
  `Bio` varchar(255) DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_availability`
--

CREATE TABLE `doctor_availability` (
  `Availability_Id` int(11) NOT NULL,
  `Doctor_Id` int(11) NOT NULL,
  `Available_Day` varchar(20) NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `Patient_Id` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(15) NOT NULL,
  `Gender` varchar(20) NOT NULL,
  `DOB` date NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient_medical_profile`
--

CREATE TABLE `patient_medical_profile` (
  `Profile_Id` int(11) NOT NULL,
  `Patient_Id` int(11) NOT NULL,
  `Blood_Group` varchar(10) NOT NULL,
  `Diabetes` varchar(10) NOT NULL,
  `Blood_Pressure` varchar(20) NOT NULL,
  `Heart_Conditions` varchar(10) NOT NULL,
  `Respiratory_Issues` varchar(10) NOT NULL,
  `Allergies` varchar(255) DEFAULT NULL,
  `Ongoing_Medication` varchar(255) DEFAULT NULL,
  `Past_Surgeries` varchar(255) DEFAULT NULL,
  `Chronic_Illnesses` varchar(255) DEFAULT NULL,
  `Smoker` varchar(15) NOT NULL,
  `Alcohol_Consumption` varchar(20) NOT NULL,
  `Height_CM` int(11) DEFAULT NULL,
  `Weight_KG` int(11) DEFAULT NULL,
  `BMI` decimal(5,2) DEFAULT NULL,
  `Updated_At` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `Payment_Id` int(11) NOT NULL,
  `Patient_Id` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `Method` varchar(20) NOT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `Transaction_Id` varchar(100) DEFAULT NULL,
  `Paid_At` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specialities`
--

CREATE TABLE `specialities` (
  `Speciality_Id` int(11) NOT NULL,
  `Speciality_Name` varchar(100) NOT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Consultation_Duration` int(11) NOT NULL,
  `Consultation_Fee` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_Id`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`Appointment_Id`),
  ADD KEY `Patient_Id` (`Patient_Id`),
  ADD KEY `Doctor_Id` (`Doctor_Id`),
  ADD KEY `Payment_Id` (`Payment_Id`);

--
-- Indexes for table `consultation_notes`
--
ALTER TABLE `consultation_notes`
  ADD PRIMARY KEY (`Note_Id`),
  ADD KEY `Appointment_Id` (`Appointment_Id`),
  ADD KEY `Doctor_Id` (`Doctor_Id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`Doctor_Id`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `Speciality_Id` (`Speciality_Id`);

--
-- Indexes for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD PRIMARY KEY (`Availability_Id`),
  ADD KEY `Doctor_Id` (`Doctor_Id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`Patient_Id`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `patient_medical_profile`
--
ALTER TABLE `patient_medical_profile`
  ADD PRIMARY KEY (`Profile_Id`),
  ADD KEY `Patient_Id` (`Patient_Id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`Payment_Id`),
  ADD KEY `Patient_Id` (`Patient_Id`);

--
-- Indexes for table `specialities`
--
ALTER TABLE `specialities`
  ADD PRIMARY KEY (`Speciality_Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `Admin_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `Appointment_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consultation_notes`
--
ALTER TABLE `consultation_notes`
  MODIFY `Note_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `Doctor_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  MODIFY `Availability_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `Patient_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patient_medical_profile`
--
ALTER TABLE `patient_medical_profile`
  MODIFY `Profile_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `Payment_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `specialities`
--
ALTER TABLE `specialities`
  MODIFY `Speciality_Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`Patient_Id`) REFERENCES `patients` (`Patient_Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`Doctor_Id`) REFERENCES `doctors` (`Doctor_Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`Payment_Id`) REFERENCES `payments` (`Payment_Id`) ON DELETE SET NULL;

--
-- Constraints for table `consultation_notes`
--
ALTER TABLE `consultation_notes`
  ADD CONSTRAINT `consultation_notes_ibfk_1` FOREIGN KEY (`Appointment_Id`) REFERENCES `appointments` (`Appointment_Id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultation_notes_ibfk_2` FOREIGN KEY (`Doctor_Id`) REFERENCES `doctors` (`Doctor_Id`) ON DELETE SET NULL;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`Speciality_Id`) REFERENCES `specialities` (`Speciality_Id`) ON DELETE SET NULL;

--
-- Constraints for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD CONSTRAINT `doctor_availability_ibfk_1` FOREIGN KEY (`Doctor_Id`) REFERENCES `doctors` (`Doctor_Id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_medical_profile`
--
ALTER TABLE `patient_medical_profile`
  ADD CONSTRAINT `patient_medical_profile_ibfk_1` FOREIGN KEY (`Patient_Id`) REFERENCES `patients` (`Patient_Id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`Patient_Id`) REFERENCES `patients` (`Patient_Id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
