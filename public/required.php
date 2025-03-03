<?php
session_start();

// Check if the user is allowed to access this page
if (!isset($_SESSION['allowed_to_access_required']) || !$_SESSION['allowed_to_access_required']) {
    // Redirect to the main page or show an error
    header('Location: index.php');
    exit;
}

// Reset the session variable to prevent reuse
unset($_SESSION['allowed_to_access_required']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Composer Installation Required</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #0d6efd;
            margin-bottom: 2rem;
        }
        p {
            color: #6c757d;
            line-height: 1.6;
        }
        pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9rem;
        }
        strong {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Important: Required Composer Installation</h1>
        
        <div class="row">
            <div class="col-md-12">
                <p class="lead mb-4">
                    Before you can use this Symfony project, you need to install the required dependencies using Composer. 
                    This is a crucial step to ensure all packages and libraries are properly set up for the application to function correctly.
                </p>
                
                <p>
                    Composer is a dependency management tool for PHP, and it's used to install and manage the 
                    project's required packages. The installation process will download and configure all the necessary 
                    components specified in the <strong>composer.json</strong> file.
                </p>

                <p>
                    If you skip this step, the application might not work correctly, and you may encounter errors 
                    or missing functionalities. It's essential to complete this installation process before proceeding.
                </p>

                <p>
                    The installation process is straightforward. Simply open your terminal, navigate to the project 
                    directory, and run the Composer install command. This command reads the project's requirements 
                    from the <strong>composer.json</strong> file and installs all the necessary packages.
                </p>

                <p>
                    During the installation, you'll see a progress output showing which packages are being installed. 
                    This might take a few moments depending on your internet connection and the number of packages 
                    being installed.
                </p>

                <p>
                    After the installation is complete, the application should be fully functional. You'll be able to 
                    access the application's features and functionality as intended by the developers.
                </p>

                <p>
                    If you encounter any issues during the installation process, please check your terminal output 
                    for error messages. You can also refer to the project's documentation or seek help from the 
                    development community if needed.
                </p>

                <p>
                    Finally, remember that keeping your project's dependencies up to date is important for security 
                    and functionality. Periodically running Composer update commands will help ensure you have the 
                    latest stable versions of the installed packages.
                </p>

                <p class="text-muted">
                    Thank you for choosing this Symfony project. Let's proceed with the Composer installation to 
                    get everything set up and ready to go!
                </p>

                <div class="alert alert-info mt-4" role="alert">
                    <h4 class="alert-heading mb-3">Run the Following Command:</h4>
                    <pre>
 composer install
                    </pre>
                    <p class="mb-0">
                        <strong>Open your terminal, navigate to the project directory, and run this command.</strong>
                    </p>
                </div>

                <p class="mt-4">
                    If you have successfully run the Composer installation command and all dependencies have been 
                    installed without errors, you can click the button below to proceed to the main domain.
                </p>

                <!-- Add a button to redirect to the main domain -->
                <div class="mt-4 text-center">
                    <a 
                       class="btn btn-primary btn-lg"
                       onclick="redirectWithMessage()">
                        Proceed to Main Domain
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function redirectWithMessage() {
            // Show a confirmation message before redirecting
            if (confirm('Confirm that Composer install was successful?')) {
                window.location.href = `${window.location.protocol}//${window.location.hostname}`; // Replace with your main domain
                
            }
        }
    </script>
</body>
</html>