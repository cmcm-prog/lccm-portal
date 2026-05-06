<?php
// 1. START SESSION (Crucial so the dashboard knows who is logged in)
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. DATABASE CONNECTION
$servername = "mysql.railway.internal"; 
$username = "root";
$password = "nAhfmxSpwiwRmvwGfihndXTOUEVjInjC"; 
$dbname = "railway";
$port = 3306;

$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 3. LOGIC HANDLER
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['user_role'] ?? '';

    // --- FACULTY LOGIN LOGIC ---
    if ($role === "Faculty") {
        $email = mysqli_real_escape_string($conn, $_POST['faculty_email']);
        $submitted_pass = $_POST['password'];

        // FIX: We look in the 'name' column because that's where emails are in your DB (image_954ecc.png)
        $query = "SELECT * FROM faculty WHERE name = '$email' LIMIT 1";
        
        // FIX: Actually execute the query (This was missing before!)
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // FIX: Match against 'Password' with a capital 'P' (image_954ecc.png)
            if (password_verify($submitted_pass, $user['Password'])) {
                // Success! Set session variables
                $_SESSION['faculty_id'] = $user['faculty_id'];
                $_SESSION['faculty_name'] = $user['name'];
                
                header("Location: faculty_dashboard.php");
                exit();
            } else {
                echo "<script>alert('Wrong password! Make sure you used a HASH in the database.');</script>";
            }
        } else {
            echo "<script>alert('No faculty account found with that email.');</script>";
        }

    // --- VISITOR REQUEST LOGIC ---
    } elseif ($role === "Visitor") {
        $name    = mysqli_real_escape_string($conn, $_POST['username']);
        $email   = mysqli_real_escape_string($conn, $_POST['email']);
        $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);

        // Save with 'Pending' status so Faculty can approve it later
        $sql = "INSERT INTO visitors (name, email, purpose, status) VALUES ('$name', '$email', '$purpose', 'Pending')";
        
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Request Submitted! Please wait for Faculty approval.');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>
