<?php

function analyzeIslandReportData($link, $data, $recordId, $logId)
{
    switch ($data['targetGroupName']) {
        
        /**
         * Selected specific one from a group.
         */
        
        case 'tothemChoice':
            switch ($data['targetDisplayName']) {
                case 'tothem':
                    if ($data['interactionValue'] == 'math') {
                        $sql = "INSERT INTO records_result (record_id, sensed)
                                VALUES ($recordId, 1)
                                ON DUPLICATE KEY UPDATE sensed = sensed + 1;";
                    } else if ($data['interactionValue'] == 'art') {
                        $sql = "INSERT INTO records_result (record_id, intuited)
                                VALUES ($recordId, 1)
                                ON DUPLICATE KEY UPDATE intuited = intuited + 1;";
                    }
                    
                    break;
            }
            
            $result = mysqli_query($link, $sql);
            
            $sql = "UPDATE records_log
                    SET analyzed = 1
                    WHERE record_log_id = $logId;";
            
            $result = mysqli_query($link, $sql);
            
            return true;
            break;
        
        /**
         * Collected exact number or all from a group.
         */
        
        case 'flashlightAndMagnifier':
        case 'scrollChoice':
            $sql = "SELECT * FROM records_log
                    WHERE record_id = $recordId
                    AND target_group_name = '{$data['targetGroupName']}'
                    ORDER BY interaction_end_time;";
            
            $result = mysqli_query($link, $sql);
            $records = mysqli_num_rows($result);
            
            switch ($data['targetDisplayName']) {
                
                /**
                 * Scene 3
                 */
                
                case 'flashlight':
                case 'magnifier':
                    if ($records === 2) {
                        $sql = "INSERT INTO records_result (record_id, sensed)
                                VALUES ($recordId, 1)
                                ON DUPLICATE KEY UPDATE sensed = sensed + 1, 
                                                        intuited = intuited - 1;";
                    } else if ($records === 1) {
                        $sql = "INSERT INTO records_result (record_id, intuited)
                                VALUES ($recordId, 1)
                                ON DUPLICATE KEY UPDATE intuited = intuited + 1;";
                    }
                    
                    break;
                case 'scroll':
                    if ($records === 4) {
                        $sql = "INSERT INTO records_result (record_id, sensed)
                                VALUES ($recordId, 1)
                                ON DUPLICATE KEY UPDATE sensed = sensed + 1,
                                                        intuited = intuited - 1;";
                    } else if ($records === 1) {
                        $sql = "INSERT INTO records_result (record_id, intuited)
                                VALUES ($recordId, 1)
                                ON DUPLICATE KEY UPDATE intuited = intuited + 1;";
                    }
                    
                    break;
                
                default:
                    return false;
                    break;
            }
            
            $result = mysqli_query($link, $sql);
            
            $sql = "UPDATE records_log
                    SET analyzed = 1
                    WHERE record_log_id = $logId;";
            
            $result = mysqli_query($link, $sql);
            
            return true;
            break;
        
        /**
         * First choice between several of a group.
         */
        
        case 'mapOrFriendsChoice':
        case 'pathChoice':
        case 'fairyChoice':
        case 'keyChoice':
        case 'partyOrBoatChoice':
            $sql = "SELECT * FROM records_log
                    WHERE record_id = $recordId
                    AND target_group_name = '{$data['targetGroupName']}'
                    ORDER BY interaction_end_time
                    LIMIT 1;";
            
            $result = mysqli_query($link, $sql);
            $record = mysqli_fetch_assoc($result);
            
            if ($record['analyzed']) {
                return false;
            }
            
            switch ($record['target_display_name']) {
                
                /**
                 * Scene 1
                 */
                
                case 'map':
                    $sql = "INSERT INTO records_result (record_id, logical)
                            VALUES ($recordId, 1)
                            ON DUPLICATE KEY UPDATE logical = logical + 1;";
                    
                    break;
                
                case 'friends':
                    $sql = "INSERT INTO records_result (record_id, emotional)
                            VALUES ($recordId, 1)
                            ON DUPLICATE KEY UPDATE emotional = emotional + 1;";
                    
                    break;
                
                /**
                 * Scene 2
                 */
                
                case 'darkPathEnd':
                    $sql = "INSERT INTO records_result (record_id, logical)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE logical = logical + 1;";
                    
                    break;
                
                case 'sunnyPathEnd':
                    $sql = "INSERT INTO records_result (record_id, emotional)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE emotional = emotional + 1;";
                    
                    break;
                
                /**
                 * Scene 4
                 */
                
                case 'book':
                    $sql = "INSERT INTO records_result (record_id, sensed)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE logical = sensed + 1;";
                    
                    break;
                
                case 'flower':
                    $sql = "INSERT INTO records_result (record_id, intuited)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE emotional = intuited + 1;";
                    
                    break;
                
                /**
                 * Scene 5
                 */
                    
                case 'key':
                    $sql = "INSERT INTO records_result (record_id, sensed)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE logical = sensed + 1;";
                    
                    break;
                    
                case 'magicKey':
                    $sql = "INSERT INTO records_result (record_id, intuited)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE emotional = intuited + 1;";
                    
                    break;
                
                /**
                 * Scene 6
                 */
                    
                case 'boat':
                    $sql = "INSERT INTO records_result (record_id, logical)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE logical = logical + 1;";
                    
                    break;
                    
                case 'party':
                    $sql = "INSERT INTO records_result (record_id, emotional)
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE emotional = emotional + 1;";
                    
                    break;
                    
                default:
                    return false;
                    break;
            }
            
            $result = mysqli_query($link, $sql);
            
            $sql = "UPDATE records_log
                    SET analyzed = 1
                    WHERE record_log_id = $logId;";
            
            $result = mysqli_query($link, $sql);
            
            return true;
            break;
    }
}

?>
