<?php

function fetch_and_process_api_data() {
    $api_key = "06f4fe2912mshe799dd41b35b27cp105d40jsn95e12f348139";

    if (!$api_key) {
        error_log("API key not set");
        return;
    }

    $curl = curl_init();

    // Step 1: Fetch deal IDs from the Deals endpoint
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://real-time-amazon-data.p.rapidapi.com/deals-v2?country=US&min_product_star_rating=ALL&price_range=ALL&discount_range=ALL",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "x-rapidapi-host: real-time-amazon-data.p.rapidapi.com",
            "x-rapidapi-key: $api_key"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
        flash("cURL Error: " . $err, "danger");
        return;
    }

    $data = json_decode($response, true);

    error_log("Raw API Response: " . $response);

    if (!isset($data["data"]) || !isset($data["data"]["deals"])) {
        error_log("Failed to fetch data from API: 'deals' key missing in " . print_r($data, true));
        return;
    }

    $deals = $data["data"]["deals"];

    // Step 2: Use deal IDs to get detailed product information from the Deal Products endpoint
    $db = getDB();
    foreach ($deals as $deal) {
        $deal_id = $deal['deal_id'];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://real-time-amazon-data.p.rapidapi.com/deal-products?country=US&sort_by=FEATURED&page=1&deal_id=$deal_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "x-rapidapi-host: real-time-amazon-data.p.rapidapi.com",
                "x-rapidapi-key: $api_key"
            ],
        ]);

        $product_response = curl_exec($curl);
        $product_err = curl_error($curl);

        curl_close($curl);

        if ($product_err) {
            echo "cURL Error #:" . $product_err;
            flash("cURL Error: " . $product_err, "danger");
            continue;
        }

        $product_data = json_decode($product_response, true);
        error_log("Product API Response for deal_id $deal_id: " . $product_response);

        if (!isset($product_data["data"]) || !isset($product_data["data"]["products"])) {
            error_log("Failed to fetch data from API: 'products' key missing in " . print_r($product_data, true));
            continue;
        }

        $products = $product_data["data"]["products"];

        foreach ($products as $product) {
            $product_id = $product['product_asin'];
            $product_name = $product['product_title'];
            $current_price = isset($product['deal_price']) ? (string)$product['deal_price'] : "0";
            $original_price = isset($product['list_price']) ? (string)$product['list_price'] : "0";
            preg_match('/(\d+)%/', $product['savings_percentage'], $matches);
            $discount_percentage = isset($matches[1]) ? (string)$matches[1] : "0";
            $image_url = $product['product_photo'];

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
                    data_source = 'api',
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
                    $error_info = $stmt->errorInfo();
                    error_log("Failed to update product: $product_id - $product_name. Error: " . print_r($error_info, true));
                }
            } else {
                $insert_query = "INSERT INTO products (
                    product_id, product_name, current_price, original_price, discount_percentage, image_url, data_source, created, modified
                ) VALUES (
                    :product_id, :product_name, :current_price, :original_price, :discount_percentage, :image_url, 'api', NOW(), NOW()
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
                    $error_info = $stmt->errorInfo();
                    error_log("Failed to insert product: $product_id - $product_name. Error: " . print_r($error_info, true));
                }
            }
        }
    }

    flash("Successfully fetched and processed data from API", "success");
}

fetch_and_process_api_data();

?>
