<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['MAIL_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['MAIL_USERNAME'];
        $this->mail->Password = $_ENV['MAIL_PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = $_ENV['MAIL_PORT'];
        $this->mail->CharSet = 'UTF-8';
        $this->mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
    }
    public function sendEmail(string $to, string $subject, string $body): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
    public function sendAccountVerifiedEmail(string $to, string $name): bool
{
    try {
        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->isHTML(true);
        $this->mail->Subject = 'Votre compte ITINIREVES est maintenant vérifié !';

        $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: 'Helvetica', 'Arial', sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        width: 100%;
                        max-width: 600px;
                        margin: 40px auto;
                        background: #ffffff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .header img {
                        width: 120px;
                    }
                    .content {
                        font-size: 16px;
                        color: #333333;
                        line-height: 1.6;
                    }
                    .button {
                        display: block;
                        width: 200px;
                        margin: 30px auto;
                        padding: 15px;
                        background-color: #3498db;
                        color: white;
                        text-align: center;
                        text-decoration: none;
                        border-radius: 30px;
                        font-weight: bold;
                    }
                    .footer {
                        text-align: center;
                        font-size: 12px;
                        color: #999999;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <img src='https://res.cloudinary.com/dgmv7apiu/image/upload/v1745691101/logo_1_q6hanc.png' alt='ITINIREVES'>
                    </div>
                    <div class='content'>
                        <h2>Bonjour $name,</h2>
                        <p>Félicitations ! Votre compte a été <strong>vérifié avec succès</strong>.</p>
                        <p>Vous pouvez maintenant vous connecter et profiter de tous nos services !</p>

                        <a href='http://127.0.0.1:8000//login' class='button'>Se connecter</a>

                        <a href='127.0.0.1:8000/login' class='button'>Se connecter</a>

                        <p>Merci de faire partie de la communauté ITINIREVES ✈️🌍</p>
                    </div>
                    <div class='footer'>
                        &copy; 2025 ITINIREVES. Tous droits réservés.
                    </div>
                </div>
            </body>
        </html>
        ";

        $this->mail->Body = $body;
        return $this->mail->send();
    } catch (Exception $e) {
        return false;
    }
}

    public function sendFeedbackEmail(string $to, string $name, string $feedback, int $note): bool
    {
        try {
            $this->mail->addAddress($to);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Merci pour votre feedback - ITINIREVES';
            
            $body = "
            <html>
                <head>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            color: #333;
                        }
                        .email-container {
                            width: 100%;
                            max-width: 600px;
                            margin: 0 auto;
                            padding: 20px;
                            background-color: #f9f9f9;
                            border-radius: 10px;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }
                        .email-header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .email-header img {
                            width: 150px;
                            margin-bottom: 10px;
                        }
                        .email-content {
                            font-size: 16px;
                            line-height: 1.6;
                        }
                        .email-footer {
                            margin-top: 20px;
                            font-size: 14px;
                            text-align: center;
                            color: #888;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <div class='email-header'>
                            <img src='https://res.cloudinary.com/dgmv7apiu/image/upload/v1745691101/logo_1_q6hanc.png' alt='Logo ITINIREVES'>
                            <h2>Confirmation de Suppression de Votre Feedback</h2>
                        </div>
                        <div class='email-content'>
                            <p>Bonjour " . $name . ",</p>
                            <p>Nous souhaitons vous informer que votre feedback a été supprimé de notre système. Nous avons pris en compte votre évaluation de <strong>" . $note . "/5 étoiles</strong> ainsi que votre commentaire :</p>
                            <p><em>" . $feedback . "</em></p>
                            <p>Nous comprenons que des modifications peuvent être nécessaires et nous apprécions votre participation à l'amélioration de nos services. Si vous avez des questions ou souhaitez réitérer votre évaluation, n'hésitez pas à nous contacter.</p>
                            <p>Merci pour votre compréhension et votre confiance.</p>
                        </div>
                        <div class='email-footer'>
                            <p>Cordialement,<br>L'équipe ITINIREVES</p>
                        </div>
                    </div>
                </body>
            </html>
            ";
            
            
        
            
            $this->mail->Body = $body;
            $this->mail->send();
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }



public function sendAccountInvalidEmail(string $to, string $name): bool
{
    try {
        $this->mail->clearAddresses();
        $this->mail->addAddress($to);
        $this->mail->isHTML(true);
        $this->mail->Subject = 'Échec de la vérification de votre compte ITINIREVES';

        $body = "
        <html>
            <head>
                <style>
                    body {
                        font-family: 'Helvetica', 'Arial', sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        width: 100%;
                        max-width: 600px;
                        margin: 40px auto;
                        background: #ffffff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                    }
                    .header img {
                        width: 120px;
                    }
                    .content {
                        font-size: 16px;
                        color: #333333;
                        line-height: 1.6;
                    }
                    .footer {
                        text-align: center;
                        font-size: 12px;
                        color: #999999;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <img src='https://res.cloudinary.com/dgmv7apiu/image/upload/v1745691101/logo_1_q6hanc.png' alt='ITINIREVES'>
                    </div>
                    <div class='content'>
                        <h2>Bonjour $name,</h2>
                        <p>Nous n'avons pas pu vérifier votre compte en raison d'un échec de vérification (par exemple, le reCAPTCHA n'a pas été validé).</p>
                        <p>Merci de réessayer l’inscription ou de nous contacter si le problème persiste.</p>
                        <p>Nous restons à votre disposition pour toute question.</p>
                    </div>
                    <div class='footer'>
                        &copy; 2025 ITINIREVES. Tous droits réservés.
                    </div>
                </div>
            </body>
        </html>
        ";

        $this->mail->Body = $body;
        return $this->mail->send();
    } catch (Exception $e) {
        return false;
    }
}
}