<?php

    /**
     * I Mauricio Canul, 000881810, certify that this material is my original work. 
     * No other person's work has been used without suitable acknowledgment and 
     * I have not made my work available to anyone else.
     * @author Mauricio Canul
     * @version 1.4
     * @package COMP 10260 Assignment 4
    */

    /* 
        Line used to get the 'page' value from the GET superglobal (if it exists), 
        sets it to 0 if something went wrong, it also has filtering input in order
        to only get an int
    */
    $pageEntry = (isset($_GET['page'])) ? filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT): 0;
    // Value to define the maximum number of quotes that can be obtained, number can be changed in order to get more

    /*
     * Hello teacher, just letting you know, found a weird bug on the client side where it is sending 1, then 3 times 2, then 5 times 6
     * as variables in the GET superglobal, I could not find a way to actually solve getting that and I do not know if the following is
     * "within bounds" or "on the spirit" of this assignment, but now I only receive the get param and check that it is correctly made
     * and not the number 0, in order to not run the rest of the code in the case that something was messed with in the client side
     * 
     * However, this came with the cost of now calculating the page number on the server side and allocating the value to a SESSION
     * variable, hope this does not alter my mark and hope that you understand that the alteration was just made to make it more stable
     * (Previous version skipped pages and had duplicates) current version goes up by 1 page at a time no matter what
     */

     /**
      * Post submission update:
      * Teachers acknowledged the last comment as accurate and noted it as a bug with the given starter code, as this note was only read after
      * the final due date no corrected front end code was ever submitted. However, teachers congratulated me on noticing the bug ^-^
      */

    // We start a user session
    session_start();

    // We check if we have a count of pages or if the value passed in from $page entry is 1, in such case, we set pageCount to 1
    if(!isset($_SESSION['pageCount']) || $pageEntry == 1) $_SESSION['pageCount'] = 1;

    // Then we check if $pageEntry was a number and not a 0, if it is, then we set our page number as our pageCount SESSION global and we increment it
    if (intval($pageEntry) && $pageEntry != 0){
        $page = $_SESSION['pageCount'];
        $_SESSION['pageCount']++;

    } 
    // If somehow the previous if condition is not met, the program appropriately dies, as it is assumed the code was messed with.
    else die("What did you do?");

    $limit = 20;
    // The calculation to define from which point of the DB we are going to start retrieving quotes from, so no quotes repeat
    $offset = ($page - 1) * $limit;

    /**
     * Function used to format our authors and quotes into the correct card format to be pushed into
     * an array of "card strings" that will display into our html, it sanitizes the two given values
     * and then injects them into our card format, which is then return
     * @param string $val1 First value to be sanitized and injected, expected to be an author quote
     * @param string $val2 Second value to be sanitized and injected, expected to be a quote
     * @return string a formated string that is valid html for a "card" object (a formated div)
     */
    function cardFormat($val1, $val2){
            // Never trust a DB! specially one you create yourself!!!
            // We filter the author_name value gotten from the database
            $filteredAuthor = filter_var($val1, FILTER_SANITIZE_SPECIAL_CHARS);
            // We filter the quote_text value gotten from the database
            $filteredQuote = filter_var($val2, FILTER_SANITIZE_SPECIAL_CHARS);
            // The we push into our cards array a string value formated as html with the values above injected into it
            $formatedString = 
                "<div class='card mb-3 a4card w-100'>
                        <div class='card-header'>
                            $filteredAuthor
                        </div>
                        <div class='card-body d-flex align-items-center'>
                            <p class='card-text w-100'>
                                $filteredQuote
                            </p>
                        </div>
                </div>";
            return $formatedString;
    }

    // If function meant to check whether the code executes or not, it can only execute if the value of $page was set and is greater than 0
    if(is_numeric($page) && $page > 0){
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
            false upon reaching the end, it is set to fetch each row as an associative array and then feed
            its values to the function that is returning the strings in the format we want, which we then 
            push into our cards[] array.
        */ 
        while($res = $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($cards, cardFormat($res['author_name'], $res['quote_text']));
        } 
        // Finally, we echo a JSON encoded array of strings, which will be cards containing our retrieved quotes
        echo json_encode($cards);
    }

?>