<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Logins/jobseekerlogin.php");
    exit();
}

require('../Config/dbconnect.php'); // Adjust the path as per your file structure
$user_id = $_SESSION['user_id'];

$query = "SELECT name, pp, Phone_no, email, address, linkedin_url, language FROM user WHERE userid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the row as a numeric array
$row = $result->fetch_row();

// Get the values from the array
$name = $row[0]; // Name
$pp = $row[1]; // Name
$phone_no = $row[2]; // Phone Number
$email = $row[3]; // Email
$address = $row[4]; // Address
$linkedin_url = $row[5]; // LinkedIn URL
$language = $row[6];

// Fetch CV details
$queryCv = "SELECT about, skills FROM cv WHERE user_id = ?";
$stmtCv = $conn->prepare($queryCv);

if (!$stmtCv) {
    die("SQL Error2: " . $conn->error);
}

$stmtCv->bind_param('s', $user_id);
$stmtCv->execute();
$cv = $stmtCv->get_result()->fetch_assoc();

// Fetch experience details
$queryExp = "SELECT job_title, company_name, starting_time, leaving_time, achievement FROM experience WHERE user_id = ?";
$stmtExp = $conn->prepare($queryExp);

if (!$stmtExp) {
    die("SQL Error3: " . $conn->error);
}

$stmtExp->bind_param('s', $user_id);
$stmtExp->execute();
$experience = $stmtExp->get_result();

// Fetch education details
$queryEdu = "SELECT degree, university_name, starting_time, leaving_time, achievement FROM education WHERE user_id = ?";
$stmtEdu = $conn->prepare($queryEdu);

if (!$stmtEdu) {
    die("SQL Error4: " . $conn->error);
}

