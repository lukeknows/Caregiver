<?php
// connect to DB
$conn = new mysqli("localhost", "shaoyan", "", "caregiver_community");
if ($conn->connect_error) {
	returnWithError($conn->connect_error);
} else {
	$users_stmt = $conn->prepare("create table Users (
		id char(13),
		username varchar(20),
		password varchar(30),
		address varchar(100),
		phone char(12),
		available_hours integer,
		care_dollars integer,
		parent_info varchar(200),
		primary key (id)
		)");
	$users_stmt->execute();
	$users_stmt->close();

	$contracts_stmt = $conn->prepare("create table Contracts (
		contract_id char(13),
		owner_id char(13),
		receiver_id char(13),
		start_date date,
		end_date date,
		daily_hours integer,
		total_hours integer,
		rating integer,
		primary key (contract_id),
		foreign key (owner_id) references Users,
		foreign key (receiver_id) references Users
		)");
	$contracts_stmt->execute();
	$contracts_stmt->close();
	$conn->close();

	$ads_stmt = $conn->prepare("create table Ads (
		user_id char(13),
		username varchar(20),
		address varchar(100),
		phone char(12),
		parent_info varchar(200),
		primary key (user_id),
		foreign key (user_id) references Users
		)");
	$ads_stmt->execute();
	$ads_stmt->close();
	$conn->close();
	returnWithError("");
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