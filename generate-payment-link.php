<?php

require 'init.php'; // Include Stripe initialization

$error = '';
$success = '';
$payment_link_url = '';

// Fetch all products from Stripe
$products = $stripe->products->all();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect selected products from the form
    $selected_products = $_POST['products'] ?? [];

    // Basic validation
    if (empty($selected_products)) {
        $error = 'Please select at least one product.';
    } else {
        try {
            // Prepare line items for the selected products
            $line_items = [];
            foreach ($selected_products as $product_id) {
                $product = $stripe->products->retrieve($product_id);
                $price = $stripe->prices->retrieve($product->default_price);
                $line_items[] = [
                    'price' => $price->id,
                    'quantity' => 1, // You can adjust the quantity as necessary
                ];
            }

            // Create the payment link with the selected products
            $payment_link = $stripe->paymentLinks->create([
                'line_items' => $line_items,
            ]);

            // Redirect the user to the payment link
            $payment_link_url = $payment_link->url;
            header("Location: $payment_link_url");
            exit();

        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle errors from Stripe API
            $error = 'Error generating payment link: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Payment Link</title>
    <style>
        /* Styling similar to your design */
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
        }

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

        .container {
            width: 50%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        select, input[type="checkbox"] {
            padding: 10px;
            margin: 10px 0;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px;
            background-color: #d4af37;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1rem;
        }

        button:hover {
            background-color: #c59b00;
        }

        .error {
            color: #ff0000;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .success {
            color: #28a745;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .product-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px 0;
        }

        .product-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .product-card p {
            margin: 5px 0;
            text-align: center;
        }

    </style>
</head>
<body>

<!-- Header Section -->
<header>
    <div class="logo">
        <h1>Luxuria</h1>
    </div>
</header>

<!-- Payment Link Generation Form -->
<div class="container">
    <h2>Select Products and Generate Payment Link</h2>

    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php elseif ($payment_link_url): ?>
        <div class="success">
            Payment link generated successfully. Redirecting to payment page...
        </div>
    <?php endif; ?>

    <!-- Form to select products -->
    <form method="POST" action="generate-payment-link.php">
        <label for="products">Select Products</label>
        <?php foreach ($products->data as $product): ?>
            <div class="product-card">
                <?php 
                    // Get the image URL (array_pop is used to get the last image if there are multiple)
                    $image = array_pop($product->images); 
                    $price = $stripe->prices->retrieve($product->default_price);
                ?>
                <img src="<?php echo $image; ?>" alt="<?php echo $product->name; ?>">
                <p><strong><?php echo $product->name; ?></strong></p>
                <p class="price"><?php echo strtoupper($price->currency) . ' ' . number_format($price->unit_amount / 100, 2); ?></p>
                <input type="checkbox" name="products[]" value="<?php echo $product->id; ?>" id="product-<?php echo $product->id; ?>">
                <label for="product-<?php echo $product->id; ?>">Add to Payment Link</label>
            </div>
        <?php endforeach; ?>

        <button type="submit">Generate Payment Link</button>
    </form>
</div>

</body>
</html>

