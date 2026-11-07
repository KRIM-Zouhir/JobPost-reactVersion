<?php
    require_once __DIR__ . '/../../../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;


    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    
    function sendVerifyMail($usermail, $token){
        $mail = new PHPMailer();
        
        try{
            $mail->isSMTP();

            $mail->Host       = $_ENV['mailHost'];                  
            $mail->SMTPAuth   = true;                                
            $mail->Username   = $_ENV['mailUsername'];                  
            $mail->Password   = $_ENV['mailPassword']; 
            $mail->Port       = 587;     

            $mail->setFrom('hello@demomailtrap.com', 'JobPost');
            $mail->addAddress($usermail); 

            $mail->isHTML(true);  
            $mail->Subject = 'Verify your email - JobPost';
            
            $mail->Body = "
                <h2>Welcome to JobPost!</h2>
                <p>Thanks for signing up. Please verify your email address to activate your account.</p>
                <p><a href='http://localhost:5173/verified?token={$token}'>Click here to verify your email</a></p>
                <p>This link will expire in 10 minutes.</p>
                <p>If you didn't sign up for JobPost, you can safely ignore this email.</p>
            ";

            $mail->send();
            return true;
        }catch(Exception $e){
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo} / $e";
            return false;
        }
    }

?>



