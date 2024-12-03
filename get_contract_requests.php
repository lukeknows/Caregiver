<?php
// get variables from input
$inData = getRequestInfo();

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "caregiver_community");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	// make sure user is logged in
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];

		$stmt = $conn->prepare("SELECT contract_id FROM Contracts WHERE rating = -1 AND receiver_id = ?");
		$stmt->bind_param("s", $user_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$output = '{"contracts": [';

		if ($row = $result->fetch_assoc()) // prevent extra comma
			$output .= '"' . $row['contract_id'] . '"';
		while ($row = $result->fetch_assoc())
			$output .= ',"' . $row['contract_id'] . '"';
		
		$output .= "]}";
		sendResultInfoAsJson($output);
		$stmt->close();
	} else
		returnWithError("Please log in first.");

	$conn->close();
}

function getRequestInfo()
{
	return json_decode(file_get_contents('php://input'), true);
}

function returnWithError($err)
{
	$retValue = '{"error":"' . $err . '"}';
	sendResultInfoAsJson($retValue);
}

function sendResultInfoAsJson($obj)
{
	header('Content-type: application/json');
	echo $obj;
}
?>