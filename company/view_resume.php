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
// Check if job ID is set in the URL
if (!isset($_GET['id'])) {
    die("User ID is not provided.");
}
$user_id = $_GET['id'];
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
$queryExp = "SELECT job_title, company_name, starting_time, leaving_time, image, achievement FROM experience WHERE user_id = ?";
$stmtExp = $conn->prepare($queryExp);

if (!$stmtExp) {
    die("SQL Error3: " . $conn->error);
}

$stmtExp->bind_param('s', $user_id);
$stmtExp->execute();
$experience = $stmtExp->get_result();

// Fetch education details
$queryEdu = "SELECT degree, university_name, starting_time, leaving_time, image, achievement FROM education WHERE user_id = ?";
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
    .action-container {
        display: flex;
        justify-content: flex-end;
        padding-right: 20px;
    }

    .go-back-button {
        font-size: 1em;
        font-family: Georgia;
        font-weight: normal;
        padding: 6px 12px;
        background-color: rgb(77, 77, 236);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .go-back-button:hover {
        background-color: #0056b3;
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

    .education-achievement-list {
        list-style-type: disc;
        /* Use bullets */
        margin: 10px 0;
        /* Add some margin */
        padding-left: 20px;
        /* Indent the list */
    }

    .dashboard-section {
        background-color: #f0f0f0;
        /* Light grey background */
        padding: 40px 0;
        padding-top: 30px;
    }

    /* Dashboard container styling */
    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        /* Centers the container horizontally */
        /* Ensure padding aligns with content */
    }

    /* Other existing styles */
    .document-listings {
        display: flex;
        flex-direction: column;
        /* Stack job listings vertically */
        justify-content: flex-start;
        gap: 20px;
        /* Space between job listings */
    }

    .job {
        display: flex;
        width: 100%;
        /* Full width for each job listing */
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #ccc;
        border-radius: 5px;
        cursor: pointer;
        background-color: #f9f9f9;
        min-height: 160px;
    }

    .image-gallery {
        display: flex;
        flex-wrap: wrap;
    }

    .image-item {
        margin: 10px;
    }

    .thumbnail {
        width: 100px;
        /* Thumbnail size */
        height: 100px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .thumbnail:hover {
        transform: scale(1.1);
    }

    .enlarged {
        width: auto;
        height: auto;
        max-width: 90%;
        max-height: 90%;
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

    function enlargeImage(img) {
        // Create a new div to overlay the enlarged image
        var overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = 9999;

        // Create an enlarged image
        var enlargedImg = document.createElement('img');
        enlargedImg.src = img.src;
        enlargedImg.classList.add('enlarged');

        // Add the enlarged image to the overlay
        overlay.appendChild(enlargedImg);

        // Add a click event to close the overlay
        overlay.onclick = function() {
            document.body.removeChild(overlay);
        };

        // Add the overlay to the body
        document.body.appendChild(overlay);
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
                        <a href="dashboard.php">Home</a> | <a href="postjob.php">Post Job</a>
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
                <div class="action-container">
                    <button onclick="window.history.back()" class="go-back-button">Go Back</button>
                </div>
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
                        <p><?php echo htmlspecialchars($cv['about']); ?></p>
                        <h3>Skills</h3>
                        <hr class="horizontal-line">
                        <ul>
                            <?php foreach (json_decode($cv['skills'], true) as $skill): ?>
                            <li><?php echo htmlspecialchars($skill); ?></li>
                            <?php endforeach; ?>
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

                        <hr class="horizontal-line">
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
                        ?>

                        <?php endwhile; ?>
                    </div>
                </div>
                <section class="dashboard-section" style="background-color:white;">
                    <div class="dashboard-container" style="margin: 20px; display:block; margin-top: 0px;">
                        <div class="document-listings">
                            <h3>Other documents</h3>
                            <div class="job">
                                <?php
                                    // Fetch education images (assuming the image paths are stored in 'image' column)
                                    $educationQuery = "SELECT image FROM education WHERE user_id = '$user_id'";
                                    $educationResult = mysqli_query($conn, $educationQuery);

                                    // Fetch experience images (assuming the image paths are stored in 'image' column)
                                    $experienceQuery = "SELECT image FROM experience WHERE user_id = '$user_id'";
                                    $experienceResult = mysqli_query($conn, $experienceQuery);
                                ?>
                                <!-- Display Education Images -->
                                <?php while ($row = mysqli_fetch_assoc($educationResult)): ?>
                                <div class="image-item">
                                    <img src="<?php echo $row['image']; ?>" alt="Education Document" class="thumbnail"
                                        onclick="enlargeImage(this)">
                                </div>
                                <?php endwhile; ?>

                                <!-- Display Experience Images -->
                                <?php while ($row = mysqli_fetch_assoc($experienceResult)): ?>
                                <div class="image-item">
                                    <img src="<?php echo $row['image']; ?>" alt="Experience Document" class="thumbnail"
                                        onclick="enlargeImage(this)">
                                </div>
                                <?php endwhile; ?>

                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </section>
    <?php include '../Assets/footer.php';?>
</body>

</html>