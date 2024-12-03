<?php
// get variables from input
$inData = getRequestInfo();

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "caregiver_community");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];

		// get user information
		$user_stmt = $conn->prepare("SELECT * FROM Users where id = ?");
		$user_stmt->bind_param("s", $user_id);
		$user_stmt->execute();
		$user_result = $user_stmt->get_result();
		$user_row = $user_result->fetch_assoc();
		$username = $user_row["username"];
		$address = $user_row["address"];
		$phone = $user_row["phone"];
		$parent_info = $user_row["parent_info"];

		$ad_stmt = $conn->prepare("INSERT INTO Ads (user_id, username, address, phone, parent_info) VALUES (?,?,?,?)");
		$ad_stmt->bind_param("ssss", $user_id, $username, $address, $phone, $parent_info);
		$ad_stmt->execute();
		$ad_stmt->close();
		$user_stmt->close();
	} else
		returnWithError("Please log in first.");

	$conn->close();
}

function getRequestInfo()
{
	return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($id)
{
	$retValue = '{"id":"' . $id . '","error":""}';
	sendResultInfoAsJson($retValue);
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