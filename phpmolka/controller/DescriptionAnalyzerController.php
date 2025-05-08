<?php

class DescriptionAnalyzerController {
    // Catégories de réclamations
    private $categories = [
        'technique' => [
            'bug', 'error', 'crash', 'broken', 'not working', 'issue', 'problem',
            'glitch', 'malfunction', 'failure', 'down', 'slow', 'loading'
        ],
        'service' => [
            'service', 'support', 'assistance', 'help', 'customer service',
            'response', 'contact', 'unavailable', 'waiting'
        ],
        'account' => [
            'account', 'login', 'password', 'access', 'profile', 'settings',
            'registration', 'sign up', 'sign in', 'logout'
        ],
        'billing' => [
            'payment', 'bill', 'charge', 'refund', 'price', 'cost',
            'subscription', 'invoice', 'billing', 'money'
        ],
        'feature' => [
            'feature', 'functionality', 'option', 'setting', 'preference',
            'request', 'suggestion', 'improvement', 'upgrade'
        ]
    ];

    // Niveaux d'urgence
    private $urgencyKeywords = [
        'high' => [
            'urgent', 'emergency', 'asap', 'immediately', 'critical',
            'serious', 'important', 'priority', 'urgent', 'now'
        ],
        'medium' => [
            'soon', 'needed', 'please help', 'issue', 'problem',
            'not working', 'fix', 'broken'
        ],
        'low' => [
            'when possible', 'suggestion', 'feedback', 'would like',
            'consider', 'maybe', 'think about'
        ]
    ];

    // Sentiment des mots
    private $sentimentKeywords = [
        'negative' => [
            'angry', 'frustrated', 'disappointed', 'upset', 'terrible',
            'horrible', 'bad', 'worst', 'never', 'awful', 'poor'
        ],
        'neutral' => [
            'okay', 'fine', 'normal', 'average', 'standard',
            'regular', 'usual', 'common'
        ],
        'positive' => [
            'good', 'great', 'excellent', 'amazing', 'wonderful',
            'best', 'perfect', 'love', 'appreciate', 'thanks'
        ]
    ];

    /**
     * Analyse une description de réclamation
     * @param string $description La description à analyser
     * @return array Les résultats de l'analyse
     */
    public function analyzeDescription($description) {
        $description = strtolower($description);
        
        $analysis = [
            'category' => $this->detectCategory($description),
            'urgency' => $this->detectUrgency($description),
            'sentiment' => $this->detectSentiment($description),
            'length' => strlen($description),
            'wordCount' => str_word_count($description),
            'containsQuestion' => $this->containsQuestion($description)
        ];

        return $analysis;
    }

    /**
     * Détecte la catégorie principale de la réclamation
     */
    private function detectCategory($description) {
        $maxScore = 0;
        $mainCategory = 'general';

        foreach ($this->categories as $category => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $score++;
                }
            }
            if ($score > $maxScore) {
                $maxScore = $score;
                $mainCategory = $category;
            }
        }

        return $mainCategory;
    }

    /**
     * Détecte le niveau d'urgence de la réclamation
     */
    private function detectUrgency($description) {
        $scores = [
            'high' => 0,
            'medium' => 0,
            'low' => 0
        ];

        foreach ($this->urgencyKeywords as $level => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $scores[$level]++;
                }
            }
        }

        arsort($scores);
        return array_key_first($scores);
    }

    /**
     * Détecte le sentiment général de la réclamation
     */
    private function detectSentiment($description) {
        $scores = [
            'negative' => 0,
            'neutral' => 0,
            'positive' => 0
        ];

        foreach ($this->sentimentKeywords as $sentiment => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $scores[$sentiment]++;
                }
            }
        }

        arsort($scores);
        return array_key_first($scores);
    }

    /**
     * Vérifie si la description contient une question
     */
    private function containsQuestion($description) {
        return (
            strpos($description, '?') !== false ||
            preg_match('/\b(what|when|where|who|why|how)\b/i', $description)
        );
    }
} 