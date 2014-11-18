<?php
    require '/var/www/html/Components/php/db.php';
    if (isset($_GET['sector'])){
        $sector = $_GET['sector'];
        $housing = $_GET['type'];
        $query = "SELECT AVERAGE_COST FROM HOUSING.COST_ENTRIES WHERE SECTOR_ID=(SELECT ID FROM SECTOR WHERE NAME=:sectorName) AND MONTH(INSERTION_DATE)=MONTH(CURRENT_DATE) AND HOUSING_ID=(SELECT ID FROM HOUSING_TYPE WHERE NAME=:housingName)";
        
        $average = $pdo->prepare($query);
        $average->bindParam(':sectorName', $sector);
        $average->bindParam(':housingName', $housing);
        $average->execute();
        foreach ($average->fetchall(PDO::FETCH_FUNC) as $row){
            echo $row["AVERAGE_COST"];
        }
    }

?>