<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['company_name'])) {
    header("Location: ../Logins/companylogin.php");
    exit();
}
// Get company name from session
$company_name = mysqli_real_escape_string($conn, $_SESSION['company_name']);

// Fetch company ID from the database using the company name
$companyQuery = "SELECT companyid FROM company WHERE name = '$company_name'";
$companyResult = mysqli_query($conn, $companyQuery);

// Check if the query was successful and fetch the company ID
if ($companyResult && mysqli_num_rows($companyResult) > 0) {
    $companyRow = mysqli_fetch_assoc($companyResult);
    $company_id = $companyRow['companyid'];
} else {
    // Handle the case where the company is not found
    echo "Company not found.";
    exit();
}
// Fetch all job seekers the company has exchanged messages with
$chatHeadsQuery = "
    SELECT u.userid, u.name, 
           MAX(m.timestamp) AS last_message_time,
           SUM(CASE WHEN m.is_read = 0 AND m.receiver_id = '$company_id' THEN 1 ELSE 0 END) AS unread_count
    FROM user u
    LEFT JOIN messages m ON m.receiver_id = u.userid OR m.sender_id = u.userid
    WHERE (m.sender_id = '$company_id' OR m.receiver_id = '$company_id')
    GROUP BY u.userid, u.name
    ORDER BY last_message_time DESC
";
$chatHeadsResult = mysqli_query($conn, $chatHeadsQuery);


