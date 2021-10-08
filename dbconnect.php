<?php

// Factory function to generate a specific PDO instance. Any
// problems encountered in establishing the connection are
// reported here.
//
// Include this file in other scripts as necessary
function get_pdo_instance()
{
    $hostname = 'localhost';
    $database = 'airline';
    $username = 'chao';
    $password = '123456';

    $link = "mysql:host=$hostname;dbname=$database";
    try {
        // Get a database connection instance
        $pdo = new PDO($link, $username, $password);
    } catch (PDOException $e) {
        echo "<b>Encountered PDO problem</b><br>{$e->getMessage()}";
    } catch (Exception $e) {
        echo "<b>Encountered general problem</b><br>{$e->getMessage()}";
    }
    return $pdo;
}


