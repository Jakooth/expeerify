<?php

function analyzeIslandReportData($link, $data, $recordId, $logId)
{
    switch ($data['targetGroupName']) {
        
        /**
         * First choice between several of a group.
         */
        
        case 'mapAndFriendsChoice':
        case 'pathChoice':
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
                            VALUES ({$record['record_id']}, 1)
                            ON DUPLICATE KEY UPDATE logical = logical + 1;";
                    
                    break;
                
                case 'friends':
                    $sql = "INSERT INTO records_result (record_id, emotional)
                            VALUES ({$record['record_id']}, 1)
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
