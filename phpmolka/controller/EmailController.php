<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Chemin absolu vers le dossier racine du projet
$rootPath = dirname(dirname(__FILE__));
require_once($rootPath . '/vendor/autoload.php');
require_once($rootPath . '/config/mail_config.php');

class EmailController {
    private $mailer;
    
    public function __construct() {
        try {
            $this->mailer = new PHPMailer(true);
            
            // Configuration du serveur SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = 'tls';
            $this->mailer->Port = SMTP_PORT;
            
            // Configuration de l'expéditeur
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log("Erreur d'initialisation de PHPMailer: " . $e->getMessage());
            throw new Exception("Impossible d'initialiser le service d'email.");
        }
    }
    
    public function sendClaimConfirmation($email, $description) {
        try {
            // Réinitialiser toutes les propriétés
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Destinataire
            $this->mailer->addAddress($email);
            
            // Contenu
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Confirmation de votre réclamation - InnoConnect';
            
            // Corps du message
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #007bff;'>Confirmation de Réclamation</h2>
                    <p>Bonjour,</p>
                    <p>Nous avons bien reçu votre réclamation. Notre équipe va la traiter dans les plus brefs délais.</p>
                    
                    <div style='background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #007bff;'>
                        <strong>Votre réclamation :</strong><br>
                        " . htmlspecialchars($description) . "
                    </div>
                    
                    <p>Nous vous tiendrons informé de l'avancement de votre demande.</p>
                    
                    <p style='margin-top: 20px;'>Cordialement,<br>L'équipe InnoConnect</p>
                    
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666;'>
                        <p>Ceci est un message automatique, merci de ne pas y répondre.</p>
                    </div>
                </div>
            ";
            
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));
            
            $sent = $this->mailer->send();
            error_log("Email envoyé avec succès à : " . $email);
            return $sent;
            
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email à {$email}: " . $e->getMessage());
            return false;
        }
    }
} 