// Get the job seeker ID from the URL
if (isset($_GET['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
} else {
    // Fetch the last job seeker with messages
    $latestJobSeekerQuery = "
        SELECT DISTINCT sender_id AS user_id
        FROM messages
        WHERE receiver_id = '$company_id'
        ORDER BY timestamp DESC
        LIMIT 1
    ";
    $latestJobSeekerResult = mysqli_query($conn, $latestJobSeekerQuery);
    $latestJobSeeker = mysqli_fetch_assoc($latestJobSeekerResult);
    
    $user_id = $latestJobSeeker ? $latestJobSeeker['user_id'] : null;
}

// Fetch job seeker details if a valid job seeker ID is found
if ($user_id) {
    $jobSeekerQuery = "SELECT name FROM user WHERE userid = '$user_id'";
    $jobSeekerResult = mysqli_query($conn, $jobSeekerQuery);
    $jobSeeker = mysqli_fetch_assoc($jobSeekerResult);
    if (!$jobSeeker) {
        echo "Job seeker not found.";
        exit();
    }

    // Fetch the chat messages between company and job seeker
    $messagesQuery = "SELECT * FROM messages WHERE 
                      (sender_id = '$company_id' AND receiver_id = '$user_id') OR
                      (sender_id = '$user_id' AND receiver_id = '$company_id') 
                      ORDER BY timestamp ASC";
    $messagesResult = mysqli_query($conn, $messagesQuery);
    // Update is_read for messages in the current chat
    $updateReadStatusQuery = "
    UPDATE messages 
    SET is_read = 1 
    WHERE receiver_id = '$user_id' 
    AND sender_id = '$company_id' 
    AND is_read = 0
    ";
    mysqli_query($conn, $updateReadStatusQuery);

} else {
    echo "No messages found.";
    exit();
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Insert the new message into the database
    $insertMessageQuery = "INSERT INTO messages (sender_id, receiver_id, message, timestamp, is_read) 
                           VALUES ('$company_id', '$user_id', '$message', NOW(), 0)";
    mysqli_query($conn, $insertMessageQuery);
    
    // Refresh to show the new message
    header("Location: message.php?user_id=" . urlencode($user_id));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Messages</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/postjob.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    .message-container {
        width: 70%;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 10px;
        background-color: #f9f9f9;
    }

    .chat-head {
        text-align: center;
        margin-bottom: 20px;
    }

    .chat-history {
        max-height: 400px;
        overflow-y: scroll;
        border-bottom: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 20px;
        height: 250px;

    }

    .chat-message {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }

    .company-message {
        background-color: #d4f4ff;
        text-align: right;
    }

    .user-message {
        background-color: #f4d4d4;
        text-align: left;
    }

    .message-form {
        display: flex;
        justify-content: space-between;
    }

    textarea {
        width: 80%;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    button {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .chat-heads {
        width: 40%;
        border-right: 1px solid #ccc;
        padding: 20px;
        background-color: #f0f4ff;
        /* Light blue background */
        border-radius: 10px;
        /* Rounded corners */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        /* Subtle shadow */
    }

    .chat-heads h4 {
        margin-bottom: 20px;
        font-size: 1.5em;
        /* Larger header */
        color: #333;
        /* Darker text color */
        border-bottom: 2px solid #007bff;
        /* Underline effect */
        padding-bottom: 10px;
        /* Space below header */
    }

    .chat-list {
        list-style-type: none;
        /* Remove default list styling */
        padding: 0;
        /* Remove default padding */
    }

    .chat-item {
        margin-bottom: 15px;
        /* Space between items */
    }

    .chat-link {
        display: block;
        /* Make link block-level for full clickable area */
        padding: 10px;
        /* Add padding for comfort */
        border-radius: 5px;
        /* Rounded corners */
        background-color: #fff;
        /* White background for links */
        color: #007bff;
        /* Link color */
        text-decoration: none;
        /* Remove underline */
        transition: background-color 0.3s, color 0.3s;
        /* Smooth transition */
    }

    .chat-link:hover {
        background-color: #e7f3ff;
        /* Light blue background on hover */
        color: #0056b3;
        /* Darker text color on hover */
    }

    .chat-heads p {
        color: #999;
        /* Grey color for the "No chat history" message */
        text-align: center;
        /* Center align the message */
        margin-top: 20px;
        /* Space above the message */
    }

    .chat-item {
        margin-bottom: 10px;
    }

    .chat-link {
        text-decoration: none;
        color: #333;
    }

    .chat-link.unread {
        font-weight: bold;
        /* Make the unread chat head bold */
    }

    .unread-count {
        color: #007bff;
        /* Blue color for the unread count */
        margin-left: 5px;
        /* Space between the name and count */
        font-weight: normal;
        /* Regular weight for count */
    }

    .last-message-time {
        display: block;
        font-size: 0.9em;
        color: #666;
        margin-top: 5px;
    }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="#"><img src="../logo/logo3.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="postjob.php" class="search-button"
                        style="font-size: 1em; color: white; font-family: Georgia; font-weight: normal; padding: 6px 12px;">Post
                        a
                        Job</a>
                </li>
            </ul>
        </div>
    </nav>

    <section class="banner-section">
        <div class="banner-overlay">
            <div class="banner-container">
                <div class="banner-content">
                    <h2 class="banner-heading">Post Job</h2>
                    <p class="banner-paragraph">Business plan draws on a wide range of knowledge from different business
                        <br>
                        disciplines. Business draws on a wide range of different business.
                    </p>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a> | <a href="message.php">Message</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">
            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="location.href='postjob.php'"><i class="fas fa-plus"></i> Post a Job
                </div>
                <div class="nav-item" onclick="location.href='managejob.php'"><i class="fas fa-tasks"></i> Manage Job
                </div>
                <div class="nav-item" onclick="location.href='message.php'"><i class=" fas fa-comments"></i>
                    Messages
                </div>
                <div class="nav-item" onclick="location.href='setting.php'"><i class=" fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>

            <div class="dashboard-content">
                <div class="message-container">
                    <div class="chat-head">
                        <h2>Chat with <?php echo htmlspecialchars($jobSeeker['name']); ?></h2>
                    </div>
                    <div style="display: flex;">
                        <!-- Chat heads section (left sidebar) -->
                        <div class="chat-heads" style="width: 30%; border-right: 1px solid #ccc; padding: 10px;">
                            <h4>Chats</h4>
                            <?php if (mysqli_num_rows($chatHeadsResult) > 0): ?>
                            <ul class="chat-list">
                                <?php while ($chatHead = mysqli_fetch_assoc($chatHeadsResult)): ?>
                                <?php
                // Check if there are any unread messages from this user
                $user_id = $chatHead['userid'];
                $unreadQuery = "
                    SELECT COUNT(*) AS unread_count 
                    FROM messages 
                    WHERE sender_id = '$user_id' 
                    AND receiver_id = '$company_id' 
                    AND is_read = 0
                ";
                $unreadResult = mysqli_query($conn, $unreadQuery);
                $unreadData = mysqli_fetch_assoc($unreadResult);
                $unreadCount = $unreadData['unread_count'];

                // Apply bold styling if there are unread messages
                $chatHeadStyle = $unreadCount > 0 ? 'font-weight: bold;' : '';
                ?>
                                <li class="chat-item">
                                    <a href="message.php?user_id=<?php echo $user_id; ?>" class="chat-link"
                                        style="<?php echo $chatHeadStyle; ?>">
                                        <?php echo htmlspecialchars($chatHead['name']); ?>
                                        <?php if ($unreadCount > 0): ?>
                                        <span style="color: red; font-weight: bold;">+<?php echo $unreadCount; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                            <?php else: ?>
                            <p>No chat history.</p>
                            <?php endif; ?>
                        </div>


                        <!-- Chat history section (right side) -->
                        <div style="width: 60%; padding: 10px; display: flex; flex-direction: column;">
                            <div class="chat-history">
                                <?php if ($messagesResult && mysqli_num_rows($messagesResult) > 0): ?>
                                <?php while ($message = mysqli_fetch_assoc($messagesResult)): ?>
                                <div
                                    class="chat-message <?php echo ($message['sender_id'] === $company_id) ? 'company-message' : 'user-message'; ?>">
                                    <strong><?php echo ($message['sender_id'] === $company_id) ? 'You' : htmlspecialchars($jobSeeker['name']); ?>:</strong>
                                    <p><?php echo htmlspecialchars($message['message']); ?></p>
                                    <small
                                        class="last-message-time"><?php echo date('Y-m-d H:i', strtotime($message['timestamp'])); ?></small>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <p>No messages yet.</p>
                                <?php endif; ?>
                            </div>

                            <form action="" method="POST" class="message-form">
                                <textarea name="message" rows="3" placeholder="Type your message..."></textarea>
                                <button type="submit">Send</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../Assets/footer.php';?>
    <script>
    // Optional: Auto-scroll to the bottom of chat history
    const chatHistory = document.querySelector('.chat-history');
    if (chatHistory) {
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
    </script>
</body>

</html>