<?php
// Include the database connection
include 'db.php';

// Start session to access professor's email
session_start();

// Ensure professor email exists in the session
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['error' => 'Not logged']);
    exit;
}

$professorEmail = $_SESSION['user_email'];

// Fetch theses for the logged-in professor
$query = "SELECT th_title, th_description, th_status, th_student_email FROM Thesis WHERE th_supervisor = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $professorEmail);
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
