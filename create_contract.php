<?php
// get variables from input
$inData = getRequestInfo();

$receiver_name = $inData['receiver_name'];
$start_date = $inData['start_date'];
$end_date = $inData['end_date'];
$daily_hours = $inData['daily_hours'];

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "caregiver_community");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];

		// check that the receiver exists, and if so...
		$stmt = $conn->prepare("SELECT id FROM Users WHERE username = ?");
		$stmt->bind_param("s", $receiver_name);
		$stmt->execute();
		$result = $stmt->get_result();
		$receiver_exists = $result->fetch_assoc();
		
		if ($receiver_exists) {
			// ...get their id
			$receiver_id = $receiver_exists['id'];

			// make sure it's different from user id
			if ($receiver_id == $user_id)
				returnWithError("You can't be the owner and receiver of a single contract.");
			else {
				// Calculate total contract hours
				$start = new DateTime($start_date);
				$end = new DateTime($end_date);
				$interval = $start->diff($end);
				$total_days = $interval->days + 1; // Add 1 day to include the start date
				$total_hours = $daily_hours * $total_days;

				// generate random id
				$contract_id = uniqid();

				// Create contract (not sure where the CREATE TABLE statements are, we might need a new one with more columns)
				$stmt = $conn->prepare("INSERT INTO Contracts (contract_id, owner_id, receiver_id, start_date, end_date, daily_hours, total_hours, rating)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
				$rating = -1; // -1 for contract not accepted yet, 0 for contract accepted but not reviewed yet
				$stmt->bind_param("sssssiii", $contract_id, $user_id, $receiver_id, $start_date, $end_date, $daily_hours, $total_hours, $rating);
				
				if ($stmt->execute()) {
					returnWithInfo($contract_id);
				} else {
					returnWithError("Failed to create contract: " . $conn->error);
				}

				$stmt->close();
			}
		} else {
			returnWithError("There is no account with the username you provided.");
		}
	} else {
		returnWithError("Please log in first.");
	}

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