$stmtEdu->bind_param('s', $user_id);
$stmtEdu->execute();
$education = $stmtEdu->get_result();
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

    .resume-container {
        display: flex;
        margin: 20px;
    }

    .left-side {
        width: 40%;
        background-color: #f0f0f0;
        /* Light grey shade */
        padding: 20px;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .right-side {
        width: 60%;
        padding: 20px;
    }

    .horizontal-line {
        border: 0;
        height: 1px;
        background: #ccc;
        margin: 10px 0;
    }

    h2,
    h3 {
        margin: 10px 0;
    }

    ul {
        list-style-type: disc;
        padding-left: 20px;
    }


    .photo {
        width: 100%;
        height: auto;
        max-width: 150px;
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
    // JavaScript to handle active link state
    document.addEventListener('DOMContentLoaded', () => {
        // Show the 'Post a Job' form by default
        showForm('myresume');
    });

    function closeMessage() {
        document.querySelector('.success-message').style.display = 'none';
    }

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
                <a href="http://localhost/JobPortal/jobseeker/dashboard.php"><img
                        src="http://localhost/JobPortal/Logo/logo3.png" alt="Logo"></a>
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
                        <a href="dashboard.php">Home</a> | <a href="profile.php">My Resume</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-container">

            <div class="dashboard-nav">
                <h3>Manage Account</h3>
                <div class="nav-item" onclick="showForm('myresume')"><i class="fas fa-file-alt"></i> My Resume
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
                <div class="resume-container">
                    <div class="left-side">
                        <h2><?php echo htmlspecialchars($name); ?></h2>
                        <div class="parent-container">

                            <img src="data:image/jpeg;base64,<?php echo base64_encode($pp); ?>" alt="User Photo"
                                class="photo" onerror="this.onerror=null; this.src='userprofile.png';">
                        </div>
                        <hr class="horizontal-line">
                        <h3>Personnel Details</h3>
                        <hr class="horizontal-line">

                        <div>
                            <p style="font-size: 15px; font-weight:700;">Address:</p>
                            <p><?php echo htmlspecialchars($address ?? 'Address not provided'); ?></p>
                            <br>

                            <p style="font-size: 15px; font-weight:700;">Phone_no:</p>
                            <p><?php echo htmlspecialchars($phone_no ?? 'Phone number not provided'); ?></p>
                            <br>

                            <p style="font-size: 15px; font-weight:700;">Email:</p>
                            <p><?php echo htmlspecialchars($email ?? 'Email not provided'); ?></p><br>

                            <p style="font-size: 15px; font-weight:700;">LinkedIn:</p>
                            <p><?php echo htmlspecialchars($linkedin_url ?? 'LinkedIn url not provided'); ?></p>

                            <br>
                            </p>

                        </div>
                        <hr class="horizontal-line">
                        <h3>Languages</h3>
                        <ul>
                            <?php 
                            if (!empty($language)) {
                                foreach (explode(',', $language) as $language): ?>
                            <li><?php echo htmlspecialchars(trim($language)); ?></li>
                            <?php endforeach; 
                            } else {
                                echo '<li>No languages specified</li>';
                            }
                            ?>
                        </ul>

                    </div>
                    <div class="right-side">
                        <h2>Summary</h2>
                        <hr class="horizontal-line">
                        <p><?php echo htmlspecialchars($cv['about'] ?? 'No summary provided'); ?></p>
                        <h3>Skills</h3>
                        <hr class="horizontal-line">
                        <ul>
                            <?php 
                        if (!empty($cv['skills'])) {
                            foreach (json_decode($cv['skills'], true) as $skill): ?>
                            <li><?php echo htmlspecialchars($skill); ?></li>
                            <?php endforeach; 
                        } else {
                            echo '<li>No skills specified</li>';
                        }
                        ?>
                        </ul>

                        <h3>Experience</h3>
                        <hr class="horizontal-line">
                        <?php while ($exp = $experience->fetch_assoc()): ?>
                        <p style="font-size: large; font-weight: 600;">
                            <?php echo htmlspecialchars($exp['job_title']); ?>
                        </p>
                        <p style="font-size: 16px; font-weight: 400;">
                            <?php echo htmlspecialchars($exp['company_name']); ?></p>
                        <p style="font-size: 16px; font-weight: 400;">
                            <?php echo htmlspecialchars($exp['starting_time']) . ' to ' . htmlspecialchars($exp['leaving_time']); ?>
                        </p> <br>
                        <?php
                            // Assuming $exp['achievement'] contains newline-separated achievements
                            $achievements = explode("\n", $exp['achievement']); // Split the string into an array using new lines

                            echo '<ul class="achievement-list">'; // Start an unordered list
                            foreach ($achievements as $achievement) {
                                echo '<li>' . htmlspecialchars(trim($achievement)) . '</li>'; // Trim spaces and display each achievement as a list item
                            }
                            echo '</ul>'; // End the unordered list
                            ?>
                        <?php endwhile; ?>

                        <h3>Education</h3>
                        <hr class="horizontal-line">
                        <?php while ($edu = $education->fetch_assoc()): ?>
                        <p style="font-size: large; font-weight: 600;"><?php echo htmlspecialchars($edu['degree']); ?>
                        </p>
                        <p style="font-size: 16px; font-weight: 400;">
                            <?php echo htmlspecialchars($edu['university_name']); ?></p>
                        <p style="font-size: 16px; font-weight: 400;">
                            <?php echo htmlspecialchars($edu['starting_time']) . ' to ' . htmlspecialchars($edu['leaving_time']); ?>
                        </p> <br>
                        <?php
                        // Assuming $edu['achievement'] contains newline-separated achievements
                        $achievements = explode("\n", $edu['achievement']); // Split the achievements into an array based on new lines

                        echo '<ul class="achievement-list">'; // Start an unordered list
                        foreach ($achievements as $achievement) {
                            echo '<li>' . htmlspecialchars(trim($achievement)) . '</li>'; // Trim spaces and display each achievement as a list item
                        }
                        echo '</ul>'; // End the unordered list
                        ?><?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>