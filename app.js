document.addEventListener('DOMContentLoaded', function () {
    fetch( "me.php" )
    .then( response => {
        if ( !response.ok ) {
            throw new Error(response.status+" "+response.statusText)
        } else {
            return response.text();
        } 
    } )
    .then( data => document.getElementById( "student_info" ).innerHTML = data )
    .catch( error => document.getElementById( "student_info" ).innerHTML = '<strong>'+error+'</strong>' );

  const quoteContainer = document.getElementById('quoteContainer');
  const loadingIndicator = document.getElementById('loadingIndicator');
  let page = 1; // Initial page number

  function fetchQuotes() {
    fetch(`quotes.php?page=${page}`)
      .then(response => response.json())
      .then(data => {
        if (data.length > 0) {
          // Append quotes to the container
          data.forEach(quote => {
            quoteContainer.innerHTML += '<div class="col d-flex align-middle text-center">'+quote+'</div>';
            //quoteContainer.innerHTML += `<div class="mb-3">${quote.quote_text} - ${quote.author_name}</div>`;
          });

          // Increment page number for the next request
          page++;
        } else {
          // No more quotes, remove the loading indicator
          loadingIndicator.style.display = 'none';
        }
      })
      .catch(error => {console.error('Error fetching quotes:', error);loadingIndicator.style.display = 'none';});
  }

  function handleScroll() {
    // Check if the user has scrolled to the bottom
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
      //loadingIndicator.style.display = 'block'; // Show loading indicator
      fetchQuotes(); // Fetch more quotes
    }
  }

  // Initial fetch on page load
  fetchQuotes();

  // Add scroll event listener
  window.addEventListener('scroll', handleScroll);
});