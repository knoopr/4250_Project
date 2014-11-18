<?php
    require '/var/www/html/Components/php/db.php';
    if (isset($_GET['type'])){
        $housing = $_GET['type'];
        $query = "SELECT SECTOR.NAME, AVERAGE_COST
                    FROM (
                        SELECT SECTOR_ID ,AVERAGE_COST
                        FROM HOUSING.COST_ENTRIES
                        WHERE MONTH(INSERTION_DATE)=MONTH(CURRENT_DATE) AND
                        HOUSING_ID=(SELECT ID FROM HOUSING_TYPE WHERE NAME=:housingName)
                    ) AS TEMP
                    INNER JOIN SECTOR
                    ON SECTOR.ID=TEMP.SECTOR_ID";
        $average = $pdo->prepare($query);
        $average->bindParam(':housingName', $housing);
        $average->execute();
    
        foreach ($average->fetchall(PDO::FETCH_ASSOC) as $row){
            $JSONreturn[$row['NAME']] = $row['AVERAGE_COST'];
        }
        $JSONreturn['MAX'] = max($JSONreturn);
        $JSONreturn['MIN'] = min($JSONreturn);
        echo json_encode($JSONreturn);
        
    }
?>