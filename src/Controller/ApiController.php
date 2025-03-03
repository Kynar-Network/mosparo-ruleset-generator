<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Service\TempDirectoryService;

#[Route('/api/guest', name: 'guest_')]
class ApiController extends AbstractController
{
    private $params;
    private $csrfTokenManager;
    private $tempDirectoryService;

    public function __construct(ParameterBagInterface $params, CsrfTokenManagerInterface $csrfTokenManager, TempDirectoryService $tempDirectoryService)
    {
        $this->params = $params;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->tempDirectoryService = $tempDirectoryService;
    }

    #[Route('/ruleset_generate', name: 'ruleset_generate', methods: ['POST'])]
    public function rulesetGenerate(Request $request): JsonResponse
    {
        try {
            $csrfToken = $request->request->get('csrf-token');
            $token = new CsrfToken($this->params->get('app.csrf_key_generate'), $csrfToken);
            if (!$this->csrfTokenManager->isTokenValid($token)) {
                return new JsonResponse(['error' => 'Invalid CSRF token.'], 400);
            }

            // Handle file upload
            $file = $request->files->get('fileInput');
            if (!$file) {
                return new JsonResponse(['error' => 'No file uploaded.'], 400);
            }

            $tempDirectory = $this->tempDirectoryService->getTempDirectory();
            $fileName = Uuid::uuid4()->toString();
            $filePath = sprintf('%s/%s.txt', $tempDirectory, $fileName);

            try {
                $file->move($tempDirectory, $fileName . '.txt');
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Error saving file: ' . $e->getMessage()], 500);
            }

            $allowedRuleTypes = ['email', 'ipAddress', 'unicodeBlock', 'word', 'domain', 'provider', 'user-agent', 'website'];
            $ruleType = $request->request->get('ruleType');

            if (!in_array($ruleType, $allowedRuleTypes)) {
                return new JsonResponse(['error' => 'Invalid ruleset type provided.'], 404);
            }

            // Store necessary data for processing
            $settings = [
                'ruleName' => $request->request->get('ruleName', 'Kynar Network Ruleset'),
                'description' => $request->request->get('description', 'Kynar Network Ruleset Generator for mosparo.'),
                'ruleType' => $request->request->get('ruleType', 'email'),
                'spamRatingFactor' => (float)$request->request->get('spamRating', 0),
                'fileName' => $request->request->get('fileName', 'ruleset'),
            ];

            // Handle JSON file upload if present
            $jsonFile = $request->files->get('jsonFileInput');
            if ($jsonFile) {
                try {
                    $jsonFilePath = sprintf('%s/%s.json', $tempDirectory, $fileName);
                    $jsonFile->move($tempDirectory, $fileName . '.json');

                    // Load existing JSON data
                    $jsonData = json_decode(file_get_contents($jsonFilePath), true);
                    $settings['jsonData'] = $jsonData;
                } catch (FileException $e) {
                    return new JsonResponse(['error' => 'Error saving JSON file: ' . $e->getMessage()], 500);
                }
            }

            // Store settings in session or a database
            $this->storeSettings($fileName, $settings);

            return new JsonResponse(['processId' => $fileName], 200);
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            error_log('Error generating ruleset: ' . $e->getMessage());

            // Return a generic error message
            return new JsonResponse(['error' => 'An unexpected error occurred. Please try again later.'], 500);
        }
    }


    private function storeSettings(string $processId, array $settings): void
    {
        $settingsPath = sprintf('%s/%s.settings.json', $this->tempDirectoryService->getTempDirectory(), $processId);
        file_put_contents($settingsPath, json_encode($settings));
    }

    #[Route('/ruleset_progress_stream/{processId}', name: 'ruleset_progress_stream')]
    public function rulesetProgressStream(string $processId)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable buffering in Nginx

        ob_start();

        $tempDirectory = $this->tempDirectoryService->getTempDirectory();
        $filePath = sprintf('%s/%s.txt', $tempDirectory, $processId);
        $settingsPath = sprintf('%s/%s.settings.json', $tempDirectory, $processId);

