<?php 

    include 'connect.php'; 


    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }
        
    $admin_id = $_SESSION['user_id'] ?? null;


    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $form_type = $_POST['form_type'];
        try{  
            function jobinfo(){
                $jobtitle = htmlentities($_POST['Title']);
                $jobcompany = htmlentities($_POST['Company']);
                $joblocation = htmlentities($_POST['Location']);
                $jobtype = htmlentities($_POST['JobType']);
                $jobdescription = htmlentities($_POST['Description']);
                $jobsalry = htmlentities($_POST['Salary']);
                $jobplace = htmlentities($_POST['JobPlace']);
                $is_active = "1";
                if (
                empty($jobtitle) ||
                empty($jobcompany) ||
                empty($joblocation) ||
                empty($jobtype) ||
                empty($jobdescription) ||
                // empty($jobsalry) ||
                empty($jobplace)
                ) {
                    throw new Exception("All fields are required.");
                }

                return [$jobtitle, $jobcompany,$joblocation,$jobtype,$jobdescription,$jobsalry,$jobplace,$is_active];

            }   
            
            if($form_type == "NewJob"){
                [$jobtitle, $jobcompany,$joblocation,$jobtype,$jobdescription,$jobsalry,$jobplace,$is_active] = jobinfo();
                if(newpost($db ,$admin_id ,$jobtitle ,$jobcompany ,$joblocation ,$jobtype ,$jobdescription ,$jobsalry ,$jobplace, $is_active)){
                    $_SESSION['message'] = "Job created successfully!";
                    $_SESSION['message_type'] = "success";
                }else{
                    $_SESSION['message'] = "Job creation failed!";
                    $_SESSION['message_type'] = "error";
                }
                $redirect = $_POST['redirect_to'] ?? '/profile';
                header("Location: $redirect");
                exit;
            }
            elseif($form_type == "EditJob"){
                $jobid= htmlentities($_POST['PostId']);
                [$jobtitle, $jobcompany,$joblocation,$jobtype,$jobdescription,$jobsalry,$jobplace,$is_active] = jobinfo();
                if(editpost($db ,$jobid ,$admin_id ,$jobtitle ,$jobcompany ,$joblocation ,$jobtype ,$jobdescription ,$jobsalry ,$jobplace, $is_active)){
                    $_SESSION['message'] = "Job edited successfully!";
                    $_SESSION['message_type'] = "success";
                }else{
                    $_SESSION['message'] = "Job edition failed!";
                    $_SESSION['message_type'] = "error";
                }

                $redirect = $_POST['redirect_to'] ?? '/profile';
                header("Location: $redirect");
                exit;
            }
            elseif($form_type == "DeleteJob"){
                $jobid= htmlentities($_POST['PostId']);
                $confirmation= htmlentities($_POST['ConfirmDelete']);
                if($confirmation == "CONFIRM" OR $confirmation == "confirm"){
                    if(deletepost($db, $jobid)){
                        $_SESSION['message'] = "Job deleted successfully!";
                        $_SESSION['message_type'] = "success";
                    }
                    else{
                        $_SESSION['message'] = "Job deletion failed!";
                        $_SESSION['message_type'] = "error";
                    }
                }else{
                        $_SESSION['message'] = "Confirmation message is wrong!";
                        $_SESSION['message_type'] = "error";
                }
                $redirect = $_POST['redirect_to'] ?? '/profile';
                header("Location: $redirect");
                exit;
            }

            exit;
        }catch (Exception $e){
            echo  $e->getMessage();
        }

    }

    function newpost($db, $admin_id, $jobtitle ,$jobcompany ,$joblocation ,$jobtype ,$jobdescription ,$jobsalry ,$jobplace, $is_active) {
        try {
                $sql = "INSERT INTO posts (admin_id, title, company, location,type,description, salary, is_active) VALUES (:admin_id , :title, :company , :location, :type , :description , :salary, :is_active)";
                session_start();

                $stmt = $db->prepare($sql);
                $stmt->bindValue(':admin_id', $admin_id);
                $stmt->bindValue(':title', $jobtitle);
                $stmt->bindValue(':company', $jobcompany);
                $stmt->bindValue(':location', $joblocation);
                $stmt->bindValue(':type', $jobtype);
                $stmt->bindValue(':description', $jobdescription);
                $stmt->bindValue(':salary', $jobsalry);
                $stmt->bindValue(':is_active', $is_active);

                $stmt->execute();
                return true;

        }catch (PDOException $e){
                return false;
        }
        
    }

    function getpost($db ,$start , $rows_per_page, $searchTerm){
        try{ 
            if(isset($_GET['page-nr'])){
                $page = $_GET['page-nr'] - 1 ;
                $start = $page * $rows_per_page;
            }
            
            $table ="posts";
            if($searchTerm){
                $sql = "SELECT * FROM  $table  LIMIT $start , $rows_per_page  ";
                $stmt = $db->prepare($sql);
                $stmt->execute();
            }else{
                $sql = "SELECT * FROM  $table  LIMIT $start , $rows_per_page WHERE title LIKE '%:searchTerm%' ";
                $stmt = $db->prepare($sql);
                $stmt->execute(
                    [':searchTerm' => $searchTerm]
                );
            }

            $fetch = $stmt->fetchall(PDO::FETCH_ASSOC);

            if (empty($fetch) ){
                exit;
            }
            $jobs=[];
            foreach($fetch as $row){
                $job = [
                    'postid' => htmlentities($row['id']),
                    'admin_id' => htmlentities($row['admin_id']),
                    'title' => htmlentities($row['title']),
                    'company' => htmlentities($row['company']),
                    'location' => htmlentities($row['location']),
                    'type' => htmlentities($row['type']),
                    'description' => htmlentities($row['description']),
                    'salary' => ($row['salary'] === null) ?  "No Salary Info" : htmlentities($row['salary']),
                    'place'=> ($row['place'] === null) ?  "No Work place Info" : htmlentities($row['place']),
                    'is_active' => htmlentities($row['is_active'])
                ];
            
                $jobs[]= $job;

            }
            return $jobs;


        }catch(PDOException $e){
            echo"An error has occured!";
        }
    }

    function editpost($db ,$postid , $admin_id ,$jobtitle ,$jobcompany ,$joblocation ,$jobtype ,$jobdescription ,$jobsalry ,$jobplace, $is_active) {
         try {
                $table = "posts" ; 
                $sql = "UPDATE $table
                        SET admin_id = :admin_id, 
                            title = :title, 
                            company = :company, 
                            location = :location,
                            type = :type,
                            description = :description, 
                            salary = :salary, 
                            is_active = :is_active
                        WHERE id= :postid";


                $stmt = $db->prepare($sql);
                $stmt->bindValue(':postid', $postid);
                $stmt->bindValue(':admin_id', $admin_id);
                $stmt->bindValue(':title', $jobtitle);
                $stmt->bindValue(':company', $jobcompany);
                $stmt->bindValue(':location', $joblocation);
                $stmt->bindValue(':type', $jobtype);
                $stmt->bindValue(':description', $jobdescription);
                $stmt->bindValue(':salary', $jobsalry);
                $stmt->bindValue(':is_active', $is_active);
                $stmt->execute();
                return true ;

        }catch (PDOException $e){
            echo "An error has occured!" . $e;
            return false;
        }

    }

    function deletepost($db, $postid){
        try{
            $table = "posts" ; 
            $sql = "DELETE FROM $table WHERE id = :postid";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':postid', $postid);
            $stmt->execute();

            return true ;
        }catch (PDOException $e){
            echo "An error has occured!" . $e;
            return false;
        }

    }

    function id_to_user($db, $adminid){
        try{
            $table = "users";
            $sql = "SELECT * FROM $table WHERE id= :adminid";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':adminid' , $adminid);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

            if (empty($fetch) ){
                echo $fetch;
                echo "No one exits with this ID !";
                exit;
            }
            else{
                $name = htmlentities($fetch['username']);
                echo $name;
            }

        }catch(PDOException $e){
            echo "An error has occured $e" ;
            return false;
        }
    }

    function getpsotbyid($db , $postid){
        try{
            $table = "posts"; 
            $sql = "SELECT * FROM $table WHERE id = :postid";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':postid' , $postid);
            $stmt->execute();
            $fetch = $stmt->fetch(PDO::FETCH_ASSOC);

            return $fetch;
    
        }catch(PDOException $e){
            echo "An error has occured $e" ;
            return false;

        }
    }
    
?>