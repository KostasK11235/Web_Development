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
$studentInfo = $_POST['student'];

if (empty($studentInfo)) {
    echo json_encode(['error' => 'Search value is empty']);
    exit;
}


$query = "";
$stmt = null;

// Prepare the SQL query
$query = "SELECT std_full_name FROM Student WHERE std_full_name = ? OR std_AM = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $studentInfo, $studentInfo);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$studentName = [];
while ($row = $result->fetch_assoc()) {
    $studentName[] = $row;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Return the invitations as JSON
echo json_encode($studentName);
?>
