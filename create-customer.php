<?php
require 'init.php'; // Include Stripe initialization

// Initialize the error and success variables
$error = '';
$success = '';
$customer_details = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect data from the form
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address_line1 = $_POST['address_line1'] ?? '';
    $address_line2 = $_POST['address_line2'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $country = $_POST['country'] ?? '';
    $postal_code = $_POST['postal_code'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($address_line1) || empty($city) || empty($postal_code)) {
        $error = 'Please fill out all required fields.';
    } else {
        try {
            // Create the customer in Stripe
            $customer = $stripe->customers->create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => [
                    'line1' => $address_line1,
                    'line2' => $address_line2,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'postal_code' => $postal_code
                ]
            ]);

            // Success message if the customer was created successfully
            $success = 'Customer created successfully!';
            $customer_details = $customer; // Save the customer details to display later
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle errors from the Stripe API
            $error = 'Error creating customer: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Customer</title>
    <style>
        /* Basic styles */
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

        /* Form container */
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

        input {
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

        .customer-details {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .customer-details h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        .customer-details p {
            font-size: 1.1rem;
            margin-bottom: 10px;
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

<!-- Registration Form Container -->
<div class="container">
    <h2>Create Customer</h2>

    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Customer creation form -->
    <form method="POST" action="create-customer.php">
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>

        <label for="phone">Phone</label>
        <input type="text" name="phone" id="phone" required>

        <label for="address_line1">Address Line 1</label>
        <input type="text" name="address_line1" id="address_line1" required>

        <label for="address_line2">Address Line 2</label>
        <input type="text" name="address_line2" id="address_line2">

        <label for="city">City</label>
        <input type="text" name="city" id="city" required>

        <label for="state">State</label>
        <input type="text" name="state" id="state">

        <label for="country">Country</label>
        <input type="text" name="country" id="country">

        <label for="postal_code">Postal Code</label>
        <input type="text" name="postal_code" id="postal_code" required>

        <button type="submit">Create Customer</button>
    </form>
</div>

<!-- Display created customer details if successful -->
<?php if ($customer_details): ?>
    <div class="customer-details">
        <h3>Customer Details</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($customer_details->name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($customer_details->email); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer_details->phone); ?></p>
        <p><strong>Address:</strong></p>
        <p><?php echo htmlspecialchars($customer_details->address['line1']); ?></p>
        <p><?php echo htmlspecialchars($customer_details->address['line2']); ?></p>
        <p><?php echo htmlspecialchars($customer_details->address['city']); ?></p>
        <p><?php echo htmlspecialchars($customer_details->address['state']); ?></p>
        <p><?php echo htmlspecialchars($customer_details->address['country']); ?></p>
        <p><strong>Postal Code:</strong> <?php echo htmlspecialchars($customer_details->address['postal_code']); ?></p>
    </div>
<?php endif; ?>

</body>
</html>
