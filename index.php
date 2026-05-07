<?php
// 1. START SESSION
session_start();

// ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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

    if ($role === "Faculty") {
        // FIX 1: trim() removes invisible spaces that might be added during copy-paste
        $email = trim(mysqli_real_escape_string($conn, $_POST['faculty_email']));
        $submitted_pass = trim($_POST['password']); 

        $query = "SELECT * FROM faculty WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // FIX 2: Check for ANY column casing variation (just in case Railway reverted it)
            $db_hash = $user['password'] ?? $user['Password'] ?? null;

            if ($db_hash) {
                // SUCCESS CHECK
                if (password_verify($submitted_pass, $db_hash)) {
                    $_SESSION['faculty_id'] = $user['faculty_id'];
                    $_SESSION['faculty_name'] = $user['email'];
                    
                    header("Location: faculty_dashboard.php");
                    exit();
                } else {
                    // IF IT STILL FAILS, we print the raw length to catch hidden characters
                    echo "<div style='color:white; background:red; padding:10px;'>";
                    echo "ERROR: Password Mismatch.<br>";
                    echo "Typed Length: " . strlen($submitted_pass) . "<br>";
                    echo "DB Hash Length: " . strlen($db_hash) . "<br>";
                    echo "Hash starts with: " . substr($db_hash, 0, 4) . " (Should be \$2y\$ or \$2a\$)";
                    echo "</div>";
                }
            } else {
                echo "<script>alert('Error: Could not find the password column in your database table.');</script>";
            }
        } else {
            echo "<script>alert('No account found for: " . htmlspecialchars($email) . "');</script>";
        }

    } elseif ($role === "Visitor") {
        // ... (Visitor logic remains the same)
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
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: serif; }
        body { height: 100vh; overflow: hidden; position: relative; background-color: #000; }
        body::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0.2; z-index: -1; background-image: url('campus-bg.jpg'); 
            background-position: center; background-repeat: no-repeat; background-size: cover;
        }
        .header-bar { background-color: #1a428a; color: white; text-align: center; padding: 10px 0; border-bottom: 2px solid white; position: relative; z-index: 1; }
        .header-bar h1 { font-size: 1.2rem; text-transform: uppercase; }
        .header-bar p { font-size: 0.8rem; font-style: italic; }
        .container { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 80vh; position: relative; z-index: 1; }
        .page-title { color: white; font-size: 2.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); margin-bottom: 30px; letter-spacing: 2px; }
        .content-wrapper { display: flex; align-items: center; gap: 50px; max-width: 1000px; }
        .logo-section img { width: 350px; }
        .login-box { background-color: rgba(26, 66, 138, 0.9); padding: 40px; border-radius: 10px; width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        .form-group { display: flex; align-items: center; margin-bottom: 20px; position: relative; }
        .form-group label { color: white; width: 150px; font-size: 0.85rem; font-weight: bold; }
        .form-group input, .form-group select { flex: 1; padding: 8px; border: none; outline: none; background-color: white; }
        
        /* Style for the "Show Password" area */
        .show-pass-wrapper { margin-left: 150px; margin-bottom: 20px; color: white; font-size: 0.75rem; display: flex; align-items: center; gap: 5px; cursor: pointer; }
        .show-pass-wrapper input { width: auto; flex: none; cursor: pointer; }

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
                            <!-- Added an ID to this input so JavaScript can find it -->
                            <input type="password" name="password" id="facultyPass">
                        </div>
                        <!-- SHOW PASSWORD OPTION -->
                        <div class="show-pass-wrapper">
                            <input type="checkbox" id="togglePass">
                            <label for="togglePass" style="width: auto; cursor: pointer;">Show Password</label>
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

        // Logic to switch between Visitor and Faculty forms
        roleSelect.addEventListener('change', function() {
            if (this.value === 'Visitor') {
                visitorSection.style.display = 'block';
                facultySection.style.display = 'none';
                pageTitle.innerText = 'VISITOR REQUEST';
            } else if (this.value === 'Faculty') {
                visitorSection.style.display = 'none';
                facultySection.style.display = 'block';
                pageTitle.innerText = 'FACULTY LOGIN';
            }
        });

        // NEW: Logic to Show/Hide Password
        const togglePass = document.getElementById('togglePass');
        const facultyPass = document.getElementById('facultyPass');

        togglePass.addEventListener('change', function() {
            if (this.checked) {
                facultyPass.type = 'text'; // Unhide
            } else {
                facultyPass.type = 'password'; // Hide
            }
        });
    </script>

</body>
</html>
