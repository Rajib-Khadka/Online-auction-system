<?php
require('../Config/dbconnect.php');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

// Get user ID from session
$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// Fetch all companies the user has exchanged messages with
$chatHeadsQuery = "
    SELECT c.companyid, c.name, 
           MAX(m.timestamp) AS last_message_time,
           SUM(CASE WHEN m.is_read = 0 AND m.receiver_id = '$user_id' THEN 1 ELSE 0 END) AS unread_count
    FROM company c
    LEFT JOIN messages m ON m.receiver_id = c.companyid OR m.sender_id = c.companyid
    WHERE (m.sender_id = '$user_id' OR m.receiver_id = '$user_id')
    GROUP BY c.companyid, c.name
    ORDER BY last_message_time DESC
";
$chatHeadsResult = mysqli_query($conn, $chatHeadsQuery);


// Get the company ID from the URL
// Get the company ID from the URL or fetch the latest company
if (isset($_GET['company_id'])) {
    $company_id = mysqli_real_escape_string($conn, $_GET['company_id']);
} else {
    // Fetch the last company with messages
    $latestCompanyQuery = "
        SELECT DISTINCT receiver_id AS company_id
        FROM messages
        WHERE sender_id = '$user_id'
        ORDER BY timestamp DESC
        LIMIT 1
    ";
    $latestCompanyResult = mysqli_query($conn, $latestCompanyQuery);
    $latestCompany = mysqli_fetch_assoc($latestCompanyResult);
    
    // Set company_id if found
    $company_id = $latestCompany ? $latestCompany['company_id'] : null;
}

// Fetch the company details if a valid company ID is found
if ($company_id) {
    $companyQuery = "SELECT name FROM company WHERE companyid = '$company_id'";
    $companyResult = mysqli_query($conn, $companyQuery);
    $company = mysqli_fetch_assoc($companyResult);
    if (!$company) {
        echo "Company not found.";
        exit();
    }

    // Fetch the chat messages between user and company
    $messagesQuery = "SELECT * FROM messages WHERE 
                      (sender_id = '$user_id' AND receiver_id = '$company_id') OR
                      (sender_id = '$company_id' AND receiver_id = '$user_id') 
                      ORDER BY timestamp ASC";
    $messagesResult = mysqli_query($conn, $messagesQuery);
     // Update is_read for messages in the current chat
     $updateReadStatusQuery = "
     UPDATE messages 
     SET is_read = 1 
     WHERE receiver_id = '$company_id' 
     AND sender_id = '$user_id' 
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
                           VALUES ('$user_id', '$company_id', '$message', NOW(), 0)";
    mysqli_query($conn, $insertMessageQuery);
    
    // Refresh to show the new message
    header("Location: message.php?company_id=" . urlencode($company_id));
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Messages</title>
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

    .user-message {
        background-color: #d4f4ff;
        text-align: right;
    }

    .company-message {
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
                <a href="http://localhost/JobPortal/index.php"><img src="http://localhost/JobPortal/Logo/logo3.png"
                        alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="category.php">Category</a></li>
                <li><a href="job.php">Jobs</a></li>
                <li>
                    <?php $user_id = $_SESSION['user_id'];
                        $query = "SELECT name FROM user WHERE userid = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param('s', $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                        $user_name = htmlspecialchars($user['name']);
                    ?>
                    <a href="profile.php" class="username">
                        <i class="fa fa-lock" aria-hidden="true" style="padding-left: 0px; font-size: 14px;"></i>
                        <?php echo $user_name; ?>
                    </a>
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
                        <a href="dashboard.php">Home</a> | <a href="message.php">Messages</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">
            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="location.href='profile.php'"><i class="fas fa-file-alt"></i> My Resume
                </div>
                <div class="nav-item" onclick="location.href='bookmarkedjobs.php'"><i class="fas fa-heart"></i>
                    Bookmarked Jobs</div>
                <div class="nav-item" onclick="location.href='appliedjobs.php'"><i class="fas fa-briefcase"></i> Applied
                    Jobs</div>

                <div class="nav-item" onclick="location.href='message.php'"><i class="fas fa-comments"></i> Messages
                </div>

                <div class="nav-item" onclick="location.href='setting.php'"><i class="fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>

            <div class="dashboard-content">
                <div class="message-container">
                    <div class="chat-head">
                        <h2>Chat with <?php echo htmlspecialchars($company['name']); ?></h2>
                    </div>
                    <div style="display: flex;">
                        <!-- Chat heads section (left sidebar) -->
                        <div class="chat-heads">
                            <h4>Chats</h4>
                            <?php if (mysqli_num_rows($chatHeadsResult) > 0): ?>
                            <ul class="chat-list">
                                <?php while ($chatHead = mysqli_fetch_assoc($chatHeadsResult)): ?>
                                <li class="chat-item">
                                    <a href="message.php?company_id=<?php echo $chatHead['companyid']; ?>"
                                        class="chat-link <?php echo ($chatHead['unread_count'] > 0) ? 'unread' : ''; ?>">
                                        <?php echo htmlspecialchars($chatHead['name']); ?>
                                        <?php if ($chatHead['unread_count'] > 0): ?>
                                        <span class="unread-count">(+<?php echo $chatHead['unread_count']; ?>)</span>
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
                                <?php if (mysqli_num_rows($messagesResult) > 0): ?>
                                <?php while ($message = mysqli_fetch_assoc($messagesResult)): ?>
                                <div
                                    class="chat-message <?php echo $message['sender_id'] == $user_id ? 'user-message' : 'company-message'; ?>">
                                    <strong><?php echo $message['sender_id'] == $user_id ? 'You' : htmlspecialchars($company['name']); ?>:</strong>

                                    <p><?php echo htmlspecialchars($message['message']); ?></p>
                                    <span><?php echo date('M d, Y h:i A', strtotime($message['timestamp'])); ?></span>
                                </div>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <p>No messages yet. Start a conversation!</p>
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