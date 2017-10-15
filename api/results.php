<?php
$host = 'localhost';
$id = 'root';
$password = 'Secret123!';
$db = 'expeerify';

$link = mysqli_connect($host, $id, $password, $db);

if (getenv('REQUEST_METHOD') == 'GET') {
    if (isset($_GET['id'])) {
        $sql = "SELECT * FROM results
                ORDER BY result_id DESC
                LIMIT 1;";
    } else {
        $sql = "SELECT * FROM results
                ORDER BY result_id DESC;";
    }
    
    $query = mysqli_query($link, $sql);
    
    $results = array();
    
    while ($result = mysqli_fetch_assoc($query)) {
        $results[] = $result;
    }
    
    echo json_encode(array(
        'response' => $results
    ));
    
    exit();
}

if (getenv('REQUEST_METHOD') == 'POST') {
    $request = file_get_contents("php://input");
    $requestData = json_decode($request, true);
    
    $resultId = isset($requestData['resultId']) ? $requestData['resultId'] > 0 ? $requestData['resultId'] : false : false;
    $endTime = isset($requestData['endTime']) ? $requestData['endTime'] ==
    'Jan 1, 1, 12:00:00 AM' ? "null" : "'{$requestData ['endTime']}'" : "null";
    
    if (isset($requestData)) {
        if ($resultId) {
            $sql = "UPDATE results
                    SET `end` = $endTime,
                         is_light_turned_on = {$requestData['isLightTurnedOn']},
                         tried_open_without_key = {$requestData['triedOpenWithoutKey']}
                    WHERE result_id = {$requestData['resultId']};";
            
            $result = mysqli_query($link, $sql);
            $last = $requestData['resultId'];
        } else {
            $sql = "INSERT INTO results (user_id, `start`, is_light_turned_on, tried_open_without_key)
                    VALUES ( {$requestData['userId']},
                            '{$requestData['startTime']}',
                              -1,
                              -1);";
            
            $result = mysqli_query($link, $sql);
            $last = mysqli_insert_id($link);
        }
        
        $sql = "SELECT * FROM results
                WHERE result_id = $last
                LIMIT 1;";
        
        $result = mysqli_query($link, $sql);
        $response = mysqli_fetch_assoc($result);
        
        echo json_encode(
            array(
                'response' => $response
            ));
        
        exit();
    } else {
        header('HTTP/1.1 401 Unauthorized');
        
        $response['message'] = 'Unauthorized.';
        
        echo json_encode(
            array(
                'response' => $response
            ));
        
        exit();
    }
}
?>