<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

header('Content-Type: application/json');

// Fetch data from the Announcements table
$query = "SELECT ann_id, ann_title, ann_content, ann_date FROM Announcements";
$result = $conn->query($query);

$announcements = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    echo json_encode($announcements);
} else {
    echo json_encode(['error' => $conn->error]);
}

$conn->close();
?>
