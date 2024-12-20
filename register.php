<?php
	$inData = getRequestInfo();
	
	$username = $inData["username"];
	$password = $inData["password"];
	$address = $inData['address'] . " " . $inData['city'] . " " . $inData['state'] . " " . $inData['zip'];
	$phone = $inData['phone'];
	$available_hours = $inData['available_hours'];
	$parent_info = $inData['parent_info']; // I guess we'll just make this a string, should probably be a text area
	$care_dollars = 2000;

	// connect to DB
	$conn = new mysqli("localhost", "shaoyan", "", "register");
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{
		// Check if user is already in Database
        $CMD = $conn->prepare("SELECT username FROM Users WHERE username=?");
        $CMD->bind_param("s", $username);
		$CMD->execute();
		$result = $CMD->get_result();
        if( $row = $result->fetch_assoc() )
			returnWithError("This username is already in use.");
		else {
			// create unique id
			$user_id = uniqid();
			$_SESSION['user_id'] = $user_id; // Record user's session

			// insert user into database
			$stmt = $conn->prepare("INSERT into Users (id,username,password,address,phone,available_hours,care_dollars,parent_info) VALUES(?,?,?,?,?,?,?,?)");
			$stmt->bind_param("sssssiis", $user_id, $username, $password, $address, $phone, $available_hours, $care_dollars, $parent_info);
			$stmt->execute();
			$stmt->close();
			$conn->close();
			returnWithError("");
		}
	}

	function getRequestInfo()
	{
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
?>
