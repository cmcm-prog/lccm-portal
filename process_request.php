<?php
// ... (Database connection code here) ...

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = $_GET['id'];
    $action = $_GET['action']; // This will be 'Approved' or 'Rejected'

    // 1. Get the visitor's email first
    $email_query = "SELECT email, name FROM visitors WHERE id = '$id'";
    $res = mysqli_query($conn, $email_query);
    $visitor = mysqli_fetch_assoc($res);
    $to_email = $visitor['email'];
    $visitor_name = $visitor['name'];

    // 2. Update the status in the database
    $update_sql = "UPDATE visitors SET status = '$action' WHERE id = '$id'";
    
    if (mysqli_query($conn, $update_sql)) {
        // 3. SEND THE EMAIL
        $subject = "Update on your LCCM Portal Request";
        $message = "Hi $visitor_name,\n\nYour request to visit LCCM has been $action.";
        $headers = "From: no-reply@lccm.edu.ph";

        if (mail($to_email, $subject, $message, $headers)) {
            echo "Status updated and email sent!";
        } else {
            echo "Status updated, but email failed to send.";
        }
    }
}
?>
