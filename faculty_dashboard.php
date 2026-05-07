<?php
session_start();
// Security: If not logged in, kick them back to the login page
if (!isset($_SESSION['faculty_id'])) { 
    header("Location: index.php"); 
    exit(); 
}

$conn = mysqli_connect("mysql.railway.internal", "root", "nAhfmxSpwiwRmvwGfihndXTOUEVjInjC", "railway", 3306);

// Fetch all visitors
$result = mysqli_query($conn, "SELECT * FROM visitors ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>LCCM Faculty Dashboard</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #1a428a; color: white; }
        .Pending { color: orange; font-weight: bold; }
        .Approved { color: green; font-weight: bold; }
        .Rejected { color: red; font-weight: bold; }
        .btn { padding: 8px 12px; text-decoration: none; color: white; border-radius: 4px; font-size: 14px; }
        .approve-btn { background-color: #28a745; }
        .reject-btn { background-color: #dc3545; margin-left: 5px; }
    </style>
</head>
<body>
    <h1>Faculty Dashboard</h1>
    <p>Welcome, <?php echo $_SESSION['faculty_name']; ?> | <a href="logout.php">Logout</a></p>
    <hr>
    <table>
        <tr>
            <th>Visitor Name</th><th>Email</th><th>Purpose</th><th>Status</th><th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['purpose']; ?></td>
            <td class="<?php echo $row['status']; ?>"><?php echo $row['status']; ?></td>
            <td>
                <?php if($row['status'] == 'Pending'): ?>
                    <a href="process_request.php?id=<?php echo $row['id']; ?>&action=Approved" class="btn approve-btn">Approve</a>
                    <a href="process_request.php?id=<?php echo $row['id']; ?>&action=Rejected" class="btn reject-btn">Reject</a>
                <?php else: ?>
                    Processed
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
