<?php
$inData = getRequestInfo();

// set variables
$username = $inData['username'];
$password = password_hash($inData['password'], PASSWORD_DEFAULT);

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "caregiver_community");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	$stmt = $conn->prepare("SELECT id, username, password FROM Users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();

		if (password_verify($password, $row['password'])) {
			echo "Login successful! Welcome, " . $row['username'] . "<br>";
			returnWithInfo($row["id"]);
			$_SESSION['user_id'] = $row['id']; // Record user's session
		} else {
			returnWithError("Incorrect username or password.");
		}
	} else {
		returnWithError("Incorrect username or password.");
	}

	$stmt->close();
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