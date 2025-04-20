<?php
function displayStarRating($rating) {
    $html = '<div class="star-rating">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="star filled">★</span>';
        } else {
            $html .= '<span class="star">☆</span>';
        }
    }
    $html .= '</div>';
    return $html;
}

function getAverageRating($product_id, $conn) {
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM product_reviews WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return round($row['avg_rating'] ?? 0, 1);
}
?>

<style>
.star-rating {
    display: inline-block;
    font-size: 1.2em;
    line-height: 1;
}

.star {
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.star.filled {
    color: #ffd700;
}

.star:hover {
    color: #ffd700;
}

.rating-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}

.average-rating {
    font-size: 0.9em;
    color: #666;
}

.rating-form {
    margin-top: 10px;
}

.rating-form select {
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.rating-form textarea {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.rating-form button {
    margin-top: 5px;
    padding: 5px 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.rating-form button:hover {
    background-color: #45a049;
}
</style> 