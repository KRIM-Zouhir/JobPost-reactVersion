<?php 
    include_once 'connect.php';
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0); 
    }

    date_default_timezone_set('Europe/Paris');
    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? "";

    switch($action){


        case 'signup':
            $signup_username = $data['username'] ?? '';
            $signup_email = filter_var($data['email']  ?? '' , FILTER_VALIDATE_EMAIL) ;
            $signup_password = $data['password'] ?? '';

            if($signup_username && $signup_email && $signup_password){
                session_start();
                signup($db, $signup_username, $signup_email, $signup_password);
            }
            break;

        case 'login' :
            $login_input = $data['username'] ?? '';
            $login_password = $data['password'] ?? '';

            if($login_input && $login_password){
                session_start();
                if(str_contains($login_input, "@")){
                    $login_email = $login_input;
                    $login_username = "";
                }else{
                    $login_username = $login_input;
                    $login_email = "";
                }

                login($db,$login_username, $login_email, $login_password);

            }
            break;

        
        case 'profile':
            session_start();
            if(!isset($_SESSION['user_id'])){
                http_response_code(401);
                echo json_encode([
                    "success" => false,
                    "message" => "You are not logged in",
                ]);
                break;
            }

            getuser($db , $_SESSION['user_id']);
            break;

        case 'logout':
            session_start();
            $_SESSION = [];


            if(!isset($_COOKIE[session_name()])):
                http_response_code(401);
                echo json_encode([
                    "success" => false,
                    "message" => "You are not logged in !",
                ]);
    
            elseif(isset($_COOKIE[session_name()])):
                setcookie(session_name(), '', time()-7000000, '/');
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Logged out succefully !",
                ]);
            endif;
            session_destroy();
            break;


        case 'verify-session': 
            if(!isset($_COOKIE[session_name()])){
                http_response_code(200);
                echo json_encode([
                    "success" => false,
                    "message" => "No session exist !",
                ]);
            }else{
                session_start();
                if(!isset($_SESSION['user_id']) ){
                    http_response_code(200);
                    echo json_encode([
                        "success" => false,
                        "message" => "Info arent correct !",
                    ]);
                    return;
                }

                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "message" => "Session found!",
                    "user" => [
                            "userid" => $_SESSION['user_id'],
                            "username" => $_SESSION['username'],
                            "role" => $_SESSION['role']
                    ]
                ]);

            }
            break;


    }





    function signup($db, $username, $email, $password){
        try{

            $password = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));
            $token_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $sql = "INSERT INTO users 
            (username, email, password, role, verification_token, token_expires_at)
            VALUES 
            (:username, :email, :password, :role, :verify_token, :token_time )";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':role', "user");
            $stmt->bindValue(':verify_token', $token);
            $stmt->bindValue(':token_time', $token_expires);
            $stmt->execute();

            http_response_code(201);
            echo json_encode(["success" => true, "message" => "Account Created Successfully !"]);

        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Singup Failed"]);
        }
    }


    function login($db, $username, $email,  $password){
        try{
            $sql = "SELECT * FROM users WHERE username = :username OR email = :email";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':username' , $username);
            $stmt->bindValue(':email' , $email);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!empty($fetch)){
                if($fetch['is_verified'] != 0){
                    $fetched_pwd = $fetch['password'];
    
                    if(password_verify($password , $fetched_pwd)){
                        $_SESSION['user_id'] = $fetch['id'];
                        $_SESSION['username'] = $fetch['username'];
                        $_SESSION['role'] = $fetch['role'];
                        http_response_code(200);
                        echo json_encode(["success" => true, "message" => "Login Successed"]);

                    }elseif($fetched_pwd != $password){
                        http_response_code(401);
                        echo json_encode(["success" => false, "message" => "Invalid Credentiels"]);                    
                    }

                }else{
                    http_response_code(401);
                    echo json_encode(["success" => false, "message" => "Account not verified"]);      
                    
                }
                
            }else{
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Username/Email Not Found"]);
            }

        }catch(PDOException $e){
            return false;
        }
    }

    function getuser($db, $id){
        try{
            $sql = "SELECT * FROM users WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!empty($fetch)){
                $fetched_username = $fetch['username'];
                $fetched_email = $fetch['email'];
                $fetched_role = $fetch['role'];
                http_response_code(200);
                echo json_encode([  
                    "success" => true, 
                    "message" => "User found", 
                    "user" => [
                        "username" => $fetched_username,
                        "email" => $fetched_email,
                        "role" => $fetched_role
                ]]);
            }else{
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "No user found with this ID !"]);
            }
        }catch(PDOException $e){
            return false;
        }
    }
?>