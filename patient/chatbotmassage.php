<?php
// Predefined questions and answers (using keywords for matching)
$qa_pairs = [
    "hello" => "Hello! How can I assist you today?",
    "how are you" => "I'm doing great, thank you for asking! How can I help you?",
    "what is your name" => "I am your virtual assistant here to help you with anything.",
    "help" => "Sure! How can I assist you? Feel free to ask me anything.",
    "thanks" => "You're welcome! Let me know if you need further assistance.",
    "website information" => "You can find detailed information about the website in the guides section.",
    "contact" => "You can contact us through the contact page on the website.",
    "what is your purpose" => "I am here to help you find information and assist with any questions you have about the website.",
    "how can I register" => "To register on our website, go to the registration page and fill in the required details.",
    "password reset" => "If you need to reset your password, click the 'Forgot Password' link on the login page.",
    "pricing" => "For pricing details, please check our pricing page on the website.",
    "terms of service" => "You can read our terms of service by visiting the 'Terms and Conditions' page on the website.",
    "privacy policy" => "Our privacy policy is available on the 'Privacy Policy' page of the website.",
    "refund policy" => "For refund-related queries, please visit our 'Refund Policy' page.",
    "hi" => "Hi there! How can I help you today?",
    "good morning" => "Good morning! How can I assist you today?",
    "good afternoon" => "Good afternoon! What can I do for you?",
    "good evening" => "Good evening! How can I assist you?",
    "good night" => "Good night! Let me know if you need anything before you sleep.",
    "how is it going" => "Everything's going great! How can I assist you today?",
    "what is the age restriction" => "There is no specific age restriction. However, if you're under 18, parental consent may be required for certain services.",
    "how accurate you are" => "Sorry if you get wrong answers.",
    "i am unable to submit my problem on both private and public problems" => "This problem occurs because you have not paid the registration fee for the particular hospital. You can check free and paid registration hospitals in the hospital list.",
    "how exactly submit public problem works" => "When you submit your problem as public, it means everyone in the hospital you chose during submission will see your problem, problem description, and current medication.",
    "is my history shared" => "Yes, your history will be shared with all hospitals where you are registered if you submit your problem as public. This helps doctors provide solutions based on your past history.",
    "is my data shared when I submit a private problem" => "No, your data will not be shared. We securely maintain your data while protecting your privacy, when you submit yourr problem privatly.",
    "is my data shared with all doctors in the hospital when I submit a private problem" => "No, the data is shared only between you and the selected doctor. No one else can see it, ensuring high privacy.",
    "how to fill submit public problem form" => "The submit public problem form includes: 1. Problem (e.g., cough, asthma, dust allergy, etc.), 2. Problem description (details about your issue), 3. Current medication (treatments you're taking), 4. Option to upload a video, and 5. Get AI solutions.",
    "what are the benefits of this website" => "This website reduces your registration time compared to waiting in a hospital for physical registration.",
    "what should we say when we go to the hospital" => "Simply tell the receptionist your email ID. They will verify if you are registered.",
    "how to consult a doctor" => "You can consult a doctor by submitting your problem as a private problem.",
    "how much time will it take to get a solution or response to my problem" => "The response time varies depending on the hospital and the doctor's availability and responsibilities.",
    "what is the name of the person who created this website" => "Milan created this website.",
    "what is the website name" => "The website is called Electronic Health Record System.",
    "what if I get the wrong answer from a fake doctor" => "This is unlikely as we register doctors with high validation and confirmation from the hospital.",
    "what if I get wrong answers from AI" => "You can ask AI for small problems, and we have integrated OpenAI's highly capable model.",
    "is AI solution free" => "Yes, AI solutions are free. Just fill in the problem description and click on 'Get AI Solutions'.",
    "can I register for multiple hospitals" => "Yes, you can register for multiple hospitals. Go to the login page, click on 'Patient', and select the hospital you want to register for.",
    "what if I choose a wrong hospital that does not exist" => "You must verify physically if the hospital exists. If you find any anonymous entries, contact us, and we will resolve the issue.",
    "i have a problem" => "If you have a problem, please click on 'Submit Private' or 'Submit Public Problems'.",
    "i want a quick solution" => "You can get a quick solution by using AI or purchasing VIP status to be prioritized.",
    "fuck sex cock bloody" => "Please refrain from using inappropriate language.",
    "i have a very big problem, what should I do" => "It is better to approach a doctor physically for severe problems.",
    "what is the difference between private and public problems" => "Private problems are shared only with your selected doctor, while public problems are visible to all doctors in the chosen hospital.",
    "how secure is my data on this website" => "We use encryption and privacy measures to ensure your data is secure and accessible only to authorized personnel.",
    "can I delete my problem after submitting it" => "No, once a problem is submitted, it cannot be deleted. However, you can add additional information if needed.",
    "what is VIP status" => "VIP status gives you priority in consultations and faster responses to your problems."
];

// Function to calculate the similarity between the user's message and stored questions
function getSimilarityScore($str1, $str2) {
    // Tokenize the strings (split into words)
    $tokens1 = explode(' ', strtolower($str1));
    $tokens2 = explode(' ', strtolower($str2));

    // Calculate the number of common words between the two strings
    $commonWords = array_intersect($tokens1, $tokens2);
    $similarityScore = count($commonWords);

    return $similarityScore;
}

// Get the message from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($data['message']) ? $data['message'] : '';

// Return messages as a JSON response
header('Content-Type: application/json');

// If the message is empty
if (empty($userMessage)) {
    echo json_encode(["response" => "I'm sorry, I didn't understand your question. Please ask something else."]);
    exit();
}

// Check for the best matching predefined question
$response = "I'm sorry, I didn't understand your question.";
$bestMatch = null;
$bestScore = 0;

foreach ($qa_pairs as $key => $answer) {
    $score = getSimilarityScore($userMessage, $key);
    
    // If the similarity score is above a threshold, consider it a match
    if ($score > $bestScore) {
        $bestScore = $score;
        $bestMatch = $answer;
    }
}

// If a match was found, return the response
if ($bestScore > 0) {
    $response = $bestMatch;
}

echo json_encode(["response" => $response]);
?>
