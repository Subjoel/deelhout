<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
defined('ENVIRONMENT') || define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__);
$pathsConfig = FCPATH . 'app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;
$paths = new Config\Paths();
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = require realpath($bootstrap) ?: $bootstrap;
$dbArray = new \Config\Database();
$connection = mysqli_connect($dbArray->default['hostname'], $dbArray->default['username'], $dbArray->default['password'], $dbArray->default['database']);
if (empty($connection)) {
    echo 'Database connection failed! Check your database credentials in the "app/Config/Database.php" file.';
    exit();
}
$connection->query("SET CHARACTER SET utf8");
$connection->query("SET NAMES utf8");

function runQuery($sql)
{
    global $connection;
    return mysqli_query($connection, $sql);
}

if (isset($_POST["btn_submit"])) {
    update($connection);
    $success = 'The update has been successfully completed! Please delete the "update_database.php" file.';
}

function update()
{
    updateFrom23To24();
}

function updateFrom23To24()
{
    global $connection;

    runQuery("DROP TABLE routes;");

    $tableRoutes = "CREATE TABLE `routes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `route_key` varchar(100) DEFAULT NULL,
    `route` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

    $tableBrands = "CREATE TABLE `brands` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) DEFAULT NULL,
    `name_data` text DEFAULT NULL,
    `image_path` varchar(255) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";

    runQuery($tableRoutes);
    runQuery($tableBrands);

    $routesSQL = "INSERT INTO `routes` (`id`, `route_key`, `route`) VALUES
(1, 'add_coupon', 'add-coupon'),
(2, 'add_product', 'add-product'),
(3, 'add_shipping_zone', 'add-shipping-zone'),
(4, 'admin', 'admin'),
(5, 'blog', 'blog'),
(6, 'bulk_product_upload', 'bulk-product-upload'),
(7, 'cart', 'cart'),
(8, 'category', 'category'),
(9, 'change_password', 'change-password'),
(10, 'comments', 'comments'),
(11, 'contact', 'contact'),
(12, 'coupons', 'coupons'),
(13, 'dashboard', 'dashboard'),
(14, 'downloads', 'downloads'),
(15, 'earnings', 'earnings'),
(16, 'edit_coupon', 'edit-coupon'),
(17, 'edit_product', 'edit-product'),
(18, 'edit_profile', 'edit-profile'),
(19, 'edit_shipping_zone', 'edit-shipping-zone'),
(20, 'featured_products', 'featured-products'),
(21, 'followers', 'followers'),
(22, 'following', 'following'),
(23, 'forgot_password', 'forgot-password'),
(24, 'help_center', 'help-center'),
(25, 'latest_products', 'latest-products'),
(26, 'location', 'location'),
(27, 'members', 'members'),
(28, 'membership_payment_completed', 'membership-payment-completed'),
(29, 'messages', 'messages'),
(30, 'my_coupons', 'my-coupons'),
(31, 'orders', 'orders'),
(32, 'order_completed', 'order-completed'),
(33, 'order_details', 'order-details'),
(34, 'payment', 'payment'),
(35, 'payment_history', 'payment-history'),
(36, 'payment_method', 'payment-method'),
(37, 'payouts', 'payouts'),
(38, 'product', 'product'),
(39, 'products', 'products'),
(40, 'product_details', 'product-details'),
(41, 'profile', 'profile'),
(42, 'promote_payment_completed', 'promote-payment-completed'),
(43, 'quote_requests', 'quote-requests'),
(44, 'refund_requests', 'refund-requests'),
(45, 'register', 'register'),
(46, 'register_success', 'register-success'),
(47, 'reset_password', 'reset-password'),
(48, 'reviews', 'reviews'),
(49, 'rss_feeds', 'rss-feeds'),
(50, 'sale', 'sale'),
(51, 'sales', 'sales'),
(52, 'search', 'search'),
(53, 'select_membership_plan', 'select-membership-plan'),
(54, 'seller', 'seller'),
(55, 'settings', 'settings'),
(56, 'set_payout_account', 'set-payout-account'),
(57, 'shipping', 'shipping'),
(58, 'shipping_address', 'shipping-address'),
(59, 'shipping_settings', 'shipping-settings'),
(60, 'shops', 'shops'),
(61, 'shop_settings', 'shop-settings'),
(62, 'social_media', 'social-media'),
(63, 'start_selling', 'start-selling'),
(64, 'submit_request', 'submit-request'),
(65, 'tag', 'tag'),
(66, 'terms_conditions', 'terms-conditions'),
(67, 'ticket', 'ticket'),
(68, 'tickets', 'tickets'),
(69, 'wishlist', 'wishlist'),
(70, 'withdraw_money', 'withdraw-money');";
    runQuery($routesSQL);

    runQuery("ALTER TABLE conversation_messages CHANGE conversation_id chat_id int;");
    runQuery("ALTER TABLE conversations RENAME chat;");
    runQuery("ALTER TABLE conversation_messages RENAME chat_messages;");
    runQuery("ALTER TABLE chat ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL;");
    runQuery("ALTER TABLE custom_fields ADD COLUMN `where_to_display` TINYINT(4) DEFAULT 2;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `vat_status`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `commission_rate`;");
    runQuery("ALTER TABLE general_settings CHANGE hide_vendor_contact_information show_vendor_contact_information TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `vendors_change_shop_name` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE orders ADD COLUMN `transaction_fee_rate` double;");
    runQuery("ALTER TABLE orders ADD COLUMN `transaction_fee` bigint(20);");
    runQuery("ALTER TABLE orders ADD COLUMN `global_taxes_data` text;");
    runQuery("ALTER TABLE payment_gateways ADD COLUMN `transaction_fee` double;");
    runQuery("ALTER TABLE payment_settings ADD COLUMN `commission_rate` double DEFAULT 0;");
    runQuery("ALTER TABLE payment_settings ADD COLUMN `vat_status` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE payment_settings ADD COLUMN `global_taxes_data` longtext;");
    runQuery("ALTER TABLE products ADD COLUMN `price_discounted` bigint(20);");
    runQuery("ALTER TABLE products ADD COLUMN `digital_file_download_link` varchar(500);");
    runQuery("ALTER TABLE products ADD COLUMN `country_id` int DEFAULT 0;");
    runQuery("ALTER TABLE products ADD COLUMN `state_id` int DEFAULT 0;");
    runQuery("ALTER TABLE products ADD COLUMN `city_id` int DEFAULT 0;");
    runQuery("ALTER TABLE products ADD COLUMN `address` varchar(500);");
    runQuery("ALTER TABLE products ADD COLUMN `zip_code` varchar(50);");
    runQuery("ALTER TABLE products ADD COLUMN `brand_id` int DEFAULT 0;");
    runQuery("ALTER TABLE product_details DROP COLUMN `seo_title`;");
    runQuery("ALTER TABLE product_details CHANGE seo_description short_description varchar(500);");
    runQuery("ALTER TABLE product_details CHANGE seo_keywords keywords varchar(500);");
    runQuery("ALTER TABLE product_settings ADD COLUMN `digital_external_link` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `is_product_image_required` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `image_file_format` varchar(30) DEFAULT 'original';");
    runQuery("ALTER TABLE product_settings ADD COLUMN `brand_status` TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `is_brand_optional` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `brand_where_to_display` TINYINT(4) DEFAULT 2;");
    runQuery("ALTER TABLE settings ADD COLUMN `tiktok_url` varchar(500);");
    runQuery("ALTER TABLE shipping_addresses ADD COLUMN `address_type` varchar(30) DEFAULT 'shipping';");
    runQuery("ALTER TABLE users ADD COLUMN `cash_on_delivery_fee` bigint(20);");
    runQuery("ALTER TABLE users ADD COLUMN `is_fixed_vat` TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE users ADD COLUMN `fixed_vat_rate` double DEFAULT 0;");
    runQuery("ALTER TABLE users ADD COLUMN `vat_rates_data` text;");
    runQuery("ALTER TABLE users ADD COLUMN `tiktok_url` varchar(500);");
    runQuery("ALTER TABLE variation_options ADD COLUMN `price_discounted` bigint(20);");
    runQuery("ALTER TABLE variation_options DROP COLUMN `no_discount`;");

    //update price discounts
    $products = runQuery("SELECT * FROM products ORDER BY id;");
    if (!empty($products->num_rows)) {
        while ($product = mysqli_fetch_array($products)) {
            $price = $product['price'];
            $discountRate = $product['discount_rate'];
            $priceDiscounted = $price;
            if (!empty($discountRate) && !empty($price)) {
                $price = $price / 100;
                $price = $price - (($price * $discountRate) / 100);
                if (!empty($price)) {
                    $price = number_format($price, 2, '.', '');
                }
                $priceDiscounted = $price * 100;
            }
            runQuery("UPDATE products SET `price_discounted`='" . $priceDiscounted . "' WHERE `id`=" . $product['id'] . ";");
        }
    }

    //update price discounts for variations
    $variationOptions = runQuery("SELECT * FROM variation_options WHERE price != 0 ORDER BY id;");
    if (!empty($variationOptions->num_rows)) {
        while ($variationOption = mysqli_fetch_array($variationOptions)) {
            $price = $variationOption['price'];
            $discountRate = $variationOption['discount_rate'];
            $priceDiscounted = $price;
            if (!empty($discountRate) && !empty($price)) {
                $price = $price / 100;
                $price = $price - (($price * $discountRate) / 100);
                if (!empty($price)) {
                    $price = number_format($price, 2, '.', '');
                }
                $priceDiscounted = $price * 100;
            }
            runQuery("UPDATE variation_options SET `price_discounted`='" . $priceDiscounted . "' WHERE `id`=" . $variationOption['id'] . ";");
        }
    }

    runQuery("INSERT INTO `payment_gateways` (`name`, `name_key`, `public_key`, `secret_key`, `environment`, `base_currency`, `transaction_fee`, `status`, `logos`) VALUES
('PayTabs', 'paytabs', NULL, NULL, 'production', 'all', NULL, 0, 'visa,mastercard,paytabs');");

    //indexes
    runQuery("ALTER TABLE products ADD INDEX idx_brand_id (brand_id);");
    runQuery("ALTER TABLE chat_messages ADD INDEX idx_is_read (is_read);");
    runQuery("ALTER TABLE chat_messages ADD INDEX idx_deleted_user_id (deleted_user_id);");
    runQuery("ALTER TABLE comments ADD INDEX idx_status (status);");
    runQuery("ALTER TABLE coupon_products ADD INDEX idx_coupon_id (coupon_id);");
    runQuery("ALTER TABLE coupon_products ADD INDEX idx_product_id (product_id);");
    runQuery("ALTER TABLE custom_fields ADD INDEX idx_status (status);");
    runQuery("ALTER TABLE custom_fields ADD INDEX idx_where_to_display (where_to_display);");
    runQuery("ALTER TABLE custom_fields_product ADD INDEX idx_product_filter_key (product_filter_key);");
    runQuery("ALTER TABLE digital_sales ADD INDEX idx_seller_id (seller_id);");
    runQuery("ALTER TABLE digital_sales ADD INDEX idx_buyer_id (buyer_id);");
    runQuery("ALTER TABLE products ADD INDEX idx_price_discounted (price_discounted);");
    runQuery("ALTER TABLE products ADD INDEX idx_rating (rating);");
    runQuery("ALTER TABLE products ADD INDEX idx_country_id (country_id);");
    runQuery("ALTER TABLE products ADD INDEX idx_state_id (state_id);");
    runQuery("ALTER TABLE products ADD INDEX idx_city_id (city_id);");
    runQuery("ALTER TABLE quote_requests ADD INDEX idx_is_buyer_deleted (is_buyer_deleted);");
    runQuery("ALTER TABLE quote_requests ADD INDEX idx_is_seller_deleted (is_seller_deleted);");
    runQuery("ALTER TABLE refund_requests ADD INDEX idx_buyer_id (buyer_id);");
    runQuery("ALTER TABLE refund_requests ADD INDEX idx_seller_id (seller_id);");
    runQuery("ALTER TABLE support_subtickets ADD INDEX idx_ticket_id (ticket_id);");
    runQuery("ALTER TABLE support_subtickets ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE support_subtickets ADD INDEX idx_is_support_reply (is_support_reply);");
    runQuery("ALTER TABLE support_tickets ADD INDEX idx_user_id (user_id);");
    runQuery("ALTER TABLE support_tickets ADD INDEX idx_status (status);");

    //add new translations
    $p = array();
    $p["recent_chats"] = "Recent Chats";
    $p["offline"] = "Offline";
    $p["select_chat_start_messaging"] = "Select a chat to start messaging";
    $p["short_description"] = "Short Description";
    $p["keywords_exp"] = "Add a comma between words. Example: product, computer";
    $p["product_location_exp"] = "Optional product location. Your shop location will be displayed if you do not add a location for your product";
    $p["product_location"] = "Product Location";
    $p["show_vendor_contact_information"] = "Show Vendor Contact Information on the Site";
    $p["image_file_format"] = "Image File Format";
    $p["image_file_format_exp"] = "Uploaded images will be converted to the selected format";
    $p["keep_original_file_format"] = "Keep Original File Format";
    $p["tax_settings"] = "Tax Settings";
    $p["global_taxes"] = "Global Taxes";
    $p["global_taxes_exp"] = "Define new taxes by country for all sales on your site";
    $p["define_new_tax"] = "Define New Tax";
    $p["tax_name"] = "Tax Name";
    $p["tax_rate"] = "Tax Rate";
    $p["system"] = "System";
    $p["payment_cancelled"] = "Payment has been cancelled!";
    $p["profile_id"] = "Profile Id";
    $p["global"] = "Global";
    $p["vat_vendor_exp"] = "Allow vendors to add VAT for their products";
    $p["vat_vendor_dashboard_exp"] = "Define VAT values for your products based on countries";
    $p["msg_product_already_purchased"] = "You have already purchased this product before.";
    $p["tiktok_url"] = "TikTok URL";
    $p["my_coupons"] = "My Coupons";
    $p["set_fixed_vat_rate_all_countries"] = "Set Fixed VAT Rate for All Countries";
    $p["product_image_upload"] = "Product Image Upload";
    $p["error_product_image_required"] = "Product image is required! Please upload an image for your product.";
    $p["error_product_image_delete"] = "Before deleting the product image, you need to upload another image for the product!";
    $p["discounted_price"] = "Discounted Price";
    $p["brands"] = "Brands";
    $p["add_brand"] = "Add Brand";
    $p["shop_by_brand"] = "Shop By Brand";
    $p["brand"] = "Brand";
    $p["where_to_display"] = "Where to Display";
    $p["selling_on_the_site"] = "Selling on the Site";
    $p["ordinary_listing"] = "Ordinary Listing";
    $p["address_type"] = "Address Type";
    $p["msg_cart_shipping"] = "Please enter your shipping address and choose a shipping method!";
    $p["allow_vendors_change_shop_name"] = "Allow Vendors to Change Their Shop Name";
    $p["copy_code"] = "Copy Code";
    $p["coupon_valid_till"] = "Valid till: {field}";
    $p["see_products"] = "See Products";
    $p["copied"] = "Copied";
    $p["transaction_fee"] = "Transaction Fee";
    $p["select_your_country"] = "Please select your country to continue";
    $p["product_based_vat"] = "Product Based VAT";
    $p["no_vat"] = "No VAT";
    $p["digital_file"] = "Digital File";
    $p["upload_file"] = "Upload File";
    $p["add_external_download_link"] = "Add External Download Link";
    $p["warning_external_download_link"] = "For security reasons, it is recommended to upload digital files instead of adding an external download link";
    $p["warning_product_main_image"] = "You can click on the Main button on the images to select the main image of your product";
    $p["external_download_link"] = "External Download Link";
    $p["transaction_fee_exp"] = "If you do not want to charge a transaction fee, type 0";
    $p["x_url"] = "X URL";
    addTranslations($p);

    //delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='calculated_price';");
    runQuery("DELETE FROM language_translations WHERE `label`='featured_products_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='hide_vendor_contact_information';");
    runQuery("DELETE FROM language_translations WHERE `label`='latest_blog_posts_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='latest_products_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='product_image_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='search_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='system_settings';");
    runQuery("DELETE FROM language_translations WHERE `label`='twitter_url';");
    runQuery("DELETE FROM language_translations WHERE `label`='vat_included';");
    runQuery("DELETE FROM language_translations WHERE `label`='1_business_day';");
    runQuery("DELETE FROM language_translations WHERE `label`='2_3_business_days';");
    runQuery("DELETE FROM language_translations WHERE `label`='4_7_business_days';");
    runQuery("DELETE FROM language_translations WHERE `label`='8_15_business_days';");

    runQuery("UPDATE general_settings SET show_vendor_contact_information='1' WHERE id='1'");
    runQuery("UPDATE general_settings SET version='2.4' WHERE id='1'");
}

function addTranslations($translations)
{
    $languages = runQuery("SELECT * FROM languages;");
    if (!empty($languages->num_rows)) {
        while ($language = mysqli_fetch_array($languages)) {
            foreach ($translations as $key => $value) {
                $trans = runQuery("SELECT * FROM language_translations WHERE label ='" . $key . "' AND lang_id = " . $language['id']);
                if (empty($trans->num_rows)) {
                    runQuery("INSERT INTO `language_translations` (`lang_id`, `label`, `translation`) VALUES (" . $language['id'] . ", '" . $key . "', '" . $value . "');");
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modesy - Update Wizard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,700" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #444 !important;
            font-size: 14px;
            background: #007991;
            background: -webkit-linear-gradient(to left, #007991, #6fe7c2);
            background: linear-gradient(to left, #007991, #6fe7c2);
        }

        .logo-cnt {
            text-align: center;
            color: #fff;
            padding: 60px 0 60px 0;
        }

        .logo-cnt .logo {
            font-size: 42px;
            line-height: 42px;
        }

        .logo-cnt p {
            font-size: 22px;
        }

        .install-box {
            width: 100%;
            padding: 30px;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            margin: auto;
            background-color: #fff;
            border-radius: 4px;
            display: block;
            float: left;
            margin-bottom: 100px;
        }

        .form-input {
            box-shadow: none !important;
            border: 1px solid #ddd;
            height: 44px;
            line-height: 44px;
            padding: 0 20px;
        }

        .form-input:focus {
            border-color: #239CA1 !important;
        }

        .btn-custom {
            background-color: #239CA1 !important;
            border-color: #239CA1 !important;
            border: 0 none;
            border-radius: 4px;
            box-shadow: none;
            color: #fff !important;
            font-size: 16px;
            font-weight: 300;
            height: 40px;
            line-height: 40px;
            margin: 0;
            min-width: 105px;
            padding: 0 20px;
            text-shadow: none;
            vertical-align: middle;
        }

        .btn-custom:hover, .btn-custom:active, .btn-custom:focus {
            background-color: #239CA1;
            border-color: #239CA1;
            opacity: .8;
        }

        .tab-content {
            width: 100%;
            float: left;
            display: block;
        }

        .tab-footer {
            width: 100%;
            float: left;
            display: block;
        }

        .buttons {
            display: block;
            float: left;
            width: 100%;
            margin-top: 30px;
        }

        .title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
            margin-top: 0;
            text-align: center;
        }

        .sub-title {
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 30px;
            margin-top: 0;
            text-align: center;
        }

        .alert {
            text-align: center;
        }

        .alert strong {
            font-weight: 500 !important;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-sm-12 col-md-offset-2">
            <div class="row">
                <div class="col-sm-12 logo-cnt">
                    <h1>Modesy</h1>
                    <p>Welcome to the Update Wizard</p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="install-box">
                        <h2 class="title">Update from v2.3.x to v2.4.3</h2>
                        <br><br>
                        <div class="messages">
                            <?php if (!empty($error)) { ?>
                                <div class="alert alert-danger">
                                    <strong><?= $error; ?></strong>
                                </div>
                            <?php } ?>
                            <?php if (!empty($success)) { ?>
                                <div class="alert alert-success">
                                    <strong><?= $success; ?></strong>
                                    <style>.alert-info {
                                            display: none;
                                        }</style>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="step-contents">
                            <div class="tab-1">
                                <?php if (empty($success)): ?>
                                    <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                                        <div class="tab-footer text-center">
                                            <button type="submit" name="btn_submit" class="btn-custom">Update My Database</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>