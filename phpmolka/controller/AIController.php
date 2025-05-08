<?php
require_once(__DIR__ . '/DescriptionAnalyzerController.php');

class AIController {
    private $analyzer;
    
    // Réponses prédéfinies par catégorie
    private $responses = [
        'technique' => [
            'high' => [
                "Investigating issue urgently",
                "Technical team notified ASAP",
                "Emergency fix in progress"
            ],
            'medium' => [
                "Working on the fix now",
                "Issue being investigated",
                "Technical review in progress"
            ],
            'low' => [
                "Will check the issue soon",
                "Added to technical review",
                "Scheduled for inspection"
            ]
        ],
        'service' => [
            'high' => [
                "Support team contacted",
                "Immediate assistance coming",
                "Priority support engaged"
            ],
            'medium' => [
                "Service team notified",
                "Support reviewing request",
                "Help team responding soon"
            ],
            'low' => [
                "Service request logged",
                "Will review your request",
                "Support team informed"
            ]
        ],
        'account' => [
            'high' => [
                "Account team alerted",
                "Urgent access review",
                "Security team notified"
            ],
            'medium' => [
                "Checking account status",
                "Access being reviewed",
                "Account team notified"
            ],
            'low' => [
                "Will verify account soon",
                "Account review scheduled",
                "Team will check access"
            ]
        ],
        'billing' => [
            'high' => [
                "Urgent payment review",
                "Billing team alerted",
                "Financial check priority"
            ],
            'medium' => [
                "Reviewing billing issue",
                "Payment team notified",
                "Financial check started"
            ],
            'low' => [
                "Will check billing soon",
                "Payment review scheduled",
                "Financial review noted"
            ]
        ],
        'feature' => [
            'high' => [
                "Feature team notified",
                "Enhancement prioritized",
                "Update team alerted"
            ],
            'medium' => [
                "Feature request noted",
                "Enhancement considered",
                "Update team informed"
            ],
            'low' => [
                "Feature suggestion logged",
                "Will consider update",
                "Enhancement noted"
            ]
        ],
        'general' => [
            'high' => [
                "Team notified urgently",
                "Priority response coming",
                "Immediate review started"
            ],
            'medium' => [
                "Team reviewing issue",
                "Response in progress",
                "Looking into this now"
            ],
            'low' => [
                "Request acknowledged",
                "Will review shortly",
                "Team will check soon"
            ]
        ]
    ];

    // Réponses pour les questions
    private $questionResponses = [
        'technique' => [
            "Checking status for you",
            "Technical info coming",
            "Will explain solution"
        ],
        'service' => [
            "Service info coming",
            "Will provide details",
            "Checking status now"
        ],
        'account' => [
            "Account info coming",
            "Will verify details",
            "Checking records now"
        ],
        'billing' => [
            "Payment info coming",
            "Will check records",
            "Verifying details now"
        ],
        'feature' => [
            "Feature info coming",
            "Will provide details",
            "Checking options now"
        ],
        'general' => [
            "Information coming soon",
            "Will provide details",
            "Checking that for you"
        ]
    ];

    public function __construct() {
        $this->analyzer = new DescriptionAnalyzerController();
    }

    public function generateResponse($claimDescription) {
        try {
            // Analyser la description
            $analysis = $this->analyzer->analyzeDescription($claimDescription);
            
            // Sélectionner les réponses appropriées
            $suggestions = [];
            
            // Si c'est une question, ajouter une réponse spécifique aux questions
            if ($analysis['containsQuestion']) {
                $suggestions[] = $this->getRandomResponse($this->questionResponses[$analysis['category']]);
            }
            
            // Ajouter des réponses basées sur la catégorie et l'urgence
            $categoryResponses = $this->responses[$analysis['category']][$analysis['urgency']];
            while (count($suggestions) < 3) {
                $response = $this->getRandomResponse($categoryResponses);
                if (!in_array($response, $suggestions)) {
                    $suggestions[] = $response;
                }
            }

            return [
                'success' => true,
                'suggestions' => $suggestions,
                'analysis' => $analysis
            ];

        } catch (Exception $e) {
            error_log("Error in generateResponse: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error generating response: " . $e->getMessage(),
                'suggestions' => []
            ];
        }
    }

    private function getRandomResponse($responses) {
        return $responses[array_rand($responses)];
    }
} 