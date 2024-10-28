<?php
require 'vendor/autoload.php';

// Use the custom OpenAI class defined in your OpenAI.php
use OpenAI\Client as OpenAIClient;
use GuzzleHttp\Client as GuzzleClient;

// Replace with your actual OpenAI API key
$openai_api_key = 'please purchase api key';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['problem_description'])) {
    $problem_description = $_POST['problem_description'];

    try {
        // Create OpenAI client using the custom OpenAI class
        $client = OpenAI::client($openai_api_key);

        // Generate AI response using the new model
        $response = $client->completions()->create([
            'model' => 'gpt-3.5-turbo', // Updated model
            'prompt' => $problem_description,
            'max_tokens' => 300,
            'temperature' => 0.7,
        ]);

        // Extract AI-generated response
        $ai_solution = $response['choices'][0]['text'] ?? "AI could not generate a solution.";

        echo trim($ai_solution);
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>