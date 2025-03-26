<?php
include('connection.php');

if (!isset($_GET['i']) || !is_numeric($_GET['i'])) {
    die("Invalid request: ID not provided or not a number!");
}

$id = intval($_GET['i']); // Convert to an integer for security

// Check if the ID exists
$stmt = $con->prepare("SELECT * FROM garbageinfo WHERE Id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: No record found with ID $id");
}

// Execute the delete query using prepared statements to prevent SQL injection
$deleteStmt = $con->prepare("DELETE FROM garbageinfo WHERE Id = ?");
$deleteStmt->bind_param("i", $id);

if ($deleteStmt->execute()) {
    echo "<script>alert('Record deleted successfully!'); window.location.href='welcome.php';</script>";
} else {
    echo "<script>alert('Failed to delete record!');</script>";
}

// Close statements and connection
$stmt->close();
$deleteStmt->close();
$con->close();
?>
