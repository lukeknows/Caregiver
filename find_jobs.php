<?php
// get variables from input
$inData = getRequestInfo();

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "find_jobs");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	$stmt = $conn->prepare("SELECT * FROM Ads");
	$stmt->execute();
	$result = $stmt->get_result();
	$output = '{ads:[';
	$first = true;

	while ($row = $result->fetch_assoc()) {
		if ($first) 
			$first = false;
		else
			$output .= ', ';

		$output .= '{"user_id":"' . $row["user_id"] . '","username":"' . $row["username"] . '","address":"' . $row["address"] . '","phone":"' . $row["phone"] . '","parent_info":"' . $row["parent_info"] . '"}';
	}

	$stmt->close();
	$output .= ']}';

	sendResultInfoAsJson($output);

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
