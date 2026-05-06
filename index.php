<?php
// 1. DATABASE CONNECTION SETTINGS (From image_aed67e.png)
$servername = "trolley.proxy.rlwy.net";
$port       = 55915; 
$username   = "root";
$password   = "nAhfmxSpwiwRmvwGfihndXTOUEVjInjC"; // Click 'show' in Railway to get this
$dbname     = "railway";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. PHP logic to handle routing after submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['user_role'] ?? '';

    if ($role === "Faculty") {
        $email = mysqli_real_escape_string($conn, $_POST['faculty_email']);
        $pass  = $_POST['password'];

        // Real Database Check: Looking for the faculty in the table we created
        $query = "SELECT * FROM faculty WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            header("Location: faculty_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Faculty record not found!');</script>";
        }

    } elseif ($role === "Visitor") {
        $name    = mysqli_real_escape_string($conn, $_POST['username']);
        $email   = mysqli_real_escape_string($conn, $_POST['email']);
        $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);

        // Save to Visitors Table
        $sql = "INSERT INTO visitors (name, contact_number, address) VALUES ('$name', '$email', '$purpose')";
        
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Visitor Registration Successful!');</script>";
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

    * {

        margin: 0;

        padding: 0;

        box-sizing: border-box;

        font-family: serif;

    }



    body {

        height: 100vh;

        overflow: hidden;

        position: relative;

        background-color: #000;

    }



    body::before {

        content: "";

        position: absolute;

        top: 0; left: 0;

        width: 100%; height: 100%;

        opacity: 0.2;

        z-index: -1;

        background-image: url('campus-bg.jpg'); 

        background-position: center;

        background-repeat: no-repeat;

        background-size: cover;

        transition: background-image 0.5s ease-in-out;

    }



    .header-bar {

        background-color: #1a428a;

        color: white;

        text-align: center;

        padding: 10px 0;

        border-bottom: 2px solid white;

        position: relative;

        z-index: 1;

    }



    .header-bar h1 { font-size: 1.2rem; text-transform: uppercase; }

    .header-bar p { font-size: 0.8rem; font-style: italic; }



    .container {

        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        height: 80vh;

        position: relative;

        z-index: 1;

    }



    .page-title {

        color: white;

        font-size: 2.5rem;

        text-shadow: 2px 2px 4px rgba(0,0,0,0.8);

        margin-bottom: 30px;

        letter-spacing: 2px;

    }



    .content-wrapper {

        display: flex;

        align-items: center;

        gap: 50px;

        max-width: 1000px;

    }



    .logo-section img { width: 350px; }



    .login-box {

        background-color: rgba(26, 66, 138, 0.9);

        padding: 40px;

        border-radius: 10px;

        width: 450px;

        box-shadow: 0 10px 25px rgba(0,0,0,0.5);

    }



    .form-group {

        display: flex;

        align-items: center;

        margin-bottom: 20px;

    }



    .form-group label {

        color: white;

        width: 150px;

        font-size: 0.85rem;

        font-weight: bold;

    }



    .form-group input, .form-group select {

        flex: 1;

        padding: 8px;

        border: none;

        outline: none;

        background-color: white;

    }



    /* Sections hidden by default */

    #visitor-section, #faculty-section {

        display: none;

    }



    .btn-container {

        text-align: right;

        margin-top: 10px;

    }



    .login-btn {

        background-color: white;

        color: #1a428a;

        border: none;

        padding: 8px 30px;

        font-size: 1rem;

        font-weight: bold;

        cursor: pointer;

        text-transform: uppercase;

    }



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

                    

                    <!-- 1. Role Selection -->

                    <div class="form-group">

                        <label>USER ROLE:</label>

                        <select name="user_role" id="roleSelect" required>

                            <option value="" disabled selected>Select Role</option>

                            <option value="Visitor">Visitor</option>

                            <option value="Faculty">Faculty</option>

                        </select>

                    </div>



                    <!-- 2. Visitor Specific Fields -->

                    <div id="visitor-section">

                        <div class="form-group">

                            <label>FULL NAME:</label>

                            <input type="text" name="username" id="visitor_name">

                        </div>

                        <div class="form-group">

                            <label>EMAIL:</label>

                            <input type="email" name="email" id="visitor_email">

                        </div>

                        <div class="form-group">

                            <label>PURPOSE:</label>

                            <select name="purpose" id="visitor_purpose">

                                <option value="" disabled selected>Select Purpose</option>

                                <option value="Enroll">Enrollment</option>

                                <option value="Event">School Event</option>

                                <option value="Inquiry">General Inquiry</option>

                            </select>

                        </div>

                    </div>



                    <!-- 3. Faculty Specific Fields -->

                    <div id="faculty-section">

                        <div class="form-group">

                            <label>FACULTY EMAIL:</label>

                            <input type="email" name="faculty_email" id="faculty_email">

                        </div>

                        <div class="form-group">

                            <label>PASSWORD:</label>

                            <input type="password" name="password" id="faculty_pass">

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



        // Input elements to toggle "required" status

        const visitorInputs = visitorSection.querySelectorAll('input, select');

        const facultyInputs = facultySection.querySelectorAll('input, select');



        roleSelect.addEventListener('change', function() {

            if (this.value === 'Visitor') {

                // UI Changes

                visitorSection.style.display = 'block';

                facultySection.style.display = 'none';

                pageTitle.innerText = 'VISITOR LOGIN';

                

                // Toggle Required Attributes

                visitorInputs.forEach(input => input.required = true);

                facultyInputs.forEach(input => input.required = false);



            } else if (this.value === 'Faculty') {

                // UI Changes

                visitorSection.style.display = 'none';

                facultySection.style.display = 'block';

                pageTitle.innerText = 'FACULTY LOGIN';



                // Toggle Required Attributes

                visitorInputs.forEach(input => input.required = false);

                facultyInputs.forEach(input => input.required = true);

            }

        });

    </script>



</body>

</html>

${{ MySQL.MYSQL_URL }}
