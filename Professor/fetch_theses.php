<?php
// Include the database connection
include 'db.php';

// Start session to access professor's email
session_start();

// Ensure professor email exists in the session
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$professorEmail = $_SESSION['user_email'];

// Get query parameters
$queryType = $_GET['queryType'] ?? 'supervised';
$status = $_GET['status'] ?? '';
$role = $_GET['role'] ?? '';

$query = "";
$stmt = null;

// Build the query dynamically based on input
if ($queryType === 'participated') {
    if ($role === 'Επιβλέπων') {
        $query = "SELECT t.th_title, t.th_description, t.th_status, p.prof_full_name, t.th_id 
                  FROM Professor AS p 
                  INNER JOIN Thesis AS t 
                  ON t.th_supervisor = p.prof_email 
                  WHERE t.th_supervisor = ?";

        if (!empty($status)) {
            $query .= " AND t.th_status = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $professorEmail, $status);
        }else {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $professorEmail);
        }
    } elseif ($role === 'Μέλος') {
        $query = "SELECT t.th_title, t.th_description, t.th_status, p.prof_full_name, t.th_id
                  FROM Professor AS p 
                  INNER JOIN Thesis AS t 
                  ON t.th_supervisor = p.prof_email";

        if (!empty($status)) {
            $query .= " WHERE (t.th_committee_member1 = ? OR t.th_committee_member2 = ?) AND t.th_status = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $professorEmail, $professorEmail, $status);
        }else {
            $query .= " WHERE t.th_committee_member1 = ? OR t.th_committee_member2 = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $professorEmail, $professorEmail);
        }
    } else {
        $query = "SELECT t.th_title, t.th_description, t.th_status, p.prof_full_name, t.th_id 
                  FROM Professor AS p 
                  INNER JOIN Thesis AS t 
                  ON t.th_supervisor = p.prof_email";

        if (!empty($status)) {
            $query .= " WHERE (t.th_supervisor = ? OR t.th_committee_member1 = ? OR t.th_committee_member2 = ?) AND t.th_status = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $professorEmail, $professorEmail, $professorEmail, $status);
        }else {
            $query .= " WHERE t.th_supervisor = ? OR t.th_committee_member1 = ? OR t.th_committee_member2 = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $professorEmail, $professorEmail, $professorEmail);
        }
    }
} else {
    // Default to supervised theses
    $query = "SELECT t.th_title, t.th_description, t.th_status, t.th_id FROM Thesis AS t WHERE t.th_supervisor = ?";
    if (!empty($status)) {
        $query .= " AND t.th_status = ? AND t.th_student_email IS NULL";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $professorEmail, $status);
    }else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $professorEmail);
    }
}

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$theses = [];
while ($row = $result->fetch_assoc()) {
    $theses[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($theses);
?>
