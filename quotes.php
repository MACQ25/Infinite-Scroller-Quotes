<?php

    $page = (isset($_GET['page'])) ? filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT): 1;
    $limit = 20;
    $offset = max(($page - 1) * $limit, 0);

    try{
        $dbh = new PDO("mysql:host=localhost;dbname=sa000881810", "sa000881810", "Sa_20030625");
    } catch (Exception $e){
        die("ERROR: Couldn't connect. {$e->getMessage()}");
    }

    // Formulae: (pages - 1) * limit


    $command = "SELECT quotes.quote_text, authors.author_name
    FROM quotes
    JOIN authors ON quotes.author_id = authors.author_id
    LIMIT :per_page
    OFFSET :offset 
    ";

    $stmt = $dbh->prepare($command);
    $stmt->bindParam(':per_page', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $success = $stmt->execute();
    
    $cards = [];
    while($res = $stmt->fetch(PDO::FETCH_ASSOC)){
        $filteredAuthor = filter_var($res['author_name'], FILTER_SANITIZE_SPECIAL_CHARS);
        $filteredQuote = filter_var($res['quote_text'], FILTER_SANITIZE_SPECIAL_CHARS);
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
    echo json_encode($cards);
?>