        if (!file_exists($filePath) || !file_exists($settingsPath)) {
            echo "data: " . json_encode(['message' => 'Process not found.', 'isError' => true]) . "\n\n";
            ob_flush();
            flush();
            return new Response('Process not found.');
        }

        // Load settings
        $settings = json_decode(file_get_contents($settingsPath), true);

        // Load existing data or initialize new one
        $jsonFilePath = sprintf('%s/%s.json', $tempDirectory, $processId);
        if (file_exists($jsonFilePath)) {
            $existingData = json_decode(file_get_contents($jsonFilePath), true);

            // Find the existing rules of the selected type and merge items
            $items = [];
            foreach ($existingData['rules'] as &$rule) {
                if ($rule['type'] === $settings['ruleType']) {
                    $jsonItems = json_encode($rule['items']);
                    $items = json_decode($jsonItems, true);
                    break;
                }
            }
        } else {
            $existingData = [
                'lastUpdatedAt' => date('Y-m-d H:i:s'),
                'refreshInterval' => 3600,
                'rules' => []
            ];
            $items = [];
        }

        // Find the existing rule to update
        $ruleToUpdate = null;
        foreach ($existingData['rules'] as &$rule) {
            if ($rule['type'] === $settings['ruleType']) {
                $ruleToUpdate = &$rule;
                break;
            }
        }

        // Store original values for comparison
        $originalRuleName = $ruleToUpdate['name'] ?? null;
        $originalDescription = $ruleToUpdate['description'] ?? null;
        $originalSpamRatingFactor = $ruleToUpdate['spamRatingFactor'] ?? null;

        usleep(100000); // 0.1 seconds delay for demonstration purposes

        // Open the file to count total lines
        $fileHandle = fopen($filePath, "r");
        if (!$fileHandle) {
            echo "data: " . json_encode(['message' => 'Error opening file.', 'isError' => true]) . "\n\n";
            ob_flush();
            flush();
            fclose($fileHandle);
            return new Response('Error opening file.');
        }

        $totalLines = 0;
        while (($line = fgets($fileHandle)) !== false) {
            if (!empty(trim($line))) {
                $totalLines++;
            }
        }
        fclose($fileHandle);

        // Open the file again to process it
        $fileHandle = fopen($filePath, "r");
        if (!$fileHandle) {
            echo "data: " . json_encode(['message' => 'Error opening file.', 'isError' => true]) . "\n\n";
            ob_flush();
            flush();
            fclose($fileHandle);
            return new Response('Error opening file.');
        }

        $processedLines = 0;

        // Check if a rule with the same type already exists
        $ruleFound = false;
        foreach ($existingData['rules'] as &$rule) {
            if ($rule['type'] === $settings['ruleType']) {
                $rule['items'] = $items;
                $ruleFound = true;

                // Update rule name, description, and spam rating factor
                if ($settings['ruleName'] !== $originalRuleName) {
                    $rule['name'] = $settings['ruleName'];
                    echo "data: " . json_encode([
                        'message' => "Rule Name updated from <b><i>" . $originalRuleName . "</i></b> to <b><u>" . $settings['ruleName'] . "</u></b>.",
                        'isChanged' => true
                    ]) . "\n\n";
                }

                if ($settings['description'] !== $originalDescription) {
                    $rule['description'] = $settings['description'];
                    echo "data: " . json_encode([
                        'message' => "Rule Description updated from <b><i>" . $originalDescription . "</i></b> to <b><u>" . $settings['description'] . "</u></b>.",
                        'isChanged' => true
                    ]) . "\n\n";
                }

                if ($settings['spamRatingFactor'] !== $originalSpamRatingFactor) {
                    $rule['spamRatingFactor'] = $settings['spamRatingFactor'];
                    echo "data: " . json_encode([
                        'message' => "Spam Rating Factor updated from <b><i>" . $originalSpamRatingFactor . "</i></b> to <b><u>" . $settings['spamRatingFactor'] . "</u></b>.",
                        'isChanged' => true
                    ]) . "\n\n";
                }

                break;
            }
        }

