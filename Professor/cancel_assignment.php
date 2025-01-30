<?php
// Include the database connection
include 'db.php';

// Start session to get logged-in professor's email
session_start();

if (!isset($_SESSION['user_email'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$professorEmail = $_SESSION['user_email'];

// Get POST data
$thesisId = $_POST['thesisId'] ?? null;

// Validate input
if (!$thesisId || !is_numeric($thesisId)) {
    echo json_encode(['error' => 'Invalid thesis ID']);
    exit;
}

// Check if the logged-in professor is the supervisor of the thesis
$checkQuery = "SELECT th_supervisor FROM Thesis WHERE th_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $thesisId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Ensure the professor is authorized
if (!$row || $row['th_supervisor'] !== $professorEmail) {
    echo json_encode(['error' => 'You are not authorized to modify this thesis.']);
    exit;
}
$stmt->close();

// Call the stored procedure
$callQuery = "CALL cancelAssignment(?)";
$stmt = $conn->prepare($callQuery);
$stmt->bind_param("i", $thesisId);

if ($stmt->execute()) {
    echo json_encode(['success' => 'Thesis assignment successfully canceled.']);
} else {
    echo json_encode(['error' => 'Failed to cancel thesis assignment.']);
}

$stmt->close();
$conn->close();
?>
