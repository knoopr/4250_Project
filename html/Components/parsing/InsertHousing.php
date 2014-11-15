<?php
    //require '/var/www/html/Components/php/db.php';
    
    
    if (sizeof($argv) < 2){
        $Buying = Download_MLS();
        echo "TEST";
        //Couldn'r return 0 as an int because no matter what I did it would recognize the string as a 0
        if ($Buying != "0"){
            echo "test";
            //Insert_MLS("../data/".$Buying, $pdo);
        }
        /*$Renting = Download_Rental();
        if ($Renting != "0"){
            Insert_Rental($Renting);
        }*/
    }
    elseif (sizeof($argv) == 3){
        if ($argv[1] = "-b"){
            Insert_MLS($argv[2], $pdo);
        }
    }
    
    
    
    
    
    function Download_MLS(){
        //Checking if the next file in online yet.
        $nextFile = fopen("NextMLS.txt", "r");
        $fileOutput = fread($nextFile, filesize("NextMLS.txt"));
        $year = "20".substr($fileOutput, 2, 2);
        fclose($nextFile);
        
        //$query = "http://107.170.110.165/Components/data/districtMaps/C01.json";
        $query = "http://www.torontorealestateboard.com/market_news/market_watch/$year/$fileOutput";
        $results = @file_get_contents($query);
        if ($results === false){
            echo $results;
            exit();
            return "0";
        }
        
        //Writing to the pdf file
        $resultFile = fopen("../data/".$fileOutput ,"w");
        fwrite($resultFile, $results);
        fclose($resultFile);
        
        //Writing to the file stating next file to be read
        $month = substr($fileOutput,4,2);
        $month += 1;
        
        
        $nextFile = fopen("NextMLS.txt", "w");
        if ($month > 12){
            $month = 1;
            $year += 1;
        }
        fwrite($nextFile, "mw".substr($year, 2, 2).sprintf('%02d',$month).".pdf");
        fclose($nextFile);
        return $fileOutput;
    }
    
    
    
    function Download_Rental(){
        //Checking if the next file in online yet.
        $nextFile = fopen("NextRental.txt", "r");
        $fileOutput = fread($nextFile, filesize("NextRental.txt"));
        $year = substr($fileOutput, -8, 4);
        fclose($nextFile);
        
        
        $query = "http://www.torontorealestateboard.com/market_news/rental_reports/pdf/$fileOutput";
        $results = @file_get_contents($query);
        if ($results === false){
            return "0";
        }
        
        //Writing to the pdf file
        $resultFile = fopen("../data/".$fileOutput,"w");
        fwrite($resultFile, $results);
        fclose($resultFile);
        
        //Writing to the file stating next file to be read
        $quarter = substr($fileOutput, -11, 2);
        $quarter = $quarter[1]+1;
        $quarter = "Q".$quarter;
        $nextFile = fopen("NextRental.txt", "w");
        if ($quarter == "Q5"){
            $quarter = "Q1";
            $year += 1;
        }
        fwrite($nextFile, "rental_report_".$quarter."-".$year.".pdf");
        fclose($nextFile);
        return $fileOutput;
    }
    
    
    function Insert_MLS($fileName, $pdo){
        $output = shell_exec("./ParseMLS.py $fileName");
        $split = explode("\n", $output);
        
        
        // Setting up pdo statements
        $sql = "INSERT INTO HOUSING.COST_ENTRIES
        SELECT NULL, NULL, SECTOR.ID AS SECTOR_ID, HOUSING_TYPE.id AS HOUSING_ID, :average, :median
        FROM SECTOR, HOUSING_TYPE
        WHERE SECTOR.NAME =:sector
        AND HOUSING_TYPE.NAME =:type";
        
        //Binding params; in each loop as the values change means we dont have to update
        $insert = $pdo->prepare($sql);
        $insert->bindParam(':sector', $key, PDO::PARAM_STR);
        $insert->bindParam(':type', $housingType, PDO::PARAM_STR);
        
        foreach ($split as $housingValues){
            $json = json_decode($housingValues, true);
            $housingType = $json["type"];
            
            if ($json != null){
                foreach($json as $key=>$value){
                    if($key != "type"){
                        $insert->bindParam(':average', $value[0], PDO::PARAM_INT);
                        $insert->bindParam(':median', $value[1], PDO::PARAM_INT);
                        $insert->execute();
                    }
                }
            }
        }
    }
    
    
    
    function Insert_Rental($fileName){
        $output = shell_exec("./ParseRental.py $fileName");
        $split = explode("\n", $output);
        
        // Setting up pdo statements
        $sql = "INSERT INTO HOUSING.COST_ENTRIES
        SELECT NULL, NULL, SECTOR.ID AS SECTOR_ID, HOUSING_TYPE.id AS HOUSING_ID, :average
        FROM SECTOR, HOUSING_TYPE
        WHERE SECTOR.NAME =':sector'
        AND HOUSING_TYPE.TYPE =':type'";
        
        
        //Binding params; in each loop as the values change means we dont have to update
        $insert = $pdo->prepare($sql);
        $insert->bindParam(':average', $price);
        $insert->bindParam(':sector', $key);
        $insert->bindParam(':type', $rentalType);
        
        
        foreach ($split as $housingValues){
            $json = json_decode($housingValues, true);
            $housingType = $json["type"];
            
            
            if ($json != null){
                foreach($json as $key=>$value){
                    if($key != "type"){
                        $price = $value[0];
                        $rentalType = "BACHELOR ".$housingType;
                        $insert->execute();
                        
                        $price = $value[1];
                        $rentalType = "ONE-BEDROOM ".$housingType;
                        $insert->execute();
                        
                        $price = $value[2];
                        $rentalType = "TWO-BEDROOM ".$housingType;
                        $insert->execute();
                        
                        $price = $value[3];
                        $rentalType = "THREE-BEDROOM ".$housingType;
                        $insert->execute();
                    }
                }
            }
        }
    }
    ?>