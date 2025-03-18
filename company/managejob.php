<?php
require('../Config/dbconnect.php'); // Adjust the path as per your file structure

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

// Check if session variable is set
if (!isset($_SESSION['company_name'])) {
echo 'Error: Company name not found in session.';
exit();
}

// Query to fetch posted jobs
$jobsQuery = "SELECT id, title, type, shift, salary_from, salary_to, expiry_date FROM jobs WHERE company_name = ?";
$stmt = $conn->prepare($jobsQuery);

if (!$stmt) {
echo 'Error preparing statement: ' . $conn->error;
exit();
}

$stmt->bind_param("s", $_SESSION['company_name']);
$stmt->execute();
$jobsResult = $stmt->get_result();

if (!$jobsResult) {
echo 'Error executing query: ' . $stmt->error;
exit();
}

$jobs = $jobsResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Manage job</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/postjob.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    /* Table styling */
    table {
        width: 100%;
        border-collapse: collapse;
        /* Ensures there are no gaps between borders */
        font-size: 18px;
        text-align: left;
    }

    /* Table header styles */
    table thead th {
        font-weight: bold;
        padding: 10px;
        border-bottom: 2px solid #ddd;
        /* Border only at the bottom of the header */
    }

    /* Table body styles */
    table tbody tr {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        /* Shaded border for each row */
    }

    table tbody tr:nth-of-type(even) {
        background-color: #f9f9f9;
    }

    table tbody tr:hover {
        background-color: #f1f1f1;
    }

    table tbody td {
        padding: 15px;
        border-right: none;
        /* No column borders */
        font-size: 0.95em;
    }

    /* Shift values styling */
    .shift {
        color: #007bff;

    }

    /* Action icons */
    .action-icon {
        color: #1850f7;
        text-decoration: none;
        margin-right: 10px;
        font-size: 0.90em;
    }

    .action-icon:hover {
        color: #1900ff;
    }

    /* Container for the table */
    #manage-job {
        margin: 20px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #fff;
        padding: 20px;
        /* Added padding for better spacing */
    }

    #manage-job h2 {
        margin-bottom: 20px;
    }

    .message,
    .error-message {
        color: white;
        font-size: 1.3em;
        font-weight: 400;
        margin-bottom: 20px;
        position: relative;
        padding: 0px;
        border-radius: 5px;
        background-color: #3498db;
        text-align: center;
    }

    .error-message {
        color: red;
    }

    .message .close-button,
    .error-message .close-button {
        position: absolute;
        top: 5px;
        right: 10px;
        cursor: pointer;
        font-size: 1.3em;
        color: white;
        background: transparent;
        border: none;
    }
    </style>
    <script>
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        // Show the 'Post a Job' form by default
        showForm('manage-job');
    });


    function showForm(formId) {
        console.log('Showing form:', formId); // Debugging line
        document.querySelectorAll('.content-section').forEach(section => {
            if (section.id === formId) {
                section.style.display = 'block'; // Show the selected section
            } else {
                section.style.display = 'none'; // Hide all other sections
            }
        });
    }
    </script>

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
                        <a href="dashboard.php">Home</a> | <a href="managejob.php">Manage Job</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">

            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="location.href='postjob.php'"><i class="fas fa-plus"></i> Post a Job</div>
                <div class="nav-item" onclick="showForm('manage-job')"><i class="fas fa-tasks"></i> Manage Job
                </div>
                <div class="nav-item" onclick="location.href='message.php'"><i class=" fas fa-comments"></i>
                    Messages
                </div>
                <div class="nav-item" onclick="location.href='setting.php'"><i class=" fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <div class="dashboard-content">
                <?php
                    
                    // Display success or error messages if available
                    if (isset($_SESSION['job_post_success'])) {
                        echo '<div class="message" style="padding: 10px;">';
                       echo $_SESSION['job_post_success'];
                       echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                       echo '</div>';
                       unset($_SESSION['job_post_success']);
                   }

                   if (isset($_SESSION['job_post_error'])) {
                    echo '<div class="error-message" style="padding: 10px;">';
                    echo $_SESSION['job_post_error'];
                       echo '<button class="close-button" style="margin: 0px; padding: 10px;" onclick="this.parentElement.style.display=\'none\'">&times;</button>';
                       echo '</div>';
                       unset($_SESSION['job_post_error']);
                   }
                   ?>
                <div id="manage-job" class="content-section">
                    <h2>Manage Jobs</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Type</th>
                                <th>Shift</th>
                                <th>Salary</th>
                                <th>Status</th> <!-- New column for job status -->
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($jobs)) : ?>
                            <tr>
                                <td colspan="5">No jobs found.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($jobs as $job) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['title']); ?></td>
                                <td><?php echo htmlspecialchars($job['type']); ?></td>
                                <td><span class="shift"><?php echo htmlspecialchars($job['shift']); ?></span></td>
                                <td>Rs
                                    <?php echo htmlspecialchars($job['salary_from']) . ' - ' . htmlspecialchars($job['salary_to']); ?>
                                </td>
                                <td>
                                    <?php 
                                    // Check the deadline to determine if the job is opened or closed
                                    $currentDate = date('Y-m-d');
                                    $isClosed = strtotime($job['expiry_date']) < strtotime($currentDate); 
                                    ?>
                                    <button
                                        style="background-color:cornflowerblue; color: white; font-size: 15px; padding: 2px; width: 60px;"
                                        class="status-button <?php echo $isClosed ? 'closed' : 'open'; ?>" disabled>
                                        <?php echo $isClosed ? 'Closed' : 'Open'; ?>
                                    </button>
                                </td>
                                <td>
                                    <a href="viewjob.php?id=<?php echo $job['id']; ?>" class="action-icon"><i
                                            class="fas fa-eye"></i></a>
                                    <a href="editjob.php?id=<?php echo $job['id']; ?>" class="action-icon"><i
                                            class="fas fa-edit"></i></a>
                                    <a href="deletejob.php?id=<?php echo $job['id']; ?>" class="action-icon"><i
                                            class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>