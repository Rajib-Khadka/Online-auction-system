<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobPortal | login</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <?php include 'Assets/header.php';?>
    <section class="login-section">
        <div class="login-box">
            <div class="box">
                <i class="fas fa-user-circle icon"></i>
                <h3>Login as a User</h3>
                <p>Click here to login as a user and access your account.</p>
                <a href="Logins/jobseekersignup.php" class="btn btn-primary">Login</a>
            </div>
            <div class="box">
                <i class="fas fa-building icon"></i>
                <h3>Login as a Company</h3>
                <p>Click here to login as a company and manage your job postings.</p>
                <a href="Logins/companysignup.php" class="btn btn-primary">Login</a>
            </div>
        </div>
    </section>
    <?php include 'Assets/footer.php'; ?>

</body>

</html>