<?php
// get variables from input
$inData = getRequestInfo();

$contract_id = $inData['contract_id']; // hopefully users won't have to remember this and website can autofill it (I have an idea)
$accepted = $inData['accepted'];

// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "caregiver_community");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	if (isset($_SESSION['user_id'])) {
		// get contract
		$contract_stmt = $conn->prepare("SELECT * FROM Contracts WHERE rating = -1 AND contract_id = ?");
		$contract_stmt->bind_param("s", $contract_id);
		$contract_stmt->execute();
		$contract_result = $contract_stmt->get_result();
		$contract_row = $contract_result->fetch_assoc();

		// check that the contract exists
		if ($contract_row) {
			// get contract values
			$owner_id = $contract_row['owner_id'];
			$receiver_id = $contract_row['receiver_id'];
			$start_date = $contract_row['start_date'];
			$end_date = $contract_row['end_date'];
			$daily_hours = $contract_row['daily_hours'];
			$total_hours = $contract_row['total_hours'];

			// check that current user is receiver
			if ($receiver_id == $user_id) {
				// Check if the user has enough Care Dollars
				$cost_stmt = $conn->prepare("SELECT care_dollars FROM Users WHERE id = ?");
				$cost_stmt->bind_param("s", $receiver_id);
				$cost_stmt->execute();
				$cost_result = $cost_stmt->get_result();
				$cost_row = $cost_result->fetch_assoc();

				$cost = $total_hours * 30;
				if ($accepted && $row['care_dollars'] >= $cost) { 
					// make contract official, use 0 for review to mean it hasn't been reviewed yet
					$accept_stmt = $conn->prepare("UPDATE Contracts SET rating = 0 WHERE contract_id = ?");
					$accept_stmt->bind_param("s", $contract_id);
					
					if ($accept_stmt->execute()) {
						// Deduct Care Dollars from care receiver (current user)
						$new_care_dollars = $row['care_dollars'] - $cost;
						$pay_stmt = $conn->prepare("UPDATE Users SET care_dollars = ? WHERE id = ?");
						$pay_stmt->bind_param("is", $new_care_dollars, $receiver_id);
						$pay_stmt->execute();
						$pay_stmt->close();

						// give Care Dollars to caregiver
						$new_care_dollars = $row['care_dollars'] + $cost;
						$pay_stmt = $conn->prepare("UPDATE Users SET care_dollars = ? WHERE id = ?");
						$pay_stmt->bind_param("is", $new_care_dollars, $owner_id);
						$pay_stmt->execute();
						$pay_stmt->close();
							
						returnWithInfo($contract_id, $new_care_dollars);
					} else
						returnWithError("Failed to create contract: " . $conn->error);
					
					$accept_stmt->close();
				} elseif (!$accepted) {
					// delete contract
					$delete_stmt = $conn->prepare("DELETE FROM Contracts WHERE contract_id = ?");
					$delete_stmt->bind_param("s", $contract_id);
					
					if ($delete_stmt->execute())
						returnWithError("");
					else
						returnWithError("Failed to delete contract: " . $conn->error);

					$delete_stmt->close();
				} else
					returnWithError("Not enough Care Dollars (you need " . $cost . ").");
				$cost_stmt->close();
			} else
				returnWithError("This is not your contract.");
		} else
			returnWithError("Contract not found.");
		$contract_stmt->close();
	} else
		returnWithError("Please log in first.");

	$conn->close();
}

function getRequestInfo()
{
	return json_decode(file_get_contents('php://input'), true);
}

function returnWithInfo($id, $new_care_dollars)
{
	$retValue = '{"id":"' . $id . '","new_care_dollars":'.$new_care_dollars.',"error":""}';
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