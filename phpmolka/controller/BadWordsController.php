<?php

class BadWordsController {
    private $badWords = [
        'fuck', 'shit', 'ass', 'bitch', 'bastard', 'damn', 'cunt', 'dick', 'pussy', 'cock',
        'whore', 'slut', 'piss', 'nigger', 'faggot', 'retard', 'asshole', 'motherfucker',
        'bullshit', 'fag', 'nazi', 'penis', 'vagina', 'boob', 'hell'
        // Add more bad words as needed
    ];

    public function containsBadWords($text) {
        // Convert text to lowercase for case-insensitive matching
        $text = strtolower($text);
        
        // Create an array to store found bad words
        $foundBadWords = [];
        
        foreach ($this->badWords as $word) {
            // Check if the word exists in the text
            if (strpos($text, $word) !== false) {
                $foundBadWords[] = $word;
            }
        }
        
        return [
            'hasBadWords' => !empty($foundBadWords),
            'badWords' => $foundBadWords
        ];
    }
} 