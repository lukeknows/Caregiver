<?php
// connect to DB
$conn = new mysqli("localhost", "shaoyan", "1231", "initialize_database");
if ($conn->connect_error) {
    returnWithError($conn->connect_error);
} else {
    // Create Users table
    $users_stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS Users (
        id CHAR(13),
        username VARCHAR(20),
        password VARCHAR(30),
        address VARCHAR(100),
        phone CHAR(12),
        available_hours INT,
        care_dollars INT,
        parent_info VARCHAR(200),
        PRIMARY KEY (id)
    )");
    $users_stmt->execute();
    $users_stmt->close();

    // Create Contracts table
    $contracts_stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS Contracts (
        contract_id CHAR(13),
        owner_id CHAR(13),
        receiver_id CHAR(13),
        start_date DATE,
        end_date DATE,
        daily_hours INT,
        total_hours INT,
        rating INT,
        PRIMARY KEY (contract_id),
        FOREIGN KEY (owner_id) REFERENCES Users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES Users(id) ON DELETE CASCADE
    )");
    $contracts_stmt->execute();
    $contracts_stmt->close();

    // Create Ads table
    $ads_stmt = $conn->prepare("CREATE TABLE IF NOT EXISTS Ads (
        user_id CHAR(13),
        username VARCHAR(20),
        address VARCHAR(100),
        phone CHAR(12),
        parent_info VARCHAR(200),
        PRIMARY KEY (user_id),
        FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
    )");
    $ads_stmt->execute();
    $ads_stmt->close();
    
    // Close the connection
    $conn->close();
    
    returnWithError("");
}

function sendResultInfoAsJson($obj) {
    header('Content-type: application/json');
    echo $obj;
}

function returnWithError($err) {
    $retValue = '{"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}
?>

