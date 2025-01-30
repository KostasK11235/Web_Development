<?php
// Include database connection
include 'db.php';

// Check if thesis_id is provided
if (!isset($_GET['thesis_id'])) {
    echo json_encode(['error' => 'No thesis ID provided']);
    exit;
}

$thesisId = $_GET['thesis_id'];

$resultData = [];

// **🔹 First Query: Fetch Thesis Details**
$query1 = "SELECT t.th_title, t.th_description, t.th_status, p1.prof_full_name,
           COALESCE(s.std_full_name, 'Δεν έχει ανατεθεί') AS student_name,
           COALESCE(p2.prof_full_name, 'Δεν έχει οριστεί') AS committee_member1_name,
           COALESCE(p3.prof_full_name, 'Δεν έχει οριστεί') AS committee_member2_name
           FROM Thesis AS t
           LEFT JOIN Professor AS p1 ON t.th_supervisor = p1.prof_email
           LEFT JOIN Student AS s ON t.th_student_email = s.std_email
           LEFT JOIN Professor AS p2 ON t.th_committee_member1 = p2.prof_email
           LEFT JOIN Professor AS p3 ON t.th_committee_member2 = p3.prof_email
           WHERE t.th_id = ?";
$stmt1 = $conn->prepare($query1);
if (!$stmt1) {
    echo json_encode(['error' => 'Failed to prepare first query: ' . $conn->error]);
    exit;
}
$stmt1->bind_param("i", $thesisId);
$stmt1->execute();
$result1 = $stmt1->get_result();

// Fetch the first result into an associative array
if ($row1 = $result1->fetch_assoc()) {
    $resultData['thesis_details'] = $row1;
}
$stmt1->close();

// **🔹 Second Query: Fetch Committee Requests & Responses**
$query2 = "SELECT prof_full_name, req_status, req_query_date, req_answer
           FROM Requests INNER JOIN Professor ON req_professor=prof_email
           WHERE req_thesis_id = ?";
$stmt2 = $conn->prepare($query2);

if (!$stmt2) {
    echo json_encode(['error' => 'Failed to prepare second query: ' . $conn->error]);
    exit;
}
$stmt2->bind_param("i", $thesisId);
$stmt2->execute();
$result2 = $stmt2->get_result();

$committeeRequests = [];
while ($row2 = $result2->fetch_assoc()) {
    $committeeRequests[] = $row2;
}
$stmt2->close();

// Append the second query's results to the same result array
$resultData['committee_requests'] = $committeeRequests;

// Close connection
$conn->close();

// Return combined result as JSON
echo json_encode($resultData);
?>
