<?php

use App\Kernel;

// Check if the vendor directory exists
if (!file_exists(__DIR__ . '/../vendor/autoload_runtime.php')) {
    // Generate an installation token and store it in the session
    $installationToken = bin2hex(random_bytes(16));
    $_SESSION['allowed_to_access_required'] = true;

    // Redirect to required.php with the session identifier
    header('Location: required.php');
    exit;
}

require_once __DIR__ . '/../vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool)$context['APP_DEBUG']);
};
