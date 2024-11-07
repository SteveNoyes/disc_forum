<?php

// call this filterUnwantedWords() function on user inputs before saving them to the database or displaying them. This approach keeps user interactions friendly for your intended audience.

function filterUnwantedWords($input) {
    // Define an array of unwanted words
    $unwantedWords = ['badword1', 'badword2', 'badword3']; // Add any words you want to filter
    $replacement = '****'; // Replacement text for unwanted words

    // Check and replace unwanted words
    foreach ($unwantedWords as $word) {
        $pattern = '/\b' . preg_quote($word, '/') . '\b/i'; // Word boundary to match the exact word
        $input = preg_replace($pattern, $replacement, $input);
    }

    return $input;
}

// Usage example
$userInput = "This is a test with badword1.";
$filteredInput = filterUnwantedWords($userInput);
echo $filteredInput; // Output will replace unwanted words

?>