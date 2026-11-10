<?php 
    
    require_once __DIR__ .  '/connect.php';
    require_once __DIR__ . '/helper.php';


    if( $_SERVER['REQUEST_METHOD'] == 'POST'){
        $form_type = $_POST['form_type'];


        if ($form_type == 'signup'){
            $signup_user = htmlentities($_POST['username']);
            $signup_email = htmlentities($_POST['email']);
            $signup_pwd = trim($_POST['pwd']); 
            session_start();

            if (!preg_match('/^[A-Za-z._]+$/', $signup_user)) {
                echo "ERROR USERNAME";
                exit;
            }

            if(signup($db , $signup_user, $signup_email, $signup_pwd)){
                $_SESSION['success'] = "Account created successfully!";
                exit;
            }
        } 
        
        
        elseif($form_type == 'login'){
            $login_user = htmlentities($_POST['username']);
            $login_pwd = trim($_POST['pwd']);
            $saveinfo = isset($_POST['saveinfo']) ? true : false;
            if ($saveinfo){
                setcookie('userinfo', $login_user, time() + (60 * 60 * 24 * 30) , '/');
                setcookie('pwdinfo', $login_pwd , time()  + (60 * 60 * 24 * 30), '/');
            }

            if(str_contains($login_user, "@")){
                    login($db, $username = '' , $login_user, $login_pwd);
            }else{
                    login($db, $login_user, $email = '', $login_pwd);
                }
            
        }

        elseif($form_type == 'changepwd'){
            $user_id = htmlentities($_POST['user_id']);
            $newpwd = htmlentities(trim($_POST['new_pwd']));

            if(change_pwd($db , $user_id , $newpwd)){
                header("Location: /admin/users");
            }

        }

        elseif($form_type == 'edit_info'){
            $userid = htmlentities($_POST['user_id']);
            $username = htmlentities($_POST['user_name']);
            $useremail = htmlentities($_POST['user_email']);
            $userrole = htmlentities($_POST['user_role']);
            $userverified = htmlentities($_POST['user_verified']);
            $usertoken = htmlentities($_POST['user_token']);
            $usertokenexpires = htmlentities($_POST['user_tokenexpire']);
            $userdate = htmlentities($_POST['user_date']);

            if(update_user($db , $userid, $username,$useremail , $userrole ,$userverified ,$usertoken,$usertokenexpires,$userdate)){
                header("Location: /admin/users");
                exit;
            }
        }

        elseif($form_type == 'emailform'){
            $email = htmlentities($_POST['user_email']);

            reset_pwd($db, $email);
            header("Location: /reset-password");

        }

        elseif($form_type == 'resetform'){
            $newpwd = htmlentities($_POST['newpwd']);
            $newpwd1 = htmlentities($_POST['newpwd1']);
            $token = htmlentities($_POST['form_token']);

            if($newpwd == $newpwd1){
                $sql = "SELECT user_id FROM password_resets WHERE token = :token";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':token', $token);
                $stmt->execute();
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if(change_pwd($db , $fetch['user_id'], $newpwd)){
                    echo "Password reset";
                    header("Location: /login");
                }
            }
        }
    }

    
    function signup($db , $username , $email, $password) {
        try {
            $table = "users" ; 
            $role = "user";
            $token = bin2hex(random_bytes(32));
            date_default_timezone_set('Europe/Madrid');

            $token_expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO $table (username, email , password, role, verification_token, token_expires_at) VALUES (:username , :email , :password , :role , :token , :token_expires)";
        
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':role', $role);
            $stmt->bindValue(':token', $token);
            $stmt->bindValue(':token_expires', $token_expires);
            $stmt->execute();

            send_verify_mail($email, $token);
            header("Location: /verify-email");

            return true; 

        } catch(PDOException $e ){
            echo "An error has occured ! $e";
        }
    
    }


    function login($db , $username , $email , $password){
        try {
            $table = "users"; 

            $sql = "SELECT * FROM $table WHERE username = :username OR email = :email";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':username' , $username);
            $stmt->bindValue(':email' , $email);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (empty($fetch)){
                echo "This user doesnt exist!";
            }else{
                if($fetch['role'] === 'user' AND $fetch['is_verified'] == 0){
                    echo "User isnt verified";
                    exit;
                }else{
                    session_start();
                    
                    $fetched_pwd = htmlentities($fetch['password']);
    
                    if(password_verify($password , $fetched_pwd)){
                        $_SESSION['user_id'] = $fetch['id'];
                        $_SESSION['username'] = $fetch['username'];
                        $_SESSION['role'] = $fetch['role'];
                        header("Location: /profile");
                        exit;

                    }elseif( $fetched_pwd != $password ){
                        $_SESSION['error'] = "Password is incorrect" ;
                        header("Location: /login");
                        exit;
                    }
                }
            }
        

        }catch (PDOException $e ){
            echo "An error has occured ! $e";
        }
    }



    function profile_info ($db , $id){
        try{
            $table = "users"; 
            $sql = "SELECT * FROM $table WHERE id = :id "; 

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!empty($fetch)){
                $fetched_username = htmlentities($fetch['username']);
                $fetched_email = htmlentities($fetch['email']);
                $fetched_role = htmlentities($fetch['role']);
                return [$fetched_username , $fetched_email  , $fetched_role];
            }
            else{
                header('Location: /logout');
                return false;
            }

        }catch(PDOException $e){
            echo "An error has occured !" . $e ;
        }
    }

    function get_users($db , $start , $rows_per_page){
        try{
            $table = "users";
            $sql = "SELECT * FROM $table LIMIT $start, $rows_per_page";
            $users = [];

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $fetch = $stmt->fetchall(PDO::FETCH_ASSOC);

            if(!empty($fetch)){
                foreach($fetch as $row){
                    $user =[
                        'user_id' => htmlentities($row['id']),
                        'user_name' => htmlentities($row['username']),
                        'user_email' => htmlentities($row['email']),
                        'user_role' => htmlentities($row['role']),
                        'user_date' => htmlentities($row['created_at']),
                        'verification_token' => htmlentities($row['verification_token']) ?? '',
                        'token_expires_at' => htmlentities($row['token_expires_at'])  ?? '',
                        'is_verified' => htmlentities($row['is_verified'])
                    ];

                    $users[] = $user;
                }
                return $users;
            }

            return $fetch;

        }catch(PDOException $e){
            echo $e;
            return false;
        }
        
    }

    function update_user($db , $userid , $username ,  $useremail , $userrole , $userverified , $usertoken , $usertokenexpires , $userdate){
        try{
            $table = "users";
            $sql = "UPDATE $table 
                    SET username = :username , 
                        email = :email ,
                        role = :role , 
                        is_verified = :verified , 
                        verification_token = :token , 
                        token_expires_at = :tokenexpires , 
                        created_at = :userdate
                    WHERE id = :userid";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':userid', $userid);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':email', $useremail);
            $stmt->bindValue(':role', $userrole);
            $stmt->bindValue(':verified', $userverified);
            $stmt->bindValue(':token', $usertoken);
            $stmt->bindValue(':tokenexpires', $usertokenexpires);
            $stmt->bindValue(':userdate', $userdate);
            $stmt->execute();
            return true;
            
        }catch(PDOException $e){
            echo $e;
            return false;
        }
        }
                
                
        function change_pwd($db , $user_id , $newpwd){
                try{
                    $table ="users";
                    $hashed_newpwd = password_hash($newpwd, PASSWORD_DEFAULT);
                    $sql = "UPDATE $table SET password=:password WHERE id=:id";
    
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':password', $hashed_newpwd);
                    $stmt->bindValue(':id', $user_id);
                    $stmt->execute();
    
                    return true;
                
    
                }catch(PDOException $e){
                    echo $e;
                    return false;
                }
        }


        function reset_pwd($db , $email){
            try{
                $sql_email = "SELECT * FROM users WHERE email = :email";
                $stmt = $db->prepare($sql_email);
                $stmt->bindValue('email' , $email);
                $stmt->execute();
                $fetch_email = $stmt->fetch(PDO::FETCH_ASSOC);

                if(empty($fetch_email['email'])){
                    return false;
                }else{
                    $resetToken = bin2hex(random_bytes(32));
                    date_default_timezone_set('Europe/Madrid');
                    $resetExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                    $sql_reset = "INSERT INTO password_resets (user_id, token, token_expires_at) VALUES (:user_id, :token, :token_expires_at)";
                    $stmt = $db->prepare($sql_reset);
                    $stmt->bindValue(':user_id' , $fetch_email['id']);
                    $stmt->bindValue(':token' , $resetToken);
                    $stmt->bindValue(':token_expires_at' , $resetExpires);
                    $stmt->execute();
                    send_resend_pwd($email, $resetToken);
                    return true;
                }
            }catch(PDOException $e){
                echo $e;
                return false;
            }
        }

        function check_token_pwd($db , $token){
            try{
                $sql = "SELECT * FROM password_resets WHERE token = :token AND token_expires_at > NOW()";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':token' , $token);
                $stmt->execute();
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                return $fetch;

            }catch(PDOException $e){
                echo $e ;

            }
        }
?>