        // If no rule with the same type was found, add a new one
        if (!$ruleFound) {
            $existingData['rules'][] = [
                'name' => $settings['ruleName'],
                'description' => $settings['description'],
                'type' => $settings['ruleType'],
                'items' => $items,
                'spamRatingFactor' => $settings['spamRatingFactor'],
                'uuid' => Uuid::uuid4()->toString()
            ];
        }

        while (($line = fgets($fileHandle)) !== false) {
            $processedLines++;
            if (empty(trim($line))) {
                continue;
            }

            // Process each line
            $data = str_getcsv($line, ",", '"', "\\");
            if (count($data) !== 3) {
                echo "data: " . json_encode([
                    'message' => "Invalid line format: $line",
                    'isError' => true
                ]) . "\n\n";
                continue;
            }

            [$value, $rating, $lastSeen] = $data;

            // Check if the value already exists in the items array
            $found = false;
            foreach ($items as &$item) {
                if ($item['value'] === $value) {
                    if ((int)$rating > $item['rating']) {
                        $item['rating'] = (int)$rating;
                        echo "data: " . json_encode([
                            'message' => "Updated entry: $value with new rating: $rating",
                            'isChanged' => true
                        ]) . "\n\n";
                    } elseif ((int)$rating === $item['rating']) {
                        echo "data: " . json_encode([
                            'message' => "Skipped entry: $value (same rating)",
                            'isSkipped' => true
                        ]) . "\n\n";
                    }
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $items[] = [
                    'uuid' => Uuid::uuid4()->toString(),
                    'type' => $settings['ruleType'],
                    'value' => $value,
                    'rating' => (int)$rating
                ];
                echo "data: " . json_encode([
                    'message' => "Added new entry: $value with rating: $rating",
                    'isAdded' => true
                ]) . "\n\n";
            }

            // Send progress update
            $progress = ($processedLines / $totalLines) * 100;
            echo "data: " . json_encode(['progress' => round($progress, 2), 'message' => "Processing line " . ($processedLines) . " of $totalLines. (" . round($progress, 2) . "%)",]) . "\n\n";

            ob_flush();
            flush();

            // Simulate processing delay
            usleep(100000); // 0.1 seconds delay for demonstration purposes
        }
        fclose($fileHandle);

        // Check if a rule with the same type already exists
        $ruleFound = false;
        foreach ($existingData['rules'] as &$rule) {
            if ($rule['type'] === $settings['ruleType']) {
                $rule['items'] = $items;
                $ruleFound = true;
                break;
            }
        }

        // If no rule with the same type was found, add a new one
        if (!$ruleFound) {
            $existingData['rules'][] = [
                'name' => $settings['ruleName'],
                'description' => $settings['description'],
                'type' => $settings['ruleType'],
                'items' => $items,
                'spamRatingFactor' => $settings['spamRatingFactor'],
                'uuid' => Uuid::uuid4()->toString()
            ];
        }

        // Sort rules by type and items
        usort($existingData['rules'], function ($a, $b) {
            return strcmp($a['type'], $b['type']);
        });

        foreach ($existingData['rules'] as &$rule) {
            usort($rule['items'], function ($a, $b) {
                return strcmp($a['value'], $b['value']);
            });
        }

        // Update lastUpdatedAt
        $existingData['lastUpdatedAt'] = date('Y-m-d H:i:s');

        // Write JSON data to file
        try {
            file_put_contents($jsonFilePath, json_encode($existingData, JSON_PRETTY_PRINT));
        } catch (FileException $e) {
            echo "data: " . json_encode([
                'error' => "Error writing JSON file: " . $e->getMessage(),
            ]) . "\n\n";
        }

        // Generate SHA256 hash
        $sha256FilePath = sprintf('%s/%s.json.sha256', $tempDirectory, $processId);
        try {
            $hash = hash_file('sha256', $jsonFilePath);
            file_put_contents($sha256FilePath, $hash);
        } catch (FileException $e) {
            echo "data: " . json_encode([
                'error' => "Error writing SHA256 file: " . $e->getMessage(),
            ]) . "\n\n";
        }

        // Send final progress update
        ob_flush();
        flush();

        return new Response('Progressbar completed.');
    }


