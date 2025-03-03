<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Util\Random;

#[Route('/setup', name: 'app_setup')]
class SetupController extends AbstractController
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    #[Route('/', name: '_index')]
    public function index(): Response
    {
    $appSecret = $_SERVER['APP_SECRET'] ?? null;
$appCSRFPAGE = $_SERVER['APP_CSRF_PAGE'] ?? null;
$appCSRFKEYGEN = $_SERVER['APP_CSRF_KEY_GENERATE'] ?? null;

// Check if all variables are set and not null
if (!empty($appSecret) && !empty($appCSRFPAGE) && !empty($appCSRFKEYGEN)) {
    return $this->redirectToRoute('app_home');
}


        return $this->render('setup/index.html.twig');
    }

    #[Route('/generate', name: '_generate', methods: ['POST'])]
public function generate(): Response
{
    $envFilePath = $this->getParameter('kernel.project_dir') . '/.env';

    // Check if the .env file exists
    if (!$this->filesystem->exists($envFilePath)) {
        throw new \Exception('The .env file does not exist.');
    }

    // Read the contents of the .env file
    $content = file_get_contents($envFilePath);

    // Generate new random values
    $newAppSecret = bin2hex(random_bytes(32));
    $newCSRFPAGE = bin2hex(random_bytes(32));
    $newCSRFGEN = bin2hex(random_bytes(32));

    // Quote values if needed (e.g., for spaces)
    $newAppSecretQuoted = $this->quoteIfContainsSpaces($newAppSecret);
    $newCSRFPAGEQuoted = $this->quoteIfContainsSpaces($newCSRFPAGE);
    $newCSRFGENQuoted = $this->quoteIfContainsSpaces($newCSRFGEN);

    // Replace or append APP_SECRET
    if (preg_match('/^APP_SECRET=/m', $content)) {
        $content = preg_replace('/^APP_SECRET=.*$/m', "APP_SECRET=$newAppSecretQuoted", $content);
    } else {
        $content .= "\nAPP_SECRET=$newAppSecretQuoted";
    }

    // Replace or append APP_CSRF_PAGE
    if (preg_match('/^APP_CSRF_PAGE=/m', $content)) {
        $content = preg_replace('/^APP_CSRF_PAGE=.*$/m', "APP_CSRF_PAGE=$newCSRFPAGEQuoted", $content);
    } else {
        $content .= "\nAPP_CSRF_PAGE=$newCSRFPAGEQuoted";
    }

    // Replace or append APP_CSRF_KEY_GENERATE
    if (preg_match('/^APP_CSRF_KEY_GENERATE=/m', $content)) {
        $content = preg_replace('/^APP_CSRF_KEY_GENERATE=.*$/m', "APP_CSRF_KEY_GENERATE=$newCSRFGENQuoted", $content);
    } else {
        $content .= "\nAPP_CSRF_KEY_GENERATE=$newCSRFGENQuoted";
    }

    // Write the updated content back to the .env file
    try {
        $this->filesystem->dumpFile($envFilePath, $content);
    } catch (\Exception $e) {
        throw new \Exception('Failed to write the .env file: ' . $e->getMessage());
    }

    // Redirect to the home page
    return $this->redirectToRoute('app_home');
}


    private function quoteIfContainsSpaces(string $value): string
    {
        if (strpos($value, ' ') !== false) {
            return '"' . $value . '"';
        }
        return $value;
    }
}
