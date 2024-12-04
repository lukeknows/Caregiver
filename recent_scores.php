<?php
// get variables from input
$inData = getRequestInfo();

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "recent_scores");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
		$stmt = $conn->prepare("SELECT contract_id, rating, end_date FROM Contracts WHERE owner_id = ?");
		$stmt->bind_param("s", $user_id);
		$stmt->execute();
		$result = $stmt->get_result();

		$recent_contracts = [];

		while ($row = $result->fetch_assoc()) {
			$contract_id = $row["contract_id"];
			$rating = $row["rating"];
			$date = $row["end_date"]; // might need to be converted to number
			$recent_contracts[$date] = [$contract_id, $rating];
			ksort($recent_contracts);
			if (count($recent_contracts) > 10)
				array_splice($recent_contracts, 0, 1);
		}

		$output = '{"scores":{';
		$first = true; // avoid extra comma

		foreach ($recent_contracts as $date => $info) {
			if ($first)
				$first = false;
			else
				$output .= ', ';
			$output .= '"' . $info[0] . '":' . $info[1];
		}

		$output .= '}}';

		$stmt->close();
	} else
		returnWithError('Please log in first.');

	$conn->close();
}

function getRequestInfo()
{
	return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($scores)
{
	$retValue = '{"scores":"' . $scores . '","error":""}';
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
