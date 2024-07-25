<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to access this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

function fetch_and_process_api_data() {
    $api_url = "https://real-time-amazon-data.p.rapidapi.com/deals-v2?country=US&min_product_star_rating=ALL&price_range=ALL&discount_range=ALL";
    $api_key = "06f4fe2912mshe799dd41b35b27cp105d40jsn95e12348139"; 

    if (!$api_key) {
        flash("API key not set", "danger");
        return;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-rapidapi-host: real-time-amazon-data.p.rapidapi.com",
        "x-rapidapi-key: $api_key"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (!$data) {
        flash("Failed to fetch data from API", "danger");
        return;
    }

    $db = getDB();
    foreach ($data['deals'] as $deal) {
        $product_id = $deal['deal_id'];
        $product_name = $deal['deal_title'];
        $current_price = $deal['deal_price']['amount'];
        $original_price = $deal['list_price']['amount'];
        $discount_percentage = $deal['savings_percentage'];
        $image_url = $deal['deal_photo'];
        $product_asin = $deal['product_asin'];

        error_log("Processing product: $product_id - $product_name");

        $stmt = $db->prepare("SELECT product_id FROM products WHERE product_id = :product_id");
        $stmt->execute([":product_id" => $product_id]);
        $existing_product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_product) {
            $update_query = "UPDATE products SET 
                product_name = :product_name,
                current_price = :current_price,
                original_price = :original_price,
                discount_percentage = :discount_percentage,
                image_url = :image_url,
                modified = NOW()
                WHERE product_id = :product_id";
            $stmt = $db->prepare($update_query);
            $result = $stmt->execute([
                ":product_id" => $product_id,
                ":product_name" => $product_name,
                ":current_price" => $current_price,
                ":original_price" => $original_price,
                ":discount_percentage" => $discount_percentage,
                ":image_url" => $image_url
            ]);
            if ($result) {
                error_log("Updated product: $product_id - $product_name");
            } else {
                error_log("Failed to update product: $product_id - $product_name");
            }
        } else {
            $insert_query = "INSERT INTO products (
                product_id, product_name, current_price, original_price, discount_percentage, image_url, created, modified
            ) VALUES (
                :product_id, :product_name, :current_price, :original_price, :discount_percentage, :image_url, NOW(), NOW()
            )";
            $stmt = $db->prepare($insert_query);
            $result = $stmt->execute([
                ":product_id" => $product_id,
                ":product_name" => $product_name,
                ":current_price" => $current_price,
                ":original_price" => $original_price,
                ":discount_percentage" => $discount_percentage,
                ":image_url" => $image_url
            ]);
            if ($result) {
                error_log("Inserted product: $product_id - $product_name");
            } else {
                error_log("Failed to insert product: $product_id - $product_name");
            }
        }
    }

    flash("Successfully fetched and processed data from API", "success");
}

fetch_and_process_api_data();
header("Location: " . get_url("admin/fetch_api_data.php"));
?>
