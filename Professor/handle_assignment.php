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
$thesisID = $_POST['thesisId'];
$studentInfo = $_POST['studentName'];

if (empty($studentInfo)) {
    echo json_encode(['error' => 'Student value is empty']);
    exit;
}
else if(empty($thesisID)) {
    echo json_encode(['error' => 'Thesis ID value is empty']);
    exit;
}


$query = "";
$stmt = null;

try {
// Prepare the SQL query
$query = "CALL AssignThesis(?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("si", $studentInfo, $thesisID);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => "Thesis assigned successfully."]);
} else {
    echo json_encode(['error' => 'Failed to execute the stored procedure']);
}

$stmt->close();
} catch (Exception $e) {
echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>