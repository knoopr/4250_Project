<?php
    
    
    include '_/components/php/db.php';
    
    
    $fileOutput = "test";
    $Buying = Download_MLS();
    //Couldn'r return 0 as an int because no matter what I did it would recognize the string as a 0
    if ($Buying != "0"){
        Insert_MLS($Buying);
    }
    $Renting = Download_Rental();
    if ($Renting != "0"){
        Insert_Rental($Renting);
    }
    
    
    
    
    
    function Download_MLS(){
        //Checking if the next file in online yet.
        $nextFile = fopen("NextMLS.txt", "r");
        $fileOutput = fread($nextFile, filesize("NextMLS.txt"));
        $year = "20".substr($fileOutput, 2, 2);
        fclose($nextFile);
        
        
        $query = "http://www.torontorealestateboard.com/market_news/market_watch/$year/$fileOutput";
        $results = @file_get_contents($query);
        if ($results === false){
            return "0";
        }
        
        //Writing to the pdf file
        $resultFile = fopen($fileOutput,"w");
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
        $resultFile = fopen($fileOutput,"w");
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
    
    
    function Insert_MLS($fileName){
        $output = shell_exec("./ParseMLS.py $fileName");
        $split = explode("\n", $output);
        $today = date(DATE_RFC2822, 'America/Toronto' );
        
        
        // Setting up pdo statements
        $sql = "INSERT INTO db10263_should.AVERAGE_COST
        SELECT NULL, :date, sector.id AS sector, housing_type.id AS type, :average
        FROM sector, housing_type
        WHERE sector.name =':sector'
        AND housing_type.name =':type'";
        
        
        //Binding params; in each loop as the values change means we dont have to update
        $insert = $pdo->prepare($sql);
        $insert->bindParam(':date', $today);
        $insert->bindParam(':average', $value[0]); //change to $value[1] for median
        $insert->bindParam(':sector', $key);
        $insert->bindParam(':type', $housingType);
        
        
        exit;
        
        
        foreach ($split as $housingValues){
            $json = json_decode($housingValues, true);
            $housingType = $json["type"];
            
            if ($json != null){
                foreach($json as $key=>$value){
                    if($key != "type"){
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
        $sql = "INSERT INTO db10263_should.AVERAGE_COST
        SELECT NULL, :date, sector.id AS sector, housing_type.id AS type, :average
        FROM sector, housing_type
        WHERE sector.name =':sector'
        AND housing_type.name =':type'";
        
        
        //Binding params; in each loop as the values change means we dont have to update
        $insert = $pdo->prepare($sql);
        $insert->bindParam(':date', $today);
        $insert->bindParam(':average', $price);
        $insert->bindParam(':sector', $key);
        $insert->bindParam(':type', $rentalType);
        
        
        foreach ($split as $housingValues){
            $json = json_decode($housingValues, true);
            $housingType = $json["type"];
            
            
            if ($json != null){
                foreach($json as $key=>$value){
                    if($key != "type"){
                        $price = $value[0]
                        $rentalType = "Bachelor ".$housingType
                        $insert->execute();
                        
                        $price = $value[1]
                        $rentalType = "One-Bedroom ".$housingType
                        $insert->execute();
                        
                        $price = $value[2]
                        $rentalType = "Two-Bedroom ".$housingType
                        $insert->execute();
                        
                        $price = $value[3]
                        $rentalType = "Three-Bedroom ".$housingType
                        $insert->execute();
                    }
                }
            }
        }
    }
    ?>