<?php
// MediConnect/models/PatientMedicalProfile.php

class PatientMedicalProfile
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function getByPatient(int $patientId)
    {
        $stmt = $this->db->prepare("SELECT * FROM Patient_Medical_Profile WHERE Patient_Id = ?");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    /**
     * Save (insert or update). $data keys:
     * Patient_Id (int), Blood_Group, Diabetes, Blood_Pressure, Heart_Conditions,
     * Respiratory_Issues, Allergies, Ongoing_Medication, Past_Surgeries,
     * Chronic_Illnesses, Smoker, Alcohol_Consumption, Height_CM (float|null),
     * Weight_KG (float|null), BMI (float|null)
     */
    public function save(array $data): bool
    {
        $patientId = (int)$data['Patient_Id'];

        // compute BMI server-side if not provided but height&weight present
        if ((!isset($data['BMI']) || $data['BMI'] === null) && !empty($data['Height_CM']) && !empty($data['Weight_KG'])) {
            $h = floatval($data['Height_CM']) / 100.0;
            if ($h > 0) {
                $data['BMI'] = round(floatval($data['Weight_KG']) / ($h * $h), 2);
            } else {
                $data['BMI'] = null;
            }
        }

        $existing = $this->getByPatient($patientId);
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE Patient_Medical_Profile SET
                Blood_Group=?, Diabetes=?, Blood_Pressure=?, Heart_Conditions=?, Respiratory_Issues=?,
                Allergies=?, Ongoing_Medication=?, Past_Surgeries=?, Chronic_Illnesses=?,
                Smoker=?, Alcohol_Consumption=?, Height_CM=?, Weight_KG=?, BMI=?
                WHERE Patient_Id=?
            ");
            if (!$stmt) return false;

            // 11 strings, 3 doubles, 1 int
            $bg = $data['Blood_Group'];
            $diabetes = $data['Diabetes'];
            $bp = $data['Blood_Pressure'];
            $heart = $data['Heart_Conditions'];
            $resp = $data['Respiratory_Issues'];
            $all = $data['Allergies'];
            $med = $data['Ongoing_Medication'];
            $sur = $data['Past_Surgeries'];
            $chro = $data['Chronic_Illnesses'];
            $smoker = $data['Smoker'];
            $alc = $data['Alcohol_Consumption'];
            $hcm = $data['Height_CM'] === null ? null : floatval($data['Height_CM']);
            $wkg = $data['Weight_KG'] === null ? null : floatval($data['Weight_KG']);
            $bmi = $data['BMI'] === null ? null : floatval($data['BMI']);

            // bind params: 11s + 3d + i
            $stmt->bind_param(
                "sssssssssssdddi",
                $bg, $diabetes, $bp, $heart, $resp,
                $all, $med, $sur, $chro, $smoker, $alc,
                $hcm, $wkg, $bmi, $patientId
            );

            return $stmt->execute();
        }

        // insert
        $stmt = $this->db->prepare("
            INSERT INTO Patient_Medical_Profile
            (Patient_Id, Blood_Group, Diabetes, Blood_Pressure, Heart_Conditions, Respiratory_Issues,
            Allergies, Ongoing_Medication, Past_Surgeries, Chronic_Illnesses, Smoker, Alcohol_Consumption,
            Height_CM, Weight_KG, BMI)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        if (!$stmt) return false;

        $bg = $data['Blood_Group'];
        $diabetes = $data['Diabetes'];
        $bp = $data['Blood_Pressure'];
        $heart = $data['Heart_Conditions'];
        $resp = $data['Respiratory_Issues'];
        $all = $data['Allergies'];
        $med = $data['Ongoing_Medication'];
        $sur = $data['Past_Surgeries'];
        $chro = $data['Chronic_Illnesses'];
        $smoker = $data['Smoker'];
        $alc = $data['Alcohol_Consumption'];
        $hcm = $data['Height_CM'] === null ? null : floatval($data['Height_CM']);
        $wkg = $data['Weight_KG'] === null ? null : floatval($data['Weight_KG']);
        $bmi = $data['BMI'] === null ? null : floatval($data['BMI']);

        $stmt->bind_param(
            "isssssssssssddd",
            $patientId, $bg, $diabetes, $bp, $heart, $resp,
            $all, $med, $sur, $chro, $smoker, $alc,
            $hcm, $wkg, $bmi
        );

        return $stmt->execute();
    }
}
