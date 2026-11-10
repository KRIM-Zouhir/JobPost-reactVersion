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

    require_once __DIR__ . '/../../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;


    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();


    date_default_timezone_set('Europe/Paris');
    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? "";
    session_start();

    switch($action):
        case 'signup':
            $signup_username = $data['username'] ?? '';
            $signup_email = filter_var($data['email']  ?? '' , FILTER_VALIDATE_EMAIL) ;
            $signup_password = $data['password'] ?? '';

            if($signup_username && $signup_email && $signup_password){
                signup($db, $signup_username, $signup_email, $signup_password);
            }
            break;

        case 'login' :
            $login_input = $data['username'] ?? '';
            $login_password = $data['password'] ?? '';

            if($login_input && $login_password){
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
                if(!isset($_SESSION['user_id']) ){
                    http_response_code(400);
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

            
        case 'getUsers':
            try{
                getUsers($db, $data['start'], $data['row']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([  "success" => true, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        case 'createUser':
            try{
                addUser($db, $data['infos']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([  "success" => true, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        case 'editUser':
            try{
                $infos = $data['infos'];
                editUser($db, $infos);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([  "success" => true, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        case 'deleteUser':
            try{
                deleteUser($db, $data['infos']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([  "success" => true, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        case 'forgotPwd':
            try{
                forgotPWD($db, $data['email']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([  "success" => true, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        case 'verifyCode':
            try{
                verifyCode($db, $data['email'], $data['code']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([  "success" => true, 
                                    "message" => "Infos arent correct !"]);
            }
            break;
        case 'resetPassword':
            try{
                resetPwd($db, $data['email'], $data['newPassword']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([  "success" => true, 
                                    "message" => "Infos arent correct !"]);
            }
            break;

        
        endswitch;




    function signup($db, $username, $email, $password){
        try{

            $password = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));
            $token_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $body = "
                <h2>Welcome to JobPost!</h2>
                <p>Thanks for signing up. Please verify your email address to activate your account.</p>
                <p><a href='http://localhost:5173/verified?token={$token}'>Click here to verify your email</a></p>
                <p>This link will expire in 10 minutes.</p>
                <p>If you didn't sign up for JobPost, you can safely ignore this email.</p>";
            
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
            
            if(!sendMail($email, $body)){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Request Verify Issued !"]);
                return;
            }
        
            http_response_code(201);
            echo json_encode(["success" => true, "message" => "Account Created Successfully !"]);

        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !"]);
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
                    echo json_encode([  "success" => false, 
                                        "message" => "Account not verified",
                                        "verify" => false]);      
                    
                }
                
            }else{
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Username/Email Not Found"]);
            }

        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !"]);
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
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !"]);
        }
    }

    function getUsers($db, $start, $row){
        try{
            $sql = "SELECT * FROM users LIMIT $start, $row";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $fetchall = $stmt->fetchall(PDO::FETCH_ASSOC);

            if(!empty($fetchall)){
                $users=[];
                foreach($fetchall as $row){
                    $user = [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'email' => $row['email'],
                        'role' => $row['role'],
                        'is_verified' => $row['is_verified'],
                        'token' => $row['verification_token'],
                        'tokenexpire' => $row['token_expires_at'],
                        'date' => $row['created_at']
                    ];
                
                    $users[]= $user;
                }
                http_response_code(200);
                echo json_encode([  "success" => true, 
                                    "message" => "Users found !", 
                                    "data" => $users 
                    ]);
            }else{
                http_response_code(404);
                echo json_encode(["success" => false, "message" => "no users found"]);
            }
        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !"]);
        }
    }

    function addUser($db, $data){
        try{
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));
            $tokentime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            $sql = "INSERT INTO users 
                        (username, email, password, role, verification_token, token_expires_at)
                    VALUES
                        (:username, :email, :password, :role, :verification_token, :token_expires_at)
            ";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':username', $data['username']);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':role', $data['role']);
            $stmt->bindValue(':verification_token', $token);
            $stmt->bindValue(':token_expires_at', $tokentime);
            $stmt->execute();

            http_response_code(200);
            echo json_encode([  "success" => true, 
                                "message" => "Users Edited !"]);

        }catch(PDOException $e){            
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !",
                                "debug" => $e]);
         
        }
    }

    function editUser($db, $data){
        try{
            $sql = "UPDATE users SET
                        username = :username, 
                        email = :email,
                        role = :role,
                        is_verified = :is_verified,
                        verification_token = :token,
                        token_expires_at = :tokenexpire,
                        created_at = :date

                        WHERE  id = :userid";

            $stmt = $db->prepare($sql);
            $stmt->bindValue('userid', $data['id']);
            $stmt->bindValue('username', $data['username']);
            $stmt->bindValue('email', $data['email']);
            $stmt->bindValue('role', $data['role']);
            $stmt->bindValue('is_verified', $data['is_verified']);
            $stmt->bindValue('token', $data['token']);
            $stmt->bindValue('tokenexpire', $data['tokenexpire']);
            $stmt->bindValue('date', $data['date']);
            $stmt->execute();

            http_response_code(200);
            echo json_encode([  "success" => true, 
                                "message" => "Users Edited !"]);

        }catch(PDOException $e){            
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !"]);
         
        }
    }

    function deleteUser($db, $data){
        try{
            $sql = "DELETE FROM users WHERE id = :userid";

            $stmt = $db->prepare($sql);
            $stmt->execute(
                [':userid' => $data['id']]
            );
            http_response_code(200);
            echo json_encode([  "success" => true, 
                                "message" => "Users Deleted !"]);

        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !"]);
         
        
        }
    }

    function forgotPWD($db, $email){
        try{
            $sql = "SELECT id, username, email FROM users WHERE email = :email LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([":email" => $email]);
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$fetch['email']){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "No account exist with this email !"]);
                return;
            }

            $code = strtoupper(bin2hex(random_bytes(4)));
            $code_hash = hash_hmac('sha256', $code, $_ENV['OTP_SECRET']);
            $code_expiration = date('Y-m-d H:i:s', strtotime('+1 minutes'));

            $body = "hi {$fetch['username']},<br><br>Your password reset code is: <strong>{$code}</strong>.<br>This code will expire in 1 minute.<br><br>If you didn't request a password reset, you can safely ignore this email.";
            $sql_insert = "INSERT INTO password_reset
                            (userid, code_hash, expires_at, attempts)
                            VALUES 
                            (:userid, :code_hash, :expires_at, 0)";
            $stmt_insert = $db->prepare($sql_insert);
            $stmt_insert->execute(
                ["userid" => $fetch['id'],
                ":code_hash" => $code_hash, 
                "expires_at" => $code_expiration]
            );

            sendMail($fetch['email'], $body);
            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Code created successfully !", "debug" => $code, "statuscode" => true]);

        
        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !",
                                "debug" => $e]);
        
        }
    }

    function verifyCode($db, $email, $code){
        try{
            $sql = "SELECT pr.id, pr.userid, pr.code_hash, pr.expires_at, pr.attempts, u.email 
                    FROM password_reset AS pr JOIN users AS u 
                    WHERE u.email = :email AND pr.userid = u.id 
                    ORDER BY pr.expires_at DESC LIMIT 1";
            $stmt= $db->prepare($sql);
            $stmt->execute([":email" => $email]);
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$fetch['email']){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "No account exist with this email !"]);
                return;
            }

            if($fetch['attempts'] >= 3){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Maximum attempts reached. Please request a new code."]);
                return;
            }

            if($fetch['expires_at'] < date('Y-m-d H:i:s')){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Code has expired. Please request a new code."]);
                return;
            }

            if(!hash_equals($fetch['code_hash'], hash_hmac('sha256', $code, $_ENV['OTP_SECRET']))){
                $sql_update = "UPDATE password_reset SET attempts = attempts + 1, verified_at = NOW() WHERE id = :id";
                $stmt_update = $db->prepare($sql_update);
                $stmt_update->execute([":id" => $fetch['id']]);

                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Invalid code. Please try again."]);
                return;
            }


            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Code verified successfully !", "statusverify" => true]);
            
        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !",
                                "debug" => $e]);
        }
    }

    function resetPwd($db, $email, $newPwd){
        try{
            $sql = "UPDATE users SET password = :password WHERE email = :email";

            $stmt = $db->prepare($sql);
            $stmt->execute(
                [':password' => password_hash($newPwd, PASSWORD_DEFAULT),
                ':email' => $email]
            );

            if($stmt->rowCount() === 0){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "No account exist with this email !"]);
                return;
            }
            
            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Password reset successfully !"]);
        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode([" success" => false, 
                                "message" => "Request Issued !",
                                "debug" => $e]);
        }
    }
?>