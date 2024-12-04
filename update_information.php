<?php
$inData = getRequestInfo();

$address = $inData['address'] . " " . $inData['city'] . " " . $inData['state'] . " " . $inData['zip'];
$phone = $inData['phone'];
$available_hours = $inData['available_hours'];
$parent_info = $inData['parent_info'];

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "update_info");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
		// Check if user is already in Database
		$CMD = $conn->prepare("SELECT username FROM Users WHERE id=?");
		$CMD->bind_param("s", $user_id);
		$CMD->execute();
		$result = $CMD->get_result();
		if ($row = $result->fetch_assoc()) {
			// update information
			if ($address != "") {
				$stmt = $conn->prepare("UPDATE Users SET address = ? WHERE id = ?");
				$stmt->bind_param("ss", $address, $user_id);
				$stmt->execute();
				$stmt->close();
			}

			if ($phone != "") {
				$stmt = $conn->prepare("UPDATE Users SET phone = ? WHERE id = ?");
				$stmt->bind_param("ss", $phone, $user_id);
				$stmt->execute();
				$stmt->close();
			}

			if ($available_hours != "") {
				$stmt = $conn->prepare("UPDATE Users SET available_hours = ? WHERE id = ?");
				$stmt->bind_param("is", $available_hours, $user_id);
				$stmt->execute();
				$stmt->close();
			}

			if ($parent_info != "") {
				$stmt = $conn->prepare("UPDATE Users SET parent_info = ? WHERE id = ?");
				$stmt->bind_param("ss", $parent_info, $user_id);
				$stmt->execute();
				$stmt->close();
			}

			returnWithError("");
		}
	}
}

function getRequestInfo()
{
	return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
	header('Content-type: application/json');
	echo $obj;
}

function returnWithError($err)
{
	$retValue = '{"error":"' . $err . '"}';
	sendResultInfoAsJson($retValue);
}

?>
