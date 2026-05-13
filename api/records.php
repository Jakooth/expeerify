<?php
include ('analyize.php');

$host = 'localhost';
$id = 'root';
$password = 'Secret123!';
$db = 'expeerify';

$link = mysqli_connect($host, $id, $password, $db);

if (getenv('REQUEST_METHOD') == 'GET') {
    if (isset($_GET['record'])) {
        $sql = "SELECT * FROM records
                ORDER BY record_id DESC
                LIMIT 1;";
    } else 
        if (isset($_GET['result'])) {
            $sql = "SELECT * FROM records_result
                ORDER BY record_id DESC
                LIMIT 1;";
        } else {
            $sql = "SELECT * FROM records
                ORDER BY record_id DESC;";
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
    
    $recordId = isset($requestData['recordId']) ? $requestData['recordId'] > 0 ? $requestData['recordId'] : false : false;
    $startTime = DateTime::createFromFormat('M m, Y, H:i:s A',
            $requestData['interactionStartTime'])->format('Y-m-d H:i:s');
    $endTime = DateTime::createFromFormat('M m, Y, H:i:s A',
            $requestData['interactionEndTime'])->format('Y-m-d H:i:s');
    
    if (isset($requestData)) {
        if ($recordId) {
            $last_record = $requestData['recordId'];
        } else {
            $sql = "INSERT INTO records (user_id, session_id, test_id)
                    VALUES ({$requestData['userId']},
                            {$requestData['sessionId']},
                            {$requestData['testId']});";
            
            $result = mysqli_query($link, $sql);
            $last_record = mysqli_insert_id($link);
        }
        
        $sql = "INSERT INTO records_log (record_id,
                                         target_display_name,
                                         target_group_name,
                                         interaction_value,
                                         interaction_type,
                                         interaction_start_time,
                                         interaction_end_time)
                VALUES ($last_record,
                        '{$requestData['targetDisplayName']}',
                        '{$requestData['targetGroupName']}',
                        '{$requestData['interactionValue']}',
                        '{$requestData['interactionType']}',
                        '$startTime',
                        '$endTime');";
        
        $result = mysqli_query($link, $sql);
        $last_log = mysqli_insert_id($link);
        
        $sql = "SELECT * FROM records
                WHERE record_id = $last_record
                LIMIT 1;";
        
        $result = mysqli_query($link, $sql);
        $response = mysqli_fetch_assoc($result);
        
        $isAnalyzed = analyzeIslandReportData($link, $requestData, $last_record,
                $last_log);
        
        echo json_encode(array(
                'response' => $response
        ));
        
        exit();
    } else {
        header('HTTP/1.1 401 Unauthorized');
        
        $response['message'] = 'Unauthorized.';
        
        echo json_encode(array(
                'response' => $response
        ));
        
        exit();
    }
}
?>