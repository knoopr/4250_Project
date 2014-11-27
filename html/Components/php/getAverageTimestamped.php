<?php
    require '/var/www/html/Components/php/db.php';
    if (isset($_GET['sector'])){
        $sector = $_GET['sector'];
        $housing = $_GET['type'];
        $query = "SELECT AVERAGE_COST, INSERTION_DATE FROM HOUSING.COST_ENTRIES WHERE SECTOR_ID=(SELECT ID FROM SECTOR WHERE NAME=:sectorName) AND HOUSING_ID=(SELECT ID FROM HOUSING_TYPE WHERE NAME=:housingName)";
        
        $average = $pdo->prepare($query);
        $average->bindParam(':sectorName', $sector);
        $average->bindParam(':housingName', $housing);
        $average->execute();
        
        foreach ($average->fetchall(PDO::FETCH_ASSOC) as $row){
            $JSONreturn[$row['INSERTION_DATE']] = $row['AVERAGE_COST'];
        }
        $JSONreturn['MAX'] = max($JSONreturn);
        $JSONreturn['MIN'] = min($JSONreturn);
        echo json_encode($JSONreturn);
    }

?>