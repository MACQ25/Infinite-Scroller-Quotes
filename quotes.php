<?php

    /**
     * I Mauricio Canul, 000881810, certify that this material is my original work. 
     * No other person's work has been used without suitable acknowledgment and 
     * I have not made my work available to anyone else.
     * @author Mauricio Canul
     * @version 1.2
     * @package COMP 10260 Assignment 4
    */

    /* 
        Line used to get the 'page' value from the GET superglobal (if it exists), 
        sets it to 0 if something went wrong, it also has filtering input in order
        to only get an int
    */
    $page = (isset($_GET['page'])) ? filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT): 0;
    // Value to define the maximum number of quotes that can be obtained, number can be changed in order to get more
    $limit = 20;
    // The calculation to define from which point of the DB we are going to start retrieving quotes from, so no quotes repeat
    $offset = max(($page - 1) * $limit, 0);

    // If function meant to check whether the code executes or not, it can only execute if the value of $page was set and is greater than 0
    if(is_int($page) && $page > 0){
        // A try catch in order to connect to our database through the use of a PDO object and appropriate credentials
        try{
            $dbh = new PDO("mysql:host=localhost;dbname=sa000881810", "sa000881810", "Sa_20030625");
        } catch (Exception $e){
            die("ERROR: Couldn't connect. {$e->getMessage()}");
        }
    
        // The command that we are to use in order to acquire our quotes
        $command = "SELECT quotes.quote_text, authors.author_name
        FROM quotes
        JOIN authors ON quotes.author_id = authors.author_id
        LIMIT :per_page
        OFFSET :offset 
        ";
    
        // We start to prepare our query (the appropriate command)
        $stmt = $dbh->prepare($command);
        // We bind one of the known parameters (number of quotes per page) of the command to its corresponding var
        $stmt->bindParam(':per_page', $limit, PDO::PARAM_INT);
        // We bind one of the known parameters (place on database from which to start counting) of the command to its corresponding var
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
        // We execute the given command
        $success = $stmt->execute();
        
        // We create an array, this will be populated with cards containing quotes from the query
        $cards = [];
        /*
            This while loop will then run through every row of the queried results and will only return
            false upon reaching the end, it is set to fetch each row as an associative array, which will
            help us in building strings in the way we want, such strings are being pushed into our cards 
            array
        */ 
        while($res = $stmt->fetch(PDO::FETCH_ASSOC)){
            // Never trust a DB! specially one you create yourself!!!
            // We filter the author_name value gotten from the database
            $filteredAuthor = filter_var($res['author_name'], FILTER_SANITIZE_SPECIAL_CHARS);
            // We filter the quote_text value gotten from the database
            $filteredQuote = filter_var($res['quote_text'], FILTER_SANITIZE_SPECIAL_CHARS);
            // The we push into our cards array a string value formated as html with the values above injected into it
            array_push($cards, 
            "<div class='card mb-3 a4card w-100'>
                    <div class='card-header'>
                        $filteredAuthor
                    </div>
                    <div class='card-body d-flex align-items-center'>
                        <p class='card-text w-100'>
                            $filteredQuote
                        </p>
                    </div>
            </div>"
            );    
        } 
        // Finally, we echo a JSON encoded array of strings, which will be cards containing our retrieved quotes
        echo json_encode($cards);
    }

?>