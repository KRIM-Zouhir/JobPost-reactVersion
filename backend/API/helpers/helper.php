<?php
    require_once __DIR__ . '/../../../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;


    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    
    function sendMail($usermail, $body = null){
        $mail = new PHPMailer();
        
        try{
            $mail->isSMTP();

            $mail->Host       = $_ENV['mailHost'];                  
            $mail->SMTPAuth   = true;                                
            $mail->Username   = $_ENV['mailUsername'];                  
            $mail->Password   = $_ENV['mailPassword']; 
            $mail->Port       = $_ENV['mailPort'];     

            $mail->setFrom('hello@demomailtrap.com', 'JobPost');
            $mail->addAddress($usermail); 

            $mail->isHTML(true);  
            $mail->Subject = 'JobPost';
            
            $mail->Body = $body;

            $mail->send();
            return true;
        }catch(Exception $e){
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo} / $e";
            return false;
        }
    }

?>



