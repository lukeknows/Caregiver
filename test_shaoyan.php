<?php
$servername = "localhost";  // 使用本地 MySQL
$username = "shaoyan";      // 创建的用户名
$password = "1231"; // 设置的密码
$dbname = "caregiver_community"; // 数据库名称

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>

