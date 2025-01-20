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

// Determine which query to execute based on a request parameter
$queryType = $_GET['queryType'] ?? 'supervised'; // Default to supervised theses

if ($queryType === 'participated') {
    // Query for participated theses
    $query = "SELECT t.th_title, t.th_description, t.th_status, p.prof_full_name 
              FROM Professor AS p 
              INNER JOIN Thesis AS t 
              ON t.th_supervisor = p.prof_email 
              WHERE t.th_supervisor = ? OR t.th_committee_member1 = ? OR t.th_committee_member2 = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sss", $professorEmail, $professorEmail, $professorEmail);
} else {
    // Default query for supervised theses
    $query = "SELECT th_title, th_description, th_status FROM Thesis WHERE th_supervisor = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $professorEmail);
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
