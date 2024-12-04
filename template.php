<?php
// get variables from input
$inData = getRequestInfo();

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "template");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	// execution

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
