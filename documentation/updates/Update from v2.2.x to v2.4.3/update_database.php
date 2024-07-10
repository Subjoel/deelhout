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
    updateFrom22To23();
    updateFrom23To24();
}

function updateFrom22To23()
{
    global $connection;

    runQuery("DROP TABLE ad_spaces;");
    runQuery("DROP TABLE ci_sessions;");
    runQuery("DROP TABLE fonts;");

    $tableAdSpaces = "CREATE TABLE `ad_spaces` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `lang_id` int(11) DEFAULT 1,
      `ad_space` text DEFAULT NULL,
      `ad_code_desktop` text DEFAULT NULL,
      `desktop_width` int(11) DEFAULT NULL,
      `desktop_height` int(11) DEFAULT NULL,
      `ad_code_mobile` text DEFAULT NULL,
      `mobile_width` int(11) DEFAULT NULL,
      `mobile_height` int(11) DEFAULT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tableCI = "CREATE TABLE `ci_sessions` (
    `id` varchar(128) NOT null,
    `ip_address` varchar(45) NOT null,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT null,
    `data` blob NOT null,
    KEY `ci_sessions_timestamp` (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tableEmailQueue = "CREATE TABLE `email_queue` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `email_type` varchar(50) DEFAULT NULL,
      `email_address` varchar(255) DEFAULT NULL,
      `email_subject` varchar(255) DEFAULT NULL,
      `email_data` text DEFAULT NULL,
      `email_priority` smallint(6) DEFAULT 2,
      `template_path` varchar(255) DEFAULT NULL,
      `created_at` timestamp NULL DEFAULT current_timestamp()
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    $tableFonts = "CREATE TABLE `fonts` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `font_name` varchar(255) DEFAULT NULL,
      `font_key` varchar(255) DEFAULT NULL,
      `font_url` varchar(2000) DEFAULT NULL,
      `font_family` varchar(500) DEFAULT NULL,
      `font_source` varchar(50) DEFAULT 'google',
      `has_local_file` tinyint(1) DEFAULT 0,
      `is_default` tinyint(1) DEFAULT 0
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    runQuery($tableAdSpaces);
    runQuery($tableCI);
    runQuery($tableEmailQueue);
    runQuery($tableFonts);
    sleep(1);

    runQuery("ALTER TABLE general_settings CHANGE custom_css_codes custom_header_codes mediumtext;");
    runQuery("ALTER TABLE general_settings CHANGE custom_javascript_codes custom_footer_codes mediumtext;");
    runQuery("ALTER TABLE general_settings CHANGE mail_library mail_service varchar(100) DEFAULT 'swift';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_api_key` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_secret_key` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `mailjet_email_address` varchar(255);");
    runQuery("ALTER TABLE general_settings ADD COLUMN `watermark_text` varchar(255) DEFAULT 'Modesy';");
    runQuery("ALTER TABLE general_settings ADD COLUMN `watermark_font_size` smallint(6) DEFAULT 42;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `watermark_image_large`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `watermark_image_mid`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `watermark_image_small`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `static_content_cache_system`;");
    runQuery("ALTER TABLE general_settings CHANGE product_cache_system cache_system TINYINT(1) DEFAULT 0;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `product_image_limit`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `max_file_size_image`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `max_file_size_video`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `max_file_size_audio`;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `show_customer_email_seller` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `show_customer_phone_seller` TINYINT(1) DEFAULT 1;");
    runQuery("ALTER TABLE general_settings ADD COLUMN `newsletter_image` varchar(255);");
    runQuery("ALTER TABLE general_settings DROP COLUMN `last_cron_update_long`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `mds_key`;");
    runQuery("ALTER TABLE general_settings DROP COLUMN `purchase_code`;");
    runQuery("ALTER TABLE orders ADD COLUMN `shipping` TEXT;");
    runQuery("ALTER TABLE order_products CHANGE product_quantity product_quantity INT;");
    runQuery("ALTER TABLE payment_gateways DROP COLUMN `locale`;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `product_image_limit` smallint(6) DEFAULT 20;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `max_file_size_image` bigint(20) DEFAULT 10485760;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `max_file_size_video` bigint(20) DEFAULT 31457280;");
    runQuery("ALTER TABLE product_settings ADD COLUMN `max_file_size_audio` bigint(20) DEFAULT 10485760;");
    runQuery("ALTER TABLE users DROP COLUMN `has_active_shop`;");
    runQuery("ALTER TABLE users DROP COLUMN `shop_name`;");
    runQuery("ALTER TABLE storage_settings DROP COLUMN `aws_base_url`;");

//update shipping
    $shippingAddresses = runQuery("SELECT * FROM order_shipping ORDER BY id;");
    if (!empty($shippingAddresses->num_rows)) {
        while ($item = mysqli_fetch_array($shippingAddresses)) {
            $array = [
                'sFirstName' => $item['shipping_first_name'],
                'sLastName' => $item['shipping_last_name'],
                'sEmail' => $item['shipping_email'],
                'sPhoneNumber' => $item['shipping_phone_number'],
                'sAddress' => $item['shipping_address'],
                'sCountry' => $item['shipping_country'],
                'sState' => $item['shipping_state'],
                'sCity' => $item['shipping_city'],
                'sZipCode' => $item['shipping_zip_code'],
                'bFirstName' => $item['billing_first_name'],
                'bLastName' => $item['billing_last_name'],
                'bEmail' => $item['billing_email'],
                'bPhoneNumber' => $item['billing_phone_number'],
                'bAddress' => $item['billing_address'],
                'bCountry' => $item['billing_country'],
                'bState' => $item['billing_state'],
                'bCity' => $item['billing_city'],
                'bZipCode' => $item['billing_zip_code']
            ];
            $serialized = serialize($array);
            $serialized = mysqli_real_escape_string($connection, $serialized);
            runQuery("Update orders SET `shipping`='" . $serialized . "' WHERE `id`=" . $item['order_id'] . " ;");
        }
    }

    $sqlFonts = "INSERT INTO `fonts` (`id`, `font_name`, `font_key`, `font_url`, `font_family`, `font_source`, `has_local_file`, `is_default`) VALUES
(1, 'Arial', 'arial', NULL, 'font-family: Arial, Helvetica, sans-serif', 'local', 0, 1),
(2, 'Arvo', 'arvo', '<link href=\"https://fonts.googleapis.com/css?family=Arvo:400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Arvo\", Helvetica, sans-serif', 'google', 0, 0),
(3, 'Averia Libre', 'averia-libre', '<link href=\"https://fonts.googleapis.com/css?family=Averia+Libre:300,400,700&display=swap\" rel=\"stylesheet\">\r\n', 'font-family: \"Averia Libre\", Helvetica, sans-serif', 'google', 0, 0),
(4, 'Bitter', 'bitter', '<link href=\"https://fonts.googleapis.com/css?family=Bitter:400,400i,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Bitter\", Helvetica, sans-serif', 'google', 0, 0),
(5, 'Cabin', 'cabin', '<link href=\"https://fonts.googleapis.com/css?family=Cabin:400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Cabin\", Helvetica, sans-serif', 'google', 0, 0),
(6, 'Cherry Swash', 'cherry-swash', '<link href=\"https://fonts.googleapis.com/css?family=Cherry+Swash:400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Cherry Swash\", Helvetica, sans-serif', 'google', 0, 0),
(7, 'Encode Sans', 'encode-sans', '<link href=\"https://fonts.googleapis.com/css?family=Encode+Sans:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Encode Sans\", Helvetica, sans-serif', 'google', 0, 0),
(8, 'Helvetica', 'helvetica', NULL, 'font-family: Helvetica, sans-serif', 'local', 0, 1),
(9, 'Hind', 'hind', '<link href=\"https://fonts.googleapis.com/css?family=Hind:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Hind\", Helvetica, sans-serif', 'google', 0, 0),
(10, 'Josefin Sans', 'josefin-sans', '<link href=\"https://fonts.googleapis.com/css?family=Josefin+Sans:300,400,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Josefin Sans\", Helvetica, sans-serif', 'google', 0, 0),
(11, 'Kalam', 'kalam', '<link href=\"https://fonts.googleapis.com/css?family=Kalam:300,400,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Kalam\", Helvetica, sans-serif', 'google', 0, 0),
(12, 'Khula', 'khula', '<link href=\"https://fonts.googleapis.com/css?family=Khula:300,400,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Khula\", Helvetica, sans-serif', 'google', 0, 0),
(13, 'Lato', 'lato', '<link href=\"https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Lato\", Helvetica, sans-serif', 'google', 0, 0),
(14, 'Lora', 'lora', '<link href=\"https://fonts.googleapis.com/css?family=Lora:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Lora\", Helvetica, sans-serif', 'google', 0, 0),
(15, 'Merriweather', 'merriweather', '<link href=\"https://fonts.googleapis.com/css?family=Merriweather:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Merriweather\", Helvetica, sans-serif', 'google', 0, 0),
(16, 'Montserrat', 'montserrat', '<link href=\"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Montserrat\", Helvetica, sans-serif', 'google', 0, 0),
(17, 'Mukta', 'mukta', '<link href=\"https://fonts.googleapis.com/css?family=Mukta:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Mukta\", Helvetica, sans-serif', 'google', 0, 0),
(18, 'Nunito', 'nunito', '<link href=\"https://fonts.googleapis.com/css?family=Nunito:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Nunito\", Helvetica, sans-serif', 'google', 0, 0),
(19, 'Open Sans', 'open-sans', '<link href=\"https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap\" rel=\"stylesheet\">', 'font-family: \"Open Sans\", Helvetica, sans-serif', 'local', 1, 0),
(20, 'Oswald', 'oswald', '<link href=\"https://fonts.googleapis.com/css?family=Oswald:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Oswald\", Helvetica, sans-serif', 'google', 0, 0),
(21, 'Oxygen', 'oxygen', '<link href=\"https://fonts.googleapis.com/css?family=Oxygen:300,400,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Oxygen\", Helvetica, sans-serif', 'google', 0, 0),
(22, 'Poppins', 'poppins', '<link href=\"https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap&subset=devanagari,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Poppins\", Helvetica, sans-serif', 'local', 1, 0),
(23, 'PT Sans', 'pt-sans', '<link href=\"https://fonts.googleapis.com/css?family=PT+Sans:400,700&display=swap&subset=cyrillic,cyrillic-ext,latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"PT Sans\", Helvetica, sans-serif', 'google', 0, 0),
(24, 'Raleway', 'raleway', '<link href=\"https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">\r\n', 'font-family: \"Raleway\", Helvetica, sans-serif', 'google', 0, 0),
(25, 'Roboto', 'roboto', '<link href=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Roboto\", Helvetica, sans-serif', 'google', 0, 0),
(26, 'Roboto Condensed', 'roboto-condensed', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Condensed:300,400,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Condensed\", Helvetica, sans-serif', 'google', 0, 0),
(27, 'Roboto Slab', 'roboto-slab', '<link href=\"https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Roboto Slab\", Helvetica, sans-serif', 'google', 0, 0),
(28, 'Rokkitt', 'rokkitt', '<link href=\"https://fonts.googleapis.com/css?family=Rokkitt:300,400,500,600,700&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\">\r\n', 'font-family: \"Rokkitt\", Helvetica, sans-serif', 'google', 0, 0),
(29, 'Source Sans Pro', 'source-sans-pro', '<link href=\"https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext,vietnamese\" rel=\"stylesheet\">', 'font-family: \"Source Sans Pro\", Helvetica, sans-serif', 'google', 0, 0),
(30, 'Titillium Web', 'titillium-web', '<link href=\"https://fonts.googleapis.com/css?family=Titillium+Web:300,400,600,700&display=swap&subset=latin-ext\" rel=\"stylesheet\">', 'font-family: \"Titillium Web\", Helvetica, sans-serif', 'google', 0, 0),
(31, 'Ubuntu', 'ubuntu', '<link href=\"https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700&display=swap&subset=cyrillic,cyrillic-ext,greek,greek-ext,latin-ext\" rel=\"stylesheet\">', 'font-family: \"Ubuntu\", Helvetica, sans-serif', 'google', 0, 0),
(32, 'Verdana', 'verdana', NULL, 'font-family: Verdana, Helvetica, sans-serif', 'local', 0, 1),
(33, 'Work Sans', 'work-sans', '<link href=\"https://fonts.googleapis.com/css?family=Work+Sans:300,400,500,600&display=swap&subset=latin-ext,vietnamese\" rel=\"stylesheet\"> ', 'font-family: \"Work Sans\", Helvetica, sans-serif', 'google', 0, 0),
(34, 'Libre Baskerville', 'libre-baskerville', '<link href=\"https://fonts.googleapis.com/css?family=Libre+Baskerville:400,400i&display=swap&subset=latin-ext\" rel=\"stylesheet\"> ', 'font-family: \"Libre Baskerville\", Helvetica, sans-serif', 'google', 0, 0),
(35, 'Signika', 'signika', '<link href=\"https://fonts.googleapis.com/css2?family=Signika:wght@300;400;600;700&display=swap\" rel=\"stylesheet\">', 'font-family: \'Signika\', sans-serif;', 'google', 0, 0),
(36, 'Tajawal', 'tajawal', '<link href=\"https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap\" rel=\"stylesheet\">', 'font-family: \'Tajawal\', sans-serif;', 'google', 0, 0);";
    runQuery($sqlFonts);

//delete routes
    runQuery("INSERT INTO `routes` (`route_key`, `route`) VALUES ('edit_profile', 'edit-profile')");
    runQuery("INSERT INTO `routes` (`route_key`, `route`) VALUES ('register_success', 'register-success')");
    runQuery("DELETE FROM routes WHERE `route_key`='conversation';");
    runQuery("DELETE FROM routes WHERE `route_key`='update_profile';");
    runQuery("DELETE FROM routes WHERE `route_key`='pending_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='hidden_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='drafts';");
    runQuery("DELETE FROM routes WHERE `route_key`='completed_sales';");
    runQuery("DELETE FROM routes WHERE `route_key`='expired_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='cover_image';");
    runQuery("DELETE FROM routes WHERE `route_key`='sold_products';");
    runQuery("DELETE FROM routes WHERE `route_key`='cancelled_sales';");

    sleep(1);

//add new translations
    $p = array();
    $p["cash_on_delivery_vendor_exp"] = "Sell your products with pay on delivery option";
    $p["fade"] = "Fade";
    $p["slide"] = "Slide";
    $p["mail_service"] = "Mail Service";
    $p["smtp"] = "SMTP";
    $p["mailjet_email_address"] = "Mailjet Email Address";
    $p["mailjet_email_address_exp"] = "The address you created your Mailjet account with";
    $p["generate_sitemap"] = "Generate Sitemap";
    $p["banner_desktop"] = "Desktop Banner";
    $p["banner_desktop_exp"] = "This ad will be displayed on screens larger than 992px";
    $p["banner_mobile"] = "Mobile Banner";
    $p["banner_mobile_exp"] = "This ad will be displayed on screens smaller than 992px";
    $p["ad_size"] = "Ad Size";
    $p["width"] = "Width";
    $p["height"] = "Height";
    $p["create_ad_exp"] = "If you don not have an ad code, you can create an ad code by selecting an image and adding an URL";
    $p["download_database_backup"] = "Download Database Backup";
    $p["activation_email_sent"] = "Activation email has been sent!";
    $p["warning_edit_profile_image"] = "Click on the save changes button after selecting your image";
    $p["cover_image_type"] = "Cover Image Type";
    $p["if_review_already_added"] = "If you have already added a review, your review will be updated.";
    $p["font_size"] = "Font Size";
    $p["show_customer_email_seller"] = "Show Customer Email to Seller";
    $p["show_customer_phone_number_seller"] = "Show Customer Phone Number to Seller";
    $p["accept_cookies"] = "Accept Cookies";
    $p["custom_header_codes"] = "Custom Header Codes";
    $p["custom_header_codes_exp"] = "These codes will be added to the header of the site";
    $p["custom_footer_codes"] = "Custom Footer Codes";
    $p["custom_footer_codes_exp"] = "These codes will be added to the footer of the site";
    $p["highest_rating"] = "Highest Rating";
    addTranslations($p);

//delete old translations
    runQuery("DELETE FROM language_translations WHERE `label`='blog_post_details_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='blog_post_details_sidebar_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='completed_payouts';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_category';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_custom_field';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_language';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_option';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_page';");
    runQuery("DELETE FROM language_translations WHERE `label`='confirm_post';");
    runQuery("DELETE FROM language_translations WHERE `label`='cover_image';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_css_codes';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_css_codes_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_javascript_codes';");
    runQuery("DELETE FROM language_translations WHERE `label`='custom_javascript_codes_exp';");
    runQuery("DELETE FROM language_translations WHERE `label`='download_sitemap';");
    runQuery("DELETE FROM language_translations WHERE `label`='mail_library';");
    runQuery("DELETE FROM language_translations WHERE `label`='middle';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_category_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_category_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_custom_field_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_custom_field_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_language_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_language_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_option_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_page_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_page_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_post_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_post_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_product_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_slider_added';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_slider_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='msg_user_deleted';");
    runQuery("DELETE FROM language_translations WHERE `label`='products_sidebar_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='product_bottom_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='product_cache_system';");
    runQuery("DELETE FROM language_translations WHERE `label`='profile_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='profile_sidebar_ad_space';");
    runQuery("DELETE FROM language_translations WHERE `label`='static_content_cache_system';");
    runQuery("DELETE FROM language_translations WHERE `label`='update_sitemap';");
    runQuery("DELETE FROM language_translations WHERE `label`='warning_static_content_cache_system';");

    runQuery("UPDATE general_settings SET watermark_vrt_alignment='center' WHERE id='1'");
    runQuery("UPDATE general_settings SET watermark_hor_alignment='center' WHERE id='1'");
    runQuery("UPDATE general_settings SET version='2.3' WHERE id='1'");
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
                        <h2 class="title">Update from v2.2.x to v2.4.3</h2>
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