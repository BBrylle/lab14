<?php

require "init.php";

// Fetch products from Stripe
$products = $stripe->products->all();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Luxurious Stripe Product Store</title>
  <style>
    /* Reset default styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Georgia', serif;
      background-color: #f5f5f5;
      color: #444;
      line-height: 1.6;
      margin: 0;
      padding: 0;
    }

    /* Luxurious Header */
    header {
      background-color: #333;
      color: #fff;
      padding: 1.5rem 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 10;
    }

    header .logo h1 {
      font-size: 2rem;
      font-weight: bold;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #d4af37; /* Gold color for a luxury feel */
      margin-left: 30px;
    }

    nav ul {
      display: flex;
      list-style: none;
      margin-right: 30px;
    }

    nav ul li {
      margin: 0 20px;
    }

    nav ul li a {
      color: #fff;
      text-decoration: none;
      font-weight: 500;
      font-size: 1.1rem;
      transition: color 0.3s ease-in-out;
    }

    nav ul li a:hover {
      color: #d4af37; /* Gold hover effect */
    }

    /* Product Listings */
    .products {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-evenly;
      margin: 30px;
    }

    .product-card {
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      width: 320px;
      margin: 30px;
      text-align: center;
      padding: 25px;
      transition: transform 0.4s ease-in-out, box-shadow 0.4s ease-in-out;
    }

    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .product-card img {
      width: 100%;
      height: auto;
      border-radius: 12px;
      object-fit: cover;
      transition: transform 0.4s ease-in-out;
    }

    .product-card img:hover {
      transform: scale(1.05);
    }

    .product-card h2 {
      font-size: 1.6rem;
      font-weight: bold;
      color: #333;
      margin: 20px 0;
      letter-spacing: 0.5px;
    }

    .product-card .price {
      font-size: 1.4rem;
      color: #d4af37; /* Gold color */
      font-weight: bold;
      margin: 15px 0;
    }

    .buy-now {
      background-color: #d4af37;
      color: white;
      border: none;
      padding: 12px 25px;
      font-size: 1.2rem;
      cursor: pointer;
      border-radius: 30px;
      transition: background-color 0.3s ease-in-out, transform 0.3s ease-in-out;
    }

    .buy-now:hover {
      background-color: #c59b00;
      transform: translateY(-3px);
    }

    /* Footer Section */
    footer {
      background-color: #333;
      color: #fff;
      text-align: center;
      padding: 20px;
      font-size: 1rem;
      letter-spacing: 1px;
      position: relative;
    }

    /* Luxury Product Layout for Mobile */
    @media (max-width: 768px) {
      .products {
        flex-direction: column;
        align-items: center;
      }

      .product-card {
        width: 90%;
        margin: 20px 0;
      }

      nav ul {
        flex-direction: column;
        text-align: center;
      }

      nav ul li {
        margin: 10px 0;
      }
    }

  </style>
</head>
<body>
  <!-- Header Section -->
  <header>
    <div class="logo">
      <h1>Luxuria</h1>
    </div>
    <nav>
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">Shop</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </nav>
  </header>

  <!-- Product Listings -->
  <section class="products">
    <?php
    // Loop through each product and display them in HTML format
    foreach ($products as $product) {
        // Get the image URL (array_pop is used to get the last image if there are multiple)
        $image = array_pop($product->images);
        // Fetch price details
        $price = $stripe->prices->retrieve($product->default_price);

        // Display the product details inside HTML structure
        echo '<div class="product-card">';
        echo '<img src="' . $image . '" alt="' . $product->name . '">';
        echo '<h2>' . $product->name . '</h2>';
        echo '<p class="price">' . strtoupper($price->currency) . ' ' . number_format($price->unit_amount / 100, 2) . '</p>';
        echo '<button class="buy-now">Buy Now</button>';
        echo '</div>';
    }
    ?>
  </section>

  <!-- Footer Section -->
  <footer>
    <p>&copy; 2024 Luxuria Store. All rights reserved.</p>
  </footer>
</body>
</html>

<?php
// End of PHP block
?>
