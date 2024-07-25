<?php
require(__DIR__ . "/../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to access this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

function fetch_and_process_api_data() {
    $api_url = "https://real-time-amazon-data.p.rapidapi.com/search?query=Phone&page=1&country=US&sort_by=RELEVANCE&product_condition=ALL"; // Use the correct endpoint
    $api_key = "06f4fe2912mshe799dd41b35b27cp105d40jsn95e12348139"; // Replace with your actual API key

    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-rapidapi-host: real-time-amazon-data.p.rapidapi.com",
        "x-rapidapi-key: $api_key"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    if (!$data) {
        flash("Failed to fetch data from API", "danger");
        return;
    }

    // Process the data
    $db = getDB();
    foreach ($data['products'] as $product) {
        $product_id = $product['id'];
        $product_name = $product['name'];
        $current_price = $product['current_price'];
        $original_price = $product['original_price'];
        $discount_percentage = $product['discount_percentage'];
        $image_url = $product['image_url'];
        // Add other fields as necessary

        // Check if the product already exists
        $stmt = $db->prepare("SELECT product_id FROM products WHERE product_id = :product_id");
        $stmt->execute([":product_id" => $product_id]);
        $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_product) {
            // Update existing product
            $update_query = "UPDATE products SET 
                product_name = :product_name,
                current_price = :current_price,
                original_price = :original_price,
                discount_percentage = :discount_percentage,
                image_url = :image_url,
                modified = NOW()
                WHERE product_id = :product_id";
            $stmt = $db->prepare($update_query);
            $stmt->execute([
                ":product_id" => $product_id,
                ":product_name" => $product_name,
                ":current_price" => $current_price,
                ":original_price" => $original_price,
                ":discount_percentage" => $discount_percentage,
                ":image_url" => $image_url
            ]);
        } else {
            // Insert new product
            $insert_query = "INSERT INTO products (
                product_id, product_name, current_price, original_price, discount_percentage, image_url, created, modified
            ) VALUES (
                :product_id, :product_name, :current_price, :original_price, :discount_percentage, :image_url, NOW(), NOW()
            )";
            $stmt = $db->prepare($insert_query);
            $stmt->execute([
                ":product_id" => $product_id,
                ":product_name" => $product_name,
                ":current_price" => $current_price,
                ":original_price" => $original_price,
                ":discount_percentage" => $discount_percentage,
                ":image_url" => $image_url
            ]);
        }
    }

    flash("Successfully fetched and processed data from API", "success");
}

// Trigger the API data fetch
fetch_and_process_api_data();
header("Location: " . get_url("admin/fetch_api_data.php"));
?>
