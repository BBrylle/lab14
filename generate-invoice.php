<?php
require 'init.php'; // Include Stripe initialization

// Initialize variables
$error = '';
$success = '';
$invoice_url = '';
$invoice_pdf = '';

// Fetch all customers and products from Stripe
$customers = $stripe->customers->all();
$products = $stripe->products->all();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $customer_id = $_POST['customer_id'] ?? '';
    $selected_products = $_POST['products'] ?? [];

    // Basic validation
    if (empty($customer_id) || empty($selected_products)) {
        $error = 'Please select a customer and at least one product.';
    } else {
        try {
            // Create an invoice for the selected customer
            $invoice = $stripe->invoices->create([
                'customer' => $customer_id,
                'auto_advance' => true, // Automatically finalize and pay the invoice
            ]);

            // Add the selected products as line items
            foreach ($selected_products as $product_id) {
                $product = $stripe->products->retrieve($product_id);
                $price = $stripe->prices->retrieve($product->default_price);

                $stripe->invoiceItems->create([
                    'customer' => $customer_id,
                    'price' => $price->id,
                    'invoice' => $invoice->id,
                    'quantity' => 1, // Adjust quantity as necessary
                ]);
            }

            // Finalize the invoice
            $stripe->invoices->finalizeInvoice($invoice->id);

            // Retrieve the finalized invoice
            $invoice = $stripe->invoices->retrieve($invoice->id);

            // Get the invoice PDF and hosted URL
            $invoice_url = $invoice->hosted_invoice_url;
            $invoice_pdf = $invoice->invoice_pdf;

            // Success message
            $success = 'Invoice generated successfully!';
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle errors from Stripe API
            $error = 'Error generating invoice: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Stripe Invoice</title>
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
            color: #d4af37;
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

        .invoice-links {
            margin-top: 20px;
            text-align: center;
        }

        .invoice-links a {
            color: #d4af37;
            text-decoration: none;
            font-weight: bold;
        }

        .invoice-links a:hover {
            color: #c59b00;
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

<!-- Invoice Generation Form -->
<div class="container">
    <h2>Generate an Invoice</h2>

    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Invoice creation form -->
    <form method="POST" action="generate-invoice.php">
        <label for="customer_id">Select Customer</label>
        <select name="customer_id" id="customer_id" required>
            <option value="">Select a customer...</option>
            <?php foreach ($customers->data as $customer): ?>
                <option value="<?php echo $customer->id; ?>"><?php echo $customer->name; ?></option>
            <?php endforeach; ?>
        </select>

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
                <label for="product-<?php echo $product->id; ?>">Add to Invoice</label>
            </div>
        <?php endforeach; ?>

        <button type="submit">Generate Invoice</button>
    </form>

    <?php if ($invoice_url && $invoice_pdf): ?>
        <div class="invoice-links">
            <p>Invoice generated successfully!</p>
            <p><a href="<?php echo $invoice_url; ?>" target="_blank">View Hosted Invoice</a></p>
            <p><a href="<?php echo $invoice_pdf; ?>" target="_blank">Download Invoice PDF</a></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
