<?php 
    include_once 'connect.php';
    header("Access-Control-Allow-Origin: http://localhost:5173");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0); 
    }

    session_start();
    $action = $_POST['action'] ?? "";

    switch($action){
        case 'apply':

            $user_id = $_SESSION['user_id'] ?? null;
            $post_id = $_POST['postid'] ?? null;
            $cover_name = null;

            $folder_path = __DIR__ . "/uploads/";
            if(!is_dir($folder_path)){
                mkdir($folder_path);
                $post_folder_path = $folder_path . "post_" . $post_id . "/";
                if(!is_dir($post_folder_path)){
                    mkdir($post_folder_path);
                }
            }
            
            if($user_id && $post_id ){
                $status = "pending";
                if((isset($_FILES['cvfile']) ) && $_FILES['cvfile']['error'] == 0){
                    $tmp = $_FILES['cvfile']['tmp_name'];
                    $cv_name = "CV" . "_" . bin2hex(random_bytes(10)) . "_" . $post_id . ".pdf";

                    move_uploaded_file($tmp, $post_folder_path . $cv_name);
                } 
                if((isset($_FILES['coverfile']) ) && $_FILES['coverfile']['error'] == 0){
                    $tmp = $_FILES['coverfile']['tmp_name'];
                    $cover_name = "Cover" . "_" . bin2hex(random_bytes(10)) . "_" . $post_id . ".pdf";

                    move_uploaded_file($tmp, $post_folder_path . $cover_name);
                } 
                newApply($db, $user_id, $post_id,  $cv_name, $cover_name, $status);
            }else{
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Missing some infos !"]);
            }   
            break;

        case 'verifyApp':
            try{
                $user_id = $_SESSION['user_id'];
    
                verifyApp($db, $user_id);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Missing some infos !"]);
            }

            break;
            
        case 'getApp':
            try{
                $start = (int)  $_POST['start'] ?? null;
                $rows = (int) $_POST['rows'] ?? null;
                
                getApps($db, $start, $rows, $_POST['searchTerm']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Missing some infos !"]);
            }
            break;

        case 'statusApp':
            try{
                statusApp($db, (int) $_POST['appid'], $_POST['status']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Missing some infos !"]);
            }
            break;

    }


    function newApply($db , $user_id, $post_id, $cv_name , $cover_name , $status){
        try{
            $sql = "INSERT INTO applications (user_id, post_id, cv_filename, cover_note, status) 
                    VALUES (:user_id, :post_id, :cv_filename, :cover_note , :status)";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':post_id', $post_id);
            $stmt->bindValue(':cv_filename', $cv_name);
            $stmt->bindValue(':cover_note', $cover_name);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(200);
            echo json_encode(["success" => true, "message" => "Application Created Successfully !"]);

        
        }catch(PDOException $e)
        {
            error_log("DB Error in newApply: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Application Failed !", "debug" => $e]);
        }
    }

    function verifyApp($db, $user_id){
        try{

            $posts = [];
            $sql = "SELECT post_id FROM applications WHERE user_id = :user_id"; 

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->execute();
            $fetch= $stmt->fetchall(PDO::FETCH_ASSOC);
            

            foreach($fetch as $row){
                $post_id = $row['post_id'];

                $posts[]= $post_id;
            }

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Applications Received Successfully !",
                "posts_id" => $posts
            ]);

        
        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Application Failed !"]);
        
        }
    }

    function getApps($db, $start, $rows, $searchTerm){
        try{
            if(!$searchTerm){
                $sql = "SELECT app.*, us.username, pos.title 
                        FROM applications as app
                        JOIN users as us ON us.id = app.user_id
                        JOIN posts as pos ON pos.id = app.post_id
                        ORDER BY app.applied_at DESC
                        LIMIT $start, $rows";
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }else{
                $sql = "SELECT app.*, us.username, pos.title 
                        FROM applications as app
                        JOIN users as us ON us.id = app.user_id
                        JOIN posts as pos ON pos.id = app.post_id
                        WHERE username LIKE :searchTerm 
                            OR title LIKE :searchTerm
                        ORDER BY app.applied_at DESC
                        LIMIT $start, $rows";
                $stmt = $db->prepare($sql);
                $stmt->execute(
                    [':searchTerm' => '%' . $searchTerm . '%']
                );
            }
            
            $fetch = $stmt->fetchall(PDO::FETCH_ASSOC);

            $applications = [];
            foreach($fetch as $row){
                $application = [
                    'appid' => $row['id'],
                    'userid' => $row['user_id'],
                    'username' => $row['username'],
                    'postid' => $row['post_id'],
                    'posttitle' => $row['title'],
                    'cvfilename' => $row['cv_filename'],
                    'coverfilename' => $row['cover_note'],
                    'status' => $row['status'],
                    'appdate' => $row['applied_at']
                    ];
                $applications[] = $application;
                
            }
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Applications Loaded Successfully !",
                "data" => $applications
            ]);
        }catch(PDOException $e){
            echo "An error has occured $e";
            
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Applications Loading Failed !"
            ]);
        }

    }  

    function statusApp($db, $appid, $status){
        try{
            $sql = "UPDATE applications SET status = :status WHERE id = :appid";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':appid', $appid);
            $stmt->execute();

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Status Changed Successfully !",
            ]);
        }catch(PDOException $e){
            echo "An error has occured $e";
            
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Status Change Failed !"
            ]);
        }
    }


?>