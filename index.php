<?php
session_start();

$servername = "34.171.71.248"; 
$username = "shaoyan"; 
$password = "1231"; 
$dbname = "caregiver_community"; 

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
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $daily_hours = $_POST['daily_hours'];
            
            // Calculate total contract hours
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $interval = $start->diff($end);
            $total_days = $interval->days + 1; // Add 1 day to include the start date
            $total_hours = $daily_hours * $total_days;
            
            // Check if the user has enough Care Dollars
            $sql = "SELECT care_dollars FROM users WHERE id = '$user_id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            
            if ($row['care_dollars'] >= $total_hours) {
                // Create contract and deduct Care Dollars
                $sql = "INSERT INTO contracts (user_id, start_date, end_date, daily_hours, total_hours)
                        VALUES ('$user_id', '$start_date', '$end_date', '$daily_hours', '$total_hours')";
                
                if ($conn->query($sql) === TRUE) {
                    // Deduct Care Dollars
                    $new_care_dollars = $row['care_dollars'] - $total_hours;
                    $sql = "UPDATE users SET care_dollars = '$new_care_dollars' WHERE id = '$user_id'";
                    $conn->query($sql);
                    
                    echo "Contract created successfully, your Care Dollars have been updated.<br>";
                } else {
                    echo "Failed to create contract: " . $conn->error . "<br>";
                }
            } else {
                echo "You do not have enough Care Dollars to create this contract.<br>";
            }
        } else {
            echo "Please log in first.<br>";
        }
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
