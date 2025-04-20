<?php
session_start();
include 'db_connection.php';

if(isset($_POST['remove'])){
    $product_id = $_POST['product_id'];
    if(isset($_SESSION['cart'][$product_id])){
        unset($_SESSION['cart'][$product_id]);
    }
}

if(isset($_POST['update_quantity'])){
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    if(isset($_SESSION['cart'][$product_id])){
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <div class="container">
        <h1>Shopping Cart</h1>
        <div class="cart-items">
            <?php
            $total = 0;
            if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0){
                foreach($_SESSION['cart'] as $product_id => $item){
                    $sql = "SELECT * FROM products WHERE id = $product_id";
                    $result = mysqli_query($conn, $sql);
                    $product = mysqli_fetch_assoc($result);
                    $subtotal = $product['price'] * $item['quantity'];
                    $total += $subtotal;
            ?>
                <div class="cart-item">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <div class="item-details">
                        <h3><?php echo $product['name']; ?></h3>
                        <p>Price: $<?php echo $product['price']; ?></p>
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1">
                            <button type="submit" name="update_quantity">Update</button>
                            <button type="submit" name="remove">Remove</button>
                        </form>
                    </div>
                    <div class="item-total">
                        $<?php echo $subtotal; ?>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>Your cart is empty</p>";
            }
            ?>
        </div>
        <div class="cart-total">
            <h2>Total: $<?php echo $total; ?></h2>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    </div>
</body>
</html>
