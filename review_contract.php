<?php
// get variables from input
$inData = getRequestInfo();

$contract_id = $inData['contract_id'];
$rating = $inData['rating'];

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "caregiver_community");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];

		// get contract
		$contract_stmt = $conn->prepare("SELECT receiver_id FROM Contracts WHERE contract_id = ?");
		$contract_stmt->bind_param("s", $contract_id);
		$contract_stmt->execute();
		$contract_result = $contract_stmt->get_result();
		$contract_row = $contract_result->fetch_assoc();

		// check that the contract exists
		if ((bool) $contract_row) {
			// get receiver id
			$receiver_id = $contract_row['receiver_id'];

			// check that current user is receiver
			if ($receiver_id == $user_id) {
				// add rating
				$review_stmt = $conn->prepare("UPDATE Contracts SET rating = ? WHERE contract_id = ?");
				$review_stmt->bind_param("is", $rating, $contract_id);

				if ($review_stmt->execute())
					returnWithInfo($contract_id);
				else
					returnWithError("Failed to leave review: " . $conn->error);

				$review_stmt->close();
			} else
				returnWithError("This is not your contract.");
		} else
			returnWithError("Contract not found.");

		$contract_stmt->close();
	} else
		echo "Please log in first.<br>";


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