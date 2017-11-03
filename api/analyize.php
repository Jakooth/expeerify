<?php

function analyzeIslandReportData ($link, $data, $recordId, $logId)
{
    
    /**
     * Selected specific one from a group.
     */
    if ($data['targetGroupName'] == 'tothemChoice') {
        
        switch ($data['targetDisplayName']) {
            case 'tothem':
                if ($data['interactionValue'] == 'math') {
                    $sql = "INSERT INTO records_result (record_id, sensed)
                            VALUES ($recordId, 1)
                            ON DUPLICATE KEY UPDATE sensed = sensed + 1;";
                } else 
                    if ($data['interactionValue'] == 'art') {
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
    }
    
    /**
     * Collected exact number or all from a group.
     * This interaction cannot be of type use.
     */
    
    if ($data['targetGroupName'] == 'flashlightAndMagnifier' ||
             $data['targetGroupName'] == 'scrollChoice' ||
             $data['targetGroupName'] == 'animalsChoice') {
        
        do {
            if ($data['interactionType'] == 'use') {
                break;
            }
            
            $sql = "SELECT * FROM records_log
                WHERE record_id = $recordId
                AND target_group_name = '{$data['targetGroupName']}'
                ORDER BY interaction_end_time;";
            
            $result = mysqli_query($link, $sql);
            $records = mysqli_num_rows($result);
            
            switch ($data['targetDisplayName']) {
                
                /**
                 * Welcome Scene
                 */
                
                case 'dolphin':
                case 'lion':
                case 'elephant':
                case 'monkey':
                    if ($records === 3) {
                        $sql = "INSERT INTO records_result (record_id, sensed)
                            VALUES ($recordId, 1)
                            ON DUPLICATE KEY UPDATE sensed = sensed + 1,
                                                    intuited = intuited - 1;";
                    } else 
                        if ($records === 1) {
                            $sql = "INSERT INTO records_result (record_id, intuited)
                                VALUES ($recordId, 1)
                                ON DUPLICATE KEY UPDATE intuited = intuited + 1;";
                        }
                    
                    break;
                
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
                    } else 
                        if ($records === 1) {
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
                    } else 
                        if ($records === 1) {
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
        } while (0);
    }
    
    /**
     * Last choice between several of a group.
     * Secondery crtierion could be type of action.
     */
    
    if ($data['targetGroupName'] == 'animalsChoice') {
        
        $sql = "SELECT * FROM records_log
                WHERE record_id = $recordId
                AND target_group_name = '{$data['targetGroupName']}'
                ORDER BY interaction_end_time DESC
                LIMIT 1;";
        
        $result = mysqli_query($link, $sql);
        $record = mysqli_fetch_assoc($result);
        
        do {
            if ($record['analyzed']) {
                break;
            }
            
            switch ($record['target_display_name']) {
                
                /**
                 * Welcome scene
                 */
                
                case 'dolphin':
                    if ($data['interactionType'] != 'use') {
                        return false;
                    }
                    
                    $sql = "INSERT INTO records_result (record_id, emotional, intuited)
                        VALUES ($recordId, 1, 1)
                        ON DUPLICATE KEY UPDATE emotional = emotional + 1, intuited = intuited + 1;";
                    
                    break;
                
                case 'elephant':
                    if ($data['interactionType'] != 'use') {
                        return false;
                    }
                    
                    $sql = "INSERT INTO records_result (record_id, logical, intuited)
                        VALUES ($recordId, 1, 1)
                        ON DUPLICATE KEY UPDATE logical = logical + 1, intuited = intuited + 1;";
                    
                    break;
                
                case 'lion':
                    if ($data['interactionType'] != 'use') {
                        return false;
                    }
                    
                    $sql = "INSERT INTO records_result (record_id, logical, sensed)
                        VALUES ($recordId, 1, 1)
                        ON DUPLICATE KEY UPDATE logical = logical + 1, sensed = sensed + 1;";
                    
                    break;
                
                case 'monkey':
                    if ($data['interactionType'] != 'use') {
                        return false;
                    }
                    
                    $sql = "INSERT INTO records_result (record_id, emotional, sensed)
                        VALUES ($recordId, 1, 1)
                        ON DUPLICATE KEY UPDATE emotional = emotional + 1, sensed = sensed + 1;";
                    
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
        } while (0);
    }
    
    /**
     * First choice between several of a group.
     * Secondery crtierion could be type of action.
     */
    
    if ($data['targetGroupName'] == 'mapOrFriendsChoice' ||
             $data['targetGroupName'] == 'pathChoice' ||
             $data['targetGroupName'] == 'fairyChoice' ||
             $data['targetGroupName'] == 'keyChoice' ||
             $data['targetGroupName'] == 'partyOrBoatChoice') {
        
        $sql = "SELECT * FROM records_log
                WHERE record_id = $recordId
                AND target_group_name = '{$data['targetGroupName']}'
                ORDER BY interaction_end_time ASC
                LIMIT 1;";
        
        $result = mysqli_query($link, $sql);
        $record = mysqli_fetch_assoc($result);
        
        do {
            if ($record['analyzed']) {
                break;
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
        } while (0);
    }
    
    return true;
}

?>
