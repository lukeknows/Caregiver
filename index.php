<?php
session_start();

$servername = "34.171.71.248"; 
$username = "shaoyan"; 
$password = "1231"; 
$dbname = "index"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connection successful<br>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    if (isset($_POST['register'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $address = $_POST['address'] . " " . $_POST['city'] . " " . $_POST['state'] . " " . $_POST['zip'];
        $phone = $_POST['phone'];
        $available_hours = $_POST['available_hours'];
        
        $care_dollars = 2000;
        
        $sql = "INSERT INTO users (username, password, address, phone, available_hours, care_dollars)
                VALUES ('$username', '$password', '$address', '$phone', '$available_hours', '$care_dollars')";
        
        if ($conn->query($sql) === TRUE) {
            echo "Registration successful! You have received 2000 Care Dollars.<br>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
        }
    }
    
    
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            if (password_verify($password, $row['password'])) {
                echo "Login successful! Welcome, " . $row['username'] . "<br>";
                $_SESSION['user_id'] = $row['id']; // Record user's session
            } else {
                echo "Incorrect username or password.<br>";
            }
        } else {
            echo "Incorrect username or password.<br>";
        }
    }
    
    // Handling contract creation
    if (isset($_POST['create_contract'])) {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $receiver_id = $_POST['receiver_name'];
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $daily_hours = $_POST['daily_hours'];

            // check that the receiver exists, and if so...
            $sql = "SELECT id FROM users WHERE username = '$receiver_id'";
            $result = $conn->query($sql);
            $receiver_exists = $result->fetch_assoc();
            
            if ((bool)$receiver_exists) {
                // ...get their id
                $receiver_id = $receiver_exists['id'];

                // make sure it's different from user id
                if ($receiver_id == $user_id)
                    echo "You can't be the owner and receiver of a single contract.<br>"
                else {
                    // Calculate total contract hours
                    $start = new DateTime($start_date);
                    $end = new DateTime($end_date);
                    $interval = $start->diff($end);
                    $total_days = $interval->days + 1; // Add 1 day to include the start date
                    $total_hours = $daily_hours * $total_days;

                    // generate random id (not sure how or if user IDs are being created)
                    $contract_id = uniqid();

                    // Create contract (not sure where the CREATE TABLE statements are, we might need a new one with more columns)
                    $sql = "INSERT INTO pending_contracts (contract_id, user_id, receiver_id, start_date, end_date, daily_hours, total_hours)
                            VALUES ('$contract_id', '$user_id', '$receiver_id', '$start_date', '$end_date', '$daily_hours', '$total_hours')";
                    
                    if ($conn->query($sql) === TRUE) {
                        echo "Contract created successfully with ID ". $contract_id . ".<br>";
                    } else {
                        echo "Failed to create contract: " . $conn->error . "<br>";
                    }
                }
            } else {
                echo "There is no account with this username.<br>";
            }
        } else {
            echo "Please log in first.<br>";
        }
    }

    // handling contract acceptance (might change)
    // would probably be better to notify users when they have a contract to accept than for them to manually enter
    // the caregiver's username or contract ID and have to know beforehand about the contract
    if (isset($_POST['accept_contract']) || isset($_POST['reject_contract'])) {
        $accepted = isset($_POST['accept_contract']);
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $contract_id = $_POST['contract_id']; // hopefully users won't have to remember this and website can autofill it (I have an idea)

            // get contract
            $contract_sql = "SELECT * FROM pending_contracts WHERE contract_id = '$contract_id'";
            $contract_result = $conn->query($contract_sql);
            $contract_row = $contract_result->fetch_assoc();

            // check that the contract exists
            if ((bool)$contract_row) {
                // get contract values
                $owner_id = $contract_row['user_id'];
                $receiver_id = $contract_row['receiver_id'];
                $start_date = $contract_row['start_date'];
                $end_date = $contract_row['end_date'];
                $daily_hours = $contract_row['daily_hours'];
                $total_hours = $contract_row['total_hours'];

                // check that current user is receiver
                if ($receiver_id == $user_id) {
                    // Check if the user has enough Care Dollars
                    $sql = "SELECT care_dollars FROM users WHERE id = '$user_id'";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();

                    $cost = $total_hours * 30;
                    if (!$accepted || $row['care_dollars'] >= $cost) { 
                        // create official contract and delete pending contract
                        $sql = "DELETE FROM pending_contracts WHERE contract_id = '$contract_id'\n" . $accepted ? 
                                "INSERT INTO contracts (contract_id, user_id, receiver_id, start_date, end_date, daily_hours, total_hours, review)
                                    VALUES ('$contract_id', '$owner_id', '$user_id', '$start_date', '$end_date', '$daily_hours', '$total_hours', 0)" : "";
                                    // use 0 for review to mean it hasn't been reviewed yet
                        
                        if ($conn->query($sql) === TRUE) {
                            if ($accepted) {
                                // Deduct Care Dollars from care receiver (current user)
                                $new_care_dollars = $row['care_dollars'] - $cost;
                                $sql = "UPDATE users SET care_dollars = '$new_care_dollars' WHERE id = '$user_id'";
                                $conn->query($sql);

                                // give Care Dollars to caregiver
                                $new_care_dollars = $row['care_dollars'] + $cost;
                                $sql = "UPDATE users SET care_dollars = '$new_care_dollars' WHERE id = '$owner_id'";
                                $conn->query($sql);
                                    
                                echo "Contract created successfully. Care Dollars have been transferred and you now have " . $new_care_dollars . " Care Dollars.<br>";
                            } else
                                echo "Contract deleted successfully.<br>";
                        } else
                            echo "Failed to create contract: " . $conn->error . "<br>";
                    } else
                        echo "Not enough Care Dollars (you need " . $cost . ").<br>";
                } else
                    echo "This is not your contract.<br>";
            } else
                echo "Contract not found.<br>";
        } else
            echo "Please log in first.<br>";
    }

    // handle contract review
    if (isset($_POST['review_contract'])) {
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $contract_id = $_POST['contract_id'];
            $rating = $_POST['rating'];

            // get contract
            $contract_sql = "SELECT * FROM pending_contracts WHERE contract_id = '$contract_id'";
            $contract_result = $conn->query($contract_sql);
            $contract_row = $contract_result->fetch_assoc();

            // check that the contract exists
            if ((bool)$contract_row) {
                // get receiver id
                $receiver_id = $contract_row['receiver_id'];

                // check that current user is receiver
                if ($receiver_id == $user_id) {
                    // add rating
                    $review_sql = "UPDATE contracts SET rating = '$rating' WHERE contract_id = '$contract_id'";

                    if ($conn->query($review_sql))
                        echo "Review posted successfully.<br>";
                    else
                        echo "Failed to leave review: " . $conn->error . "<br>";
                } else
                    echo "This is not your contract.<br>";
            } else
                echo "Contract not found.<br>";
        } else
            echo "Please log in first.<br>";
    }
}
?>

