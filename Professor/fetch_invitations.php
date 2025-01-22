<?php
// Include the database connection
include 'db.php';

// Start session to access the logged-in user's email
session_start();

// Ensure professor email exists in the session
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_email = $_SESSION['user_email'];

// Prepare the SQL query
$query = "SELECT t.th_title, t.th_supervisor, t.th_id, t.th_description
          FROM Thesis AS t 
          INNER JOIN Requests AS r 
          ON r.req_thesis_id = t.th_id 
          WHERE r.req_professor = ?";

$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
    exit;
}

// Bind the parameter and execute the query
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$invitations = [];
while ($row = $result->fetch_assoc()) {
    $invitations[] = $row;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Return the invitations as JSON
echo json_encode($invitations);
?>
