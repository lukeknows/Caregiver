<?php
// get variables from input
$inData = getRequestInfo();

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "average");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	// make sure the user is logged in
	if (isset($_SESSION["user_id"])) {
		$user_id = $_SESSION["user_id"];
		$stmt = $conn->prepare("SELECT rating FROM Contracts WHERE user_id = ?");
		$stmt->bind_param("s", $user_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$num_contracts = 0;
		$total_rating = 0;
		while ($row = $result->fetch_assoc()) {
			if ($row["rating"] > 0) { // ignore unreviewed contracts
				$num_contracts++;
				$total_rating += $row["rating"];
			}
		}
		if ($num_contracts > 0) {
			$avg_rating = $total_rating / $num_contracts;
			returnWithInfo($avg_rating);
		} else
			returnWithInfo(0);
	} else
		returnWithError("Please log in first.");

	$conn->close();
}

function getRequestInfo()
{
	return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($rating)
{
	// rating of 0 means the user hasn't received a review yet
	$retValue = '{"rating":' . $rating . ',"error":""}';
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
