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
    session_start();


    switch($data['action']){
        case 'get':
            $start = $data['start'] ?? null;
            $rows = $data['rows'] ?? null;
            $searchTerm = $data['searchTerm'] ?? null;
        
            if($start !== null && $rows !== null){
                getpost($db, $start, $rows, $searchTerm);
            }else{
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Pagination values are required",
                    "pagination" => $start . $rows
                ]);
            }
            break;

        case 'create':        
            try{
                createPost($db, $_SESSION['user_id'], $data['data']['title'], $data['data']['company'], $data['data']['location'], $data['data']['type'], $data['data']['description'], $data['data']['salary'], $data['data']['place']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Some infos are required"
                ]);
            }
            break;
        case 'edit':
            try{
                editPost($db, $data['data']['postid'], $data['data']['title'], $data['data']['company'], $data['data']['location'], $data['data']['type'], $data['data']['description'], $data['data']['salary'], $data['data']['place'], $data['data']['status']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Some infos are required"
                ]);
            }
            break;
        case 'delete': 
            try{
                removePost($db, $data['data']['postid']);
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "message" => "Some infos are required"
                ]);
            }
    }

    function getpost($db, $start, $rows_per_page, $searchTerm){
        try{

            if(!$searchTerm){
                $sql = "SELECT * FROM  posts LIMIT $start , $rows_per_page";
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }else{
                $sql = "SELECT * FROM  posts WHERE title LIKE :searchTerm LIMIT $start , $rows_per_page";
                $stmt = $db->prepare($sql);
                $stmt->execute(
                    [':searchTerm' => '%' . $searchTerm . '%']
                );
            }

            $fetch = $stmt->fetchall(PDO::FETCH_ASSOC);

            if(!empty($fetch)){
                $jobs=[];
                foreach($fetch as $row){
                    $job = [
                        'postid' => $row['id'],
                        'adminid' => $row['admin_id'],
                        'title' => $row['title'],
                        'company' => $row['company'],
                        'location' => $row['location'],
                        'type' => $row['type'],
                        'description' => $row['description'],
                        'salary' => $row['salary'] === null ?  "No Salary Info" : ($row['salary']),
                        'place'=> $row['place'] === null ?  "No Work place Info" : ($row['place']),
                        'status' => ($row['is_active'])
                    ];
                
                    $jobs[]= $job;
    
                }                
                http_response_code(200);
                echo json_encode([  "success" => true, 
                                    "message" => "Offers found !", 
                                    "data" => $jobs ]);
            }else{
                http_response_code(200);
                echo json_encode([  "success" => true, 
                                    "message" => "No offers found !", 
                                    "data" => []]);
            }


        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !", "debug" => $e]);
        }
    }

    function createPost($db, $adminid, $title, $company, $location, $type, $desciption, $salary, $place){
        try{
                $table = "posts" ; 
                $sql = "INSERT INTO "  . $table . "(admin_id, title, company, location,type,description, salary, is_active, place) VALUES (:admin_id , :title, :company , :location, :type , :description , :salary, :is_active)";

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':admin_id', $adminid);
                $stmt->bindValue(':title', $title);
                $stmt->bindValue(':company', $company);
                $stmt->bindValue(':location', $location);
                $stmt->bindValue(':type', $type);
                $stmt->bindValue(':description', $desciption);
                $stmt->bindValue(':salary', $salary);
                $stmt->bindValue(':is_active', "1");
                $stmt->bindValue(':place', $place);

                $stmt->execute();

                http_response_code(200);
                echo json_encode([  "success" => true, "message" => "Post Created Successfully !"]);
        }catch(PDOException $e){
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !"]);
        }
    }

    function editPost($db, $id, $title, $comapny, $location, $type, $desciption, $salary, $place, $status){
        try{
            $sql = "UPDATE posts
                    SET title = :title,
                        company = :company,
                        location = :location,
                        type = :type,
                        description = :description,
                        salary = :salary,
                        is_active = :is_active,
                        place = :place
                    WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindValue('id', $id);
            $stmt->bindValue('title', $title);
            $stmt->bindValue('company', $comapny);
            $stmt->bindValue('location', $location);
            $stmt->bindValue('type', $type);
            $stmt->bindValue('description', $desciption);
            $stmt->bindValue('is_active', $status);
            $stmt->bindValue('salary', $salary);
            $stmt->bindValue('place', $place);
            
            $stmt->execute();

            http_response_code(200);
            echo json_encode([  "success" => true, "message" => "Post Edited Successfully !"]);

        }catch(PDOException $e){
            error_log($e);
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !", "error" => $e]);
        }
    }

    function removePost($db, $postid){
        try{
            $sql = "DELETE FROM posts WHERE id = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindValue('id', $postid);

            $stmt->execute();

            http_response_code(200);
            echo json_encode([  "success" => true, "message" => "Post Deleted Successfully !"]);
        }catch(PDOException $e){
            error_log($e);
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Request Issued !", "error" => $e]);
        }
    }

?>