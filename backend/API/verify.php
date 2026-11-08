<?php 
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0); 
    }
    
    require_once __DIR__ . '/connect.php';
    require_once __DIR__ . '/helpers/helper.php';

    date_default_timezone_set('Europe/Paris');
    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? null;

    switch($action):
        case 'checkVer':
            try{
                if($data['token']){
                    checkVer($db, $data['token']);
                }
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([" success" => false, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        case 'newVerLink':
            try{
                newVerLink($db, $data['login']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([" success" => false, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        endswitch;

    function checkVer($db, $token){
        try{
            $sql= "UPDATE users 
                    SET is_verified = 1 
                    WHERE verification_token = :token 
                    AND token_expires_at > NOW() 
                    AND is_verified = 0";

            $stmt = $db->prepare($sql);
            $stmt->execute([':token' => $token]);

            if($stmt->rowCount() === 1){
                http_response_code(200);
                echo json_encode([  "success" => true, 
                                    "message" => "Account Verified !"]);
            }else{
                http_response_code(400);
                echo json_encode([" success" => false, 
                                    "message" => "Link invalid / expired !"]);
            }
        
        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !"]);
        }
    }
    

    function newVerLink($db, $login){
        try{
            $token = bin2hex(random_bytes(32));
            $token_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $sql = "UPDATE users SET verification_token = :token, token_expires_at = :tokendate WHERE email = :login OR username = :login";

            $stmt = $db->prepare($sql);
            $stmt->execute(
                [':token' => $token,
                ':tokendate' => $token_expires,
                ':login' => $login]
            );

            if(!str_contains($login, "@")){
                $sql_mail = "SELECT id, email from users WHERE username = :login LIMIT 1";
                $stmt1 = $db->prepare($sql_mail);
                $stmt1->execute(
                    [':login' => $login]
                );
                $fetch = $stmt1->fetch(PDO::FETCH_ASSOC);
            }

            if(!sendVerifyMail($fetch['email'], $token)){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Request Verify Issued !"]);
                return;
            };


            if($stmt->rowCount() === 1){
                http_response_code(200);
                echo json_encode([  "success" => true, 
                                    "message" => "New link has been Sent !"]);
            }else{
                http_response_code(400);
                echo json_encode([" success" => false, 
                                    "message" => "Invalid infos !"]);
            }

        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !"]);
        }
    }
?>