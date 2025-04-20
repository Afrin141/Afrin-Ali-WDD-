document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.search-container form');
    const searchInput = document.getElementById('search-input');
    const searchResultsDiv = document.getElementById('search-results');

    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const searchQuery = searchInput.value;
        
        fetch('search.php?search=' + encodeURIComponent(searchQuery))
            .then(response => response.json())
            .then(data => {
                let resultsHTML = '<h2>Search Results:</h2>';
                if (data.length > 0) {
                    resultsHTML += '<ul>';
                    data.forEach(product => {
                        resultsHTML += `<li>${product.name} - Rs.${product.price}</li>`;
                    });
                    resultsHTML += '</ul>';
                } else {
                    resultsHTML += '<p>No products found matching your search.</p>';
                }
                searchResultsDiv.innerHTML = resultsHTML;
            })
            .catch(error => {
                console.error('Error:', error);
                searchResultsDiv.innerHTML = '<p>An error occurred while searching.</p>';
            });
    });
});
