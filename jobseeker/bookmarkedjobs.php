<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

require('../Config/dbconnect.php'); // Adjust the path as per your file structure

$user_id = $_SESSION['user_id'];

// Initialize error message
$error_message = '';

// Fetch saved job IDs for the logged-in user
$sql = "SELECT job_id FROM saved_jobs WHERE user_id = ? ORDER BY saved_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$job_ids = [];
while ($row = $result->fetch_assoc()) {
    $job_ids[] = $row['job_id'];
}

// If there are no saved jobs, set an error message
if (empty($job_ids)) {
    $error_message = 'You have no bookmarked jobs.';
    $job_listings = [];
} else {
    // Create a string of job IDs for the IN clause
    $job_ids_string = implode(',', array_map('intval', $job_ids));

    // Fetch job details
    $sql_jobs = "SELECT id, title, job_desc, salary_from, salary_to, type, company_name FROM jobs WHERE id IN ($job_ids_string)";
    $stmt_jobs = $conn->prepare($sql_jobs);
    $stmt_jobs->execute();
    $job_listings = $stmt_jobs->get_result();

    // Initialize an array to store job listings
    $job_listings_data = []; 

    while ($job = $job_listings->fetch_assoc()) {
        $company_name = $job['company_name'];
        if (!isset($companies[$company_name])) {
            $sql_company = "SELECT name, logo, goggle_url, location, country FROM company WHERE name = ?";
            $stmt_company = $conn->prepare($sql_company);
            $stmt_company->bind_param('s', $company_name);
            $stmt_company->execute();
            $company_result = $stmt_company->get_result();
            $companies[$company_name] = $company_result->fetch_assoc();
        }
        // Add company details to the job
        $job['company_details'] = $companies[$company_name];
        // Store the job with company details in a new array
        $job_listings_data[] = $job;
    }

    // Check if job listings data is empty
    if (empty($job_listings_data)) {
        $error_message = 'No job details found for your bookmarked jobs.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <link rel="stylesheet" href="../css/Navigation.css">
    <link rel="stylesheet" href="../css/postjob.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    .banner-section {
        position: relative;
        background-image: url('http://localhost/JobPortal/Banners/banner4.png');
        background-size: cover;
        background-position: center;
        height: 300px;
        display: flex;
        align-items: center;
    }

    .job-listings {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        margin-top: 20px;
    }

    .job {
        display: flex;
        width: 100%;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #ccc;
        border-radius: 5px;
        cursor: pointer;
        background-color: #f9f9f9;
        min-height: 160px;
    }

    .company-logo {
        width: 70px;
        height: auto;
        margin-right: 25px;
    }

    .job-title {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 16px;
    }

    .job-type {
        background-color: blue;
        color: white;
        border: none;
        padding: 7px 12px;
        border-radius: 5px;
        margin-left: 15px;
    }

    .divider {
        border: none;
        height: 1px;
        background-color: #ccc;
        margin: 10px 0;
    }

    .job-info {
        display: flex;
        justify-content: space-between;
        gap: 1px;
        margin-top: 15px;
    }

    .info-box {
        background-color: rgba(11, 49, 213, 0.1);
        width: 30%;
        font-size: 14px;
        color: #333;
        padding: 4px;
        color: rgba(11, 49, 213, 0.8);
    }

    .error-message {
        color: red;
        font-weight: bold;
        margin: 20px 0;
    }
    </style>
    <script>
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        const links = document.querySelectorAll('.nav-links a');
        links.forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    });

    function showForm(formId) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = section.id === formId ? 'block' : 'none';
        });
    }
    </script>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo">
                <a href="http://localhost/JobPortal/jobseeker/dashboard.php"><img
                        src="http://localhost/JobPortal/Logo/logo3.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="category.php">Category</a></li>
                <li><a href="job.php">Jobs</a></li>
                <li>
                    <?php 
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
                    <h2 class="banner-heading">Bookmarked Jobs</h2>
                    <p class="banner-paragraph">View your saved job listings.</p>
                    <div class="breadcrumb">
                        <a href="dashboard.php">Home</a> | <a href="bookmarkedjobs.php">Bookmarked Jobs</a>
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
                <div class="nav-item" onclick="showForm('bookmarkedjobs')"><i class="fas fa-heart"></i> Bookmarked Jobs
                </div>
                <div class="nav-item" onclick="location.href='appliedjobs.php'"><i class="fas fa-briefcase"></i> Applied
                    Jobs</div>

                <div class="nav-item" onclick="location.href='message.php'"><i class="fas fa-comments"></i> Messages
                </div>

                <div class="nav-item" onclick="location.href='setting.php'"><i class="fas fa-cog"></i> Settings</div>
                <div class="nav-item" onclick="location.href='../Logins/logout.php'"><i class="fas fa-sign-out-alt"></i>
                    Sign Out</div>
            </div>
            <div class="dashboard-content">
                <h3>Bookmarked Jobs</h3>
                <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
                <?php else: ?>
                <div class="job-listings">
                    <?php foreach ($job_listings_data as $job): ?>
                    <div class="job" onclick="window.location.href='job_details.php?id=<?php echo $job['id']; ?>'">
                        <div class="img-logo">
                            <?php
                            if (isset($job['company_details'])) {
                            echo '<img src="../uploads/' . htmlspecialchars($job['company_details']['logo']) . '"
                                alt="Company Logo" class="company-logo">';
                            } else {
                            echo '<img src="../uploads/default_logo.png" alt="Default Logo" class="company-logo">'; //Placeholder logo
                            }
                            ?></div>
                        <div class="job-details">
                            <div class="job-heading" style="display: flex;">
                                <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                                <button class="job-type"><?php echo htmlspecialchars($job['type']); ?></button>
                            </div>
                            <div class="divider"></div>
                            <p class="job-description"><?php echo substr($job['job_desc'], 0, 150)?>...</p>

                            <div class="job-info">
                                <div class="info-box">Company: <?php echo htmlspecialchars($job['company_name']); ?>
                                </div>
                                <div class="info-box">Salary:
                                    <?php echo htmlspecialchars($job['salary_from']) . ' - ' . htmlspecialchars($job['salary_to']); ?>
                                </div>
                                <div class="info-box">Location:
                                    <?php echo htmlspecialchars($job['company_details']['location']); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php'; ?>
</body>

</html>