    #[Route("/ruleset-download/{processID}/{type}", name: "ruleset_download")]
    public function downloadRuleset(string $processID, int $type): Response
    {
        $tempDirectory = $this->tempDirectoryService->getTempDirectory();
        $settingsPath = sprintf('%s/%s.settings.json', $tempDirectory, $processID);

        if (!file_exists($settingsPath)) {
            echo "data: " . json_encode(['message' => 'Process not found.', 'isError' => true]) . "\n\n";
            ob_flush();
            flush();
        }

        // Load settings
        $settings = json_decode(file_get_contents($settingsPath), true);

        // Load existing data or initialize new one
        $jsonFilePath = sprintf('%s/%s.json', $tempDirectory, $processID);
        if (file_exists($jsonFilePath)) {
            $existingData = json_decode(file_get_contents($jsonFilePath), true);
        } else {
            return new JsonResponse(['error' => 'JSON file not found.'], 404);
        }

        if ($type === 1) {
            $response = new Response(json_encode($existingData, JSON_PRETTY_PRINT));
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $settings['fileName'] . '.json"');
        } else {
            $sha256FilePath = sprintf('%s/%s.json.sha256', $tempDirectory, $processID);
            if (!file_exists($sha256FilePath)) {
                return new JsonResponse(['message' => 'SHA256 file not found.', 'isError' => true], 404);
            }

            $response = new Response(file_get_contents($sha256FilePath));
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $settings['fileName'] . '.json.sha256"');
        }

        return $response;
    }

    #[Route('/validate_json', name: 'validate_json', methods: ['POST'])]
    public function validateJson(Request $request): JsonResponse
    {
        // Handle file upload
        $file = $request->files->get('jsonFileInput');
        if (!$file) {
            return new JsonResponse(['valid' => false, 'message' => 'No JSON file uploaded.'], 400);
        }

        try {
            $content = file_get_contents($file->getPathname());
            $jsonData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['valid' => false, 'message' => 'Invalid JSON format.'], 400);
            }

            // Validate the structure
            $requiredKeys = ['lastUpdatedAt', 'refreshInterval', 'rules'];
            foreach ($requiredKeys as $key) {
                if (!isset($jsonData[$key])) {
                    return new JsonResponse(['valid' => false, 'message' => "Missing key: $key."], 400);
                }
            }

            if (!is_array($jsonData['rules'])) {
                return new JsonResponse(['valid' => false, 'message' => '"rules" should be an array.'], 400);
            }

            foreach ($jsonData['rules'] as $rule) {
                $requiredRuleKeys = ['name', 'description', 'type', 'items', 'spamRatingFactor', 'uuid'];
                foreach ($requiredRuleKeys as $key) {
                    if (!isset($rule[$key])) {
                        return new JsonResponse(['valid' => false, 'message' => "Missing key in rule: $key."], 400);
                    }
                }

                if (!is_array($rule['items'])) {
                    return new JsonResponse(['valid' => false, 'message' => '"items" should be an array.'], 400);
                }

                foreach ($rule['items'] as $item) {
                    $requiredItemKeys = ['uuid', 'type', 'value', 'rating'];
                    foreach ($requiredItemKeys as $key) {
                        if (!isset($item[$key])) {
                            return new JsonResponse(['valid' => false, 'message' => "Missing key in item: $key."], 400);
                        }
                    }
                }
            }

            // Check if there are more than 1 rule
            if (count($jsonData['rules']) > 1) {
                return new JsonResponse(['valid' => true, 'data' => $jsonData, 'multipleRules' => true]);
            }

            return new JsonResponse(['valid' => true, 'data' => $jsonData]);
        } catch (\Exception $e) {
            return new JsonResponse(['valid' => false, 'message' => 'Error reading JSON file: ' . $e->getMessage()], 500);
        }
    }
}
