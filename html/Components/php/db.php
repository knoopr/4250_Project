<?php
 $pdo = null;
try
{
    $pdo = new PDO('mysql:host=localhost;dbname=HOUSING', 'insertion', 'insertionpass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    $error = 'Unable to connect to the database server. <br>' . $e->getMessage();
    echo $error;
    exit();
}
?>