<!-- User Registration Form -->
<h2>Register</h2>
<form method="post">
<label for="username">Username:</label>
	<input type="text" name="username" required><br>
	<label for="password">Password:</label>
	<input type="password" name="password" required><br>
	<label for="address">Street Address:</label>
	<input type="text" name="address" size="30" required><br>
	<label for="city">City:</label>
	<input type="text" name="city" required>
	<label for="state">State:</label>
	<input type="text" name="state" size="2" minlength="2" maxlength="2" required>
	<label for="zip">ZIP:</label>
	<input type="text" name="zip" size="5" minlength="5" maxlength="5" required><br>
	<label for="phone">Phone:</label>
	<input type="text" name="phone" required><br>
	<label for="available_hours">Available Hours per Day:</label>
	<input type="number" name="available_hours" required><br>
	<input type="submit" name="register" value="Register">
</form>

<!-- User Login Form -->
<h2>Login</h2>
<form method="post">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <input type="submit" name="login" value="Login">
</form>

<!-- Create Contract Form -->
<h2>Create Contract</h2>
<form method="post">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" required><br>
    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" required><br>
    <label for="daily_hours">Daily Work Hours:</label>
    <input type="number" name="daily_hours" required><br>
    <input type="submit" name="create_contract" value="Create Contract">
</form>
