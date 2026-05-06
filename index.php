<?php
// 1. START SESSION (This is the "VIP Pass" that remembers the user)
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

        // Find the faculty by email
        $query = "SELECT * FROM faculty WHERE email = '$email' LIMIT 1";
    
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // VERIFY PASSWORD: This checks if the typed password matches the HASH in the DB
            if (password_verify($submitted_pass, $user['password'])) {
                // Success! Save user info to the Session
                $_SESSION['faculty_id'] = $user['id'];
                $_SESSION['faculty_name'] = $user['name'];
                
                header("Location: faculty_dashboard.php");
                exit();
            } else {
                echo "<script>alert('Wrong password! Access Denied.');</script>";
            }
        } else {
            echo "<script>alert('No faculty account found with that email.');</script>";
        }

    // --- VISITOR REQUEST LOGIC ---
    } elseif ($role === "Visitor") {
        $name    = mysqli_real_escape_string($conn, $_POST['username']);
        $email   = mysqli_real_escape_string($conn, $_POST['email']);
        $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);

        // Insert as 'Pending' status (Ensure you added the 'status' column to your DB!)
        $sql = "INSERT INTO visitors (name, email, purpose, status) VALUES ('$name', '$email', '$purpose', 'Pending')";
        
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Request Submitted! Please check your email for approval later.');</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LCCM - Portal Access</title>
    <style>
        /* [Keeping your existing CSS styles here...] */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: serif; }
        body { height: 100vh; overflow: hidden; position: relative; background-color: #000; }
        body::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.2; z-index: -1; background-image: url('campus-bg.jpg'); background-position: center; background-repeat: no-repeat; background-size: cover; }
        .header-bar { background-color: #1a428a; color: white; text-align: center; padding: 10px 0; border-bottom: 2px solid white; position: relative; z-index: 1; }
        .header-bar h1 { font-size: 1.2rem; text-transform: uppercase; }
        .header-bar p { font-size: 0.8rem; font-style: italic; }
        .container { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 80vh; position: relative; z-index: 1; }
        .page-title { color: white; font-size: 2.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); margin-bottom: 30px; letter-spacing: 2px; }
        .content-wrapper { display: flex; align-items: center; gap: 50px; max-width: 1000px; }
        .logo-section img { width: 350px; }
        .login-box { background-color: rgba(26, 66, 138, 0.9); padding: 40px; border-radius: 10px; width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .form-group { display: flex; align-items: center; margin-bottom: 20px; }
        .form-group label { color: white; width: 150px; font-size: 0.85rem; font-weight: bold; }
        .form-group input, .form-group select { flex: 1; padding: 8px; border: none; outline: none; background-color: white; }
        #visitor-section, #faculty-section { display: none; }
        .btn-container { text-align: right; margin-top: 10px; }
        .login-btn { background-color: white; color: #1a428a; border: none; padding: 8px 30px; font-size: 1rem; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .login-btn:hover { background-color: #ddd; }
    </style>
</head>
<body>

    <div class="header-bar">
        <h1>La Consolacion College Manila</h1>
        <p>A Tradition of Quality Catholic Augustinian Education</p>
    </div>

    <div class="container">
        <h2 class="page-title" id="dynamic-title">PORTAL LOGIN</h2>

        <div class="content-wrapper">
            <div class="logo-section">
                <img src="lccm-logo.png" alt="LCCM Logo">
            </div>

            <div class="login-box">
                <form id="mainForm" action="" method="POST">
                    <div class="form-group">
                        <label>USER ROLE:</label>
                        <select name="user_role" id="roleSelect" required>
                            <option value="" disabled selected>Select Role</option>
                            <option value="Visitor">Visitor</option>
                            <option value="Faculty">Faculty</option>
                        </select>
                    </div>

                    <div id="visitor-section">
                        <div class="form-group">
                            <label>FULL NAME:</label>
                            <input type="text" name="username">
                        </div>
                        <div class="form-group">
                            <label>EMAIL:</label>
                            <input type="email" name="email">
                        </div>
                        <div class="form-group">
                            <label>PURPOSE:</label>
                            <select name="purpose">
                                <option value="" disabled selected>Select Purpose</option>
                                <option value="Enroll">Enrollment</option>
                                <option value="Event">School Event</option>
                                <option value="Inquiry">General Inquiry</option>
                            </select>
                        </div>
                    </div>

                    <div id="faculty-section">
                        <div class="form-group">
                            <label>FACULTY EMAIL:</label>
                            <input type="email" name="faculty_email">
                        </div>
                        <div class="form-group">
                            <label>PASSWORD:</label>
                            <input type="password" name="password">
                        </div>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="login-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const roleSelect = document.getElementById('roleSelect');
        const visitorSection = document.getElementById('visitor-section');
        const facultySection = document.getElementById('faculty-section');
        const pageTitle = document.getElementById('dynamic-title');

        roleSelect.addEventListener('change', function() {
            if (this.value === 'Visitor') {
                visitorSection.style.display = 'block';
                facultySection.style.display = 'none';
                pageTitle.innerText = 'VISITOR REQUEST';
            } else {
                visitorSection.style.display = 'none';
                facultySection.style.display = 'block';
                pageTitle.innerText = 'FACULTY LOGIN';
            }
        });
    </script>
</body>
</html>
