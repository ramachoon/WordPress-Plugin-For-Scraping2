<?php
namespace Facebook\WebDriver;

if (!function_exists('file_get_html')) {
    require_once 'includes/simple_html_dom.php';
}


use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once __DIR__ . '/vendor/autoload.php';


set_time_limit(3000); // Set the time limit to 20 minutes

/**

 * Uninstall hook

 */

function msiUninstall() {    


}



function msiDeactivation() {



}



function msiActivation($networkWide) {    


    // Check PHP version

    if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50400) {

        deactivate_plugins(WBS_BASENAME);

        wp_die(

            '<p>The <strong>HSUHK Web scraping Tool</strong> plugin requires PHP version 5.4 or greater.</p>',

            'Plugin Activation Error',

            ['response' => 200, 'back_link' => TRUE]

        );

    }

}




add_action("wp_ajax_nopriv_msi_update_news", "msiUpdateNews");

add_action("wp_ajax_msi_update_news", "msiUpdateNews");

function msiUpdateNews() {	

    global $wpdb;    

    if ( !wp_verify_nonce( $_REQUEST['nonce'], "msi_update_news_nonce")) {      	

        echo json_encode(array('error'=>1, 'message'=>'Invalid Request'));

        die();

    }


    // This is where Selenium server 2/3 listens by default. For Selenium 4, Chromedriver or Geckodriver, use http://localhost:4444/
    $host = 'https://msi.hsu.edu.hk/en/news-and-announcement';

    $capabilities = DesiredCapabilities::chrome();

    $driver = RemoteWebDriver::create($host, $capabilities);

    // navigate to Selenium page on Wikipedia
    $driver->get('https://en.wikipedia.org/wiki/Selenium_(software)');

    // write 'PHP' in the search box
    $driver->findElement(WebDriverBy::id('searchInput')) // find search input element
        ->sendKeys('PHP') // fill the search box
        ->submit(); // submit the whole form









    for($k = 1 ; $k < 50; $k++ ) {
        try {


            $url = 'https://msi.hsu.edu.hk/wp-admin/admin-ajax.php';

            $curl = curl_init($url);
            
            // Set the request method to POST
            curl_setopt($curl, CURLOPT_POST, true);
            
            // Set the POST data
            $data = 'action=tpgLayoutAjaxAction&paged='.$k.'&rttpg_nonce=51f186db70&scID=5545';
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            
            // Set the return transfer option
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            
            // Execute the cURL request
            $response = curl_exec($curl);
            
            // Check for errors
            if ($response === false) {
                $error = curl_error($curl);
                echo "cURL error: " . $error;
            } else {
                // Decode the JSON response
                $jsonData = json_decode($response, true);

                // Access the data in the JSON response
                if ($jsonData) {
                    $html = new simple_html_dom();
                    $html->load($jsonData['data']);
                    $newsList = array();
                    $i = 0;
                    // Collect all user’s reviews into an array
                    if (!empty($html)) {
                        foreach ($html->find(".rt-col-md-12") as $divClass) {
                            $titleTag = $divClass->find(".entry-title a", 0);
                            $preTitle = $titleTag->innertext;
                            $englishTitle = '';
                            $chineseTitle = '';
                            // Match the English title using the [:en] and [; delimiters
                            preg_match('/\[:en\](.*?)\[:/', $preTitle, $matches);
                            if (isset($matches[1])) {
                                $englishTitle = $matches[1];
                            }

                            // Match the Chinese title using the [:hk] and [: delimiters
                            preg_match('/\[:hk\](.*?)\[:/', $preTitle, $matches);
                            if (isset($matches[1])) {
                                $chineseTitle = $matches[1];
                            }

                            $newsList[$i]['title'] = $englishTitle;
                            $newsList[$i]['chineseTitle'] = $chineseTitle;
                            $newsList[$i]['newsItemUrl'] = rtrim( $titleTag->href, "'");

                            // cover image
                            $image = $divClass->find(".rt-img-responsive", 0);
                            if ($image) {
                                $newsList[$i]['featuredImgSrc'] = $image->src;
                            }
                            // Assuming you have already loaded the HTML string into the $html variable using Simple HTML DOM

                            // Find the <span> element with the class "date-meta"
                            $spanElement = $divClass->find('span.date-meta', 0);

                            // Get the text content of the <span> element
                            $date = $spanElement->plaintext;

                            // Extract the date part from the text content
                            $date = trim(substr($date, strpos($date, ' ') + 1));

                            if ($date) {
                                $newsList[$i]['date'] = $date;
                            }
                            try {
                                save_new_post_msi_msi_msi($newsList[$i]);
                            } catch (Exception $e) {
                                continue;
                            }
                            $i++;
                        }

                    }
                } else {
                    break;
                }
            }
            
            // Close the cURL session
            curl_close($curl);
        }
        catch ( Exception $e) {
            echo $i.'-----';
        }

    }
    echo json_encode(array('success' => true));
    exit;
}


function save_new_post_msi_msi_msi($news) {
    $scm_hsu_edu_hk_home_url = 'https://msi.hsu.edu.hk';
    $category = "news";
    // Create a new WP_Query instance to check for existing posts
    $query = new WP_Query(array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'language',  // Replace with your actual taxonomy name
                'field' => 'slug',  // Replace with 'name' if you are using language names instead of slugs
                'terms' => 'en',
            ),
        ),
        'category_name' => $category,
        'posts_per_page' => 1,
        'title' => $news['title'],
    ));

    if (!$query->have_posts()) {

        try {
            $newsEngContent = file_get_html($news['newsItemUrl'], false);
            if(!empty($newsEngContent)) {
                $contentEngTag = $newsEngContent->find("#gk-mainbody article", 0);
                $elementsToRemove = $contentEngTag->find('header', 0);
                if ($elementsToRemove) {
                    $elementsToRemove->outertext = '';
                }
                $content = $contentEngTag->save();
                $post_eng_data = array(
                    'post_type' => 'post',
                    'post_title'    => wp_strip_all_tags($news['title']),
                    'post_content'  => $content,
                    'post_author'   => get_current_user_id() ? get_current_user_id() : 1,
                    'post_excerpt'  => "",
                    'post_date'     => date('Y/m/d', strtotime($news['date']))
                );
                $english_post_id  = wp_insert_post($post_eng_data);
    
                wp_set_post_terms($english_post_id , 'en', 'language');
    
                $images = $contentEngTag ? $contentEngTag->find('img') : [];
                if($images !== null && isset($images) && !empty($images)) {
                    foreach ($images as $image) {
                        $origSrc = $src = trim($image->src);
                        if (strpos($src, 'data:image') === 0) {
                            // Skip current iteration and move to the next
                            continue;
                        }
                        if(strpos($src, 'http') === FALSE) {
                            $src = $scm_hsu_edu_hk_home_url.$src;
                        }
                        // Download to temp folder
                        $tmp = download_url( $src );
                        $file_array = array();
                        $newSrc = '';
                
                        preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $src, $matches);
                        if (isset($matches[0]) && $matches[0]) {
                            $file_array['name'] = basename($matches[0]);
                            $file_array['tmp_name'] = $tmp;
                            if ( is_wp_error( $tmp ) ) {
                                @unlink($file_array['tmp_name']);
                                $file_array['tmp_name'] = '';
                            } else {
                                // do the validation and storage stuff
                                $imageId = media_handle_sideload( $file_array, $english_post_id, '');
                
                                // If error storing permanently, unlink
                                if ( is_wp_error($imageId) ) {
                                    @unlink($file_array['tmp_name']);
                                } else {
                                    $newSrc = wp_get_attachment_url($imageId);
                                }
                            }
                        } else {
                            @unlink($tmp);
                        }
                
                        // Replace images url in code
                        if ($newSrc) {
                            $contentEngTag = str_replace(htmlentities($origSrc), $newSrc, $contentEngTag);
                        }
                    }
                }
                $media_data = array(
                    'name'     => basename($news['featuredImgSrc']),
                    'tmp_name' => download_url($news['featuredImgSrc'])
                );
            
                $media_id = media_handle_sideload($media_data, $english_post_id );
            
                set_post_thumbnail( $english_post_id , $media_id );
                $english_post_id  = wp_update_post(
                    array(
                        'ID'            => (int) $english_post_id ,
                        'post_status'   => 'publish',
                        'post_date'     => date('Y/m/d', strtotime($news['date'])),
                        'post_content'  => $contentEngTag ? $contentEngTag : ''
                    )
                );
            
            
                $engUrl = $news['newsItemUrl'];
                $chiUrl = "";

                if (strpos($engUrl, "/en/") !== false) {
                    $chiUrl = str_replace("/en/", "/hk/", $engUrl);
                } else {
                    $path = parse_url($engUrl, PHP_URL_PATH);
                    $postEndpoint = basename($path);
                    $chiUrl = $scm_hsu_edu_hk_home_url. "/hk/".$postEndpoint;
                }
                
                $newsChiContent = file_get_html($chiUrl, false);
                if(!empty($newsChiContent)) {
                    $contentTag = $newsChiContent->find("#gk-mainbody article", 0);
                    $elementsToRemove = $contentTag->find('header', 0);
                    if ($elementsToRemove) {
                        $elementsToRemove->outertext = '';
                    }
                    $content = $contentTag->save();
                    $categoryNewsZh = "news-zh";
                    $chinese_post = array(
                        'post_type' => 'post',
                        'post_title'    => wp_strip_all_tags($news['chineseTitle']),
                        'post_content'  => $content,
                        'post_author'   => get_current_user_id() ? get_current_user_id() : 1,
                        'post_excerpt'  => "",
                        'post_date'     => date('Y/m/d', strtotime($news['date']))
                    );
            
                    $chinese_post_id = wp_insert_post($chinese_post);
                    wp_set_post_terms($chinese_post_id, 'zh', 'language');
                    
                    $chinese_images = $contentTag ? $contentTag->find('img') : [];
                    if($chinese_images !== null && isset($chinese_images) && !empty($chinese_images)) {
                        foreach ($chinese_images as $image) {
                            $origSrc = $src = trim($image->src);
                            if (strpos($src, 'data:image') === 0) {
                                // Skip current iteration and move to the next
                                continue;
                            }
    
                            if(strpos($src, 'http') === FALSE) {
                                $src = $scm_hsu_edu_hk_home_url.$src;
                            }
                            // Download to temp folder
                            $tmp = download_url( $src );
                            $file_array = array();
                            $newSrc = '';
                
                            preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $src, $matches);
                            if (isset($matches[0]) && $matches[0]) {
                                $file_array['name'] = basename($matches[0]);
                                $file_array['tmp_name'] = $tmp;
                                if ( is_wp_error( $tmp ) ) {
                                    @unlink($file_array['tmp_name']);
                                    $file_array['tmp_name'] = '';
                                } else {
                                    // do the validation and storage stuff
                                    $imageId = media_handle_sideload( $file_array, $chinese_post_id, '');
                
                                    // If error storing permanently, unlink
                                    if ( is_wp_error($imageId) ) {
                                        @unlink($file_array['tmp_name']);
                                    } else {
                                        $newSrc = wp_get_attachment_url($imageId);
                                    }
                                }
                            } else {
                                @unlink($tmp);
                            }
                
                            // Replace images url in code
                            if ($newSrc) {
                                $contentTag = str_replace(htmlentities($origSrc), $newSrc, $contentTag);
                            }
                        }
                    }
                    $media_data = array(
                        'name'     => basename($news['featuredImgSrc']),
                        'tmp_name' => download_url($news['featuredImgSrc'])
                    );
            
                    $media_id = media_handle_sideload($media_data, $chinese_post_id);
            
                    set_post_thumbnail( $chinese_post_id, $media_id );
                    $chinese_post_id = wp_update_post(
                        array(
                            'ID'            => (int) $chinese_post_id,
                            'post_status'   => 'publish',
                            'post_date'     => date('Y/m/d', strtotime($news['date'])),
                            'post_content'  => $contentTag ? $contentTag : ''
                        )
                    );
                }
    
    
                if (function_exists('pll_save_post_translations')) {
                    pll_save_post_translations(array('en' => $english_post_id, 'zh' => $chinese_post_id));
                }
            }
        } catch ( Exception $e) {
            echo "Error Handle-".$e->getMessage();
        }

    }
}




// * * * * * curl -s http://localhost/bingo/wp-json/msi_scraping/v1
function handle_msi_custom_endpoint() {
    // Import WordPress core files
    require_once(ABSPATH . 'wp-load.php');
    require_once(ABSPATH . 'wp-admin/includes/admin.php');
    global $wpdb;

    $currentDate = date("Y/m/d");
    $break = false;


    for($k = 1 ; $k < 30; $k++ ) {
        // Initialize cURL
        $curl = curl_init();

        // Set the URL to send the POST request to
        $url = 'https://msi.hsu.edu.hk/wp-admin/admin-ajax.php';

        // Set the POST data as an array of query parameters
        $data = array(
            'action' => 'tpgLayoutAjaxAction',
            'paged' => $k,
            'rttpg_nonce' => '51f186db70',
            'scID' => '5545'
        );

        // Convert the data array to a query string
        $queryString = http_build_query($data);

        // Set the cURL options
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $queryString);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // Execute the cURL request
        $response = curl_exec($curl);

        // Close the cURL session
        curl_close($curl);

        // Decode the JSON response
        $jsonData = json_decode($response, true);

        // Access the data in the JSON response
        if ($jsonData) {
            $html = new simple_html_dom();
            $html->load($jsonData['data']);

            $newsList = array();
            $i = 0;
            // Collect all user’s reviews into an array
            if (!empty($html)) {
                foreach ($html->find(".rt-col-md-12") as $divClass) {
                    $titleTag = $divClass->find(".entry-title a", 0);
                    $preTitle = $titleTag->innertext;
                    $englishTitle = '';
                    $chineseTitle = '';
                    // Match the English title using the [:en] and [; delimiters
                    preg_match('/\[:en\](.*?)\[:/', $preTitle, $matches);
                    if (isset($matches[1])) {
                        $englishTitle = $matches[1];
                    }

                    // Match the Chinese title using the [:hk] and [: delimiters
                    preg_match('/\[:hk\](.*?)\[:/', $preTitle, $matches);
                    if (isset($matches[1])) {
                        $chineseTitle = $matches[1];
                    }

                    $newsList[$i]['title'] = $englishTitle;
                    $newsList[$i]['chineseTitle'] = $chineseTitle;

                    $newsList[$i]['newsItemUrl'] = $titleTag->href;
                    // cover image
                    $image = $divClass->find(".rt-img-responsive", 0);
                    if ($image) {
                        $newsList[$i]['featuredImgSrc'] = $image->src;
                    }
                    // Assuming you have already loaded the HTML string into the $html variable using Simple HTML DOM

                    // Find the <span> element with the class "date-meta"
                    $spanElement = $divClass->find('span.date-meta', 0);

                    // Get the text content of the <span> element
                    $date = $spanElement->plaintext;

                    // Extract the date part from the text content
                    $date = trim(substr($date, strpos($date, ' ') + 1));

                    if ($date) {
                        $newsList[$i]['date'] = $date;
                    }

                    $thisDate = date('Y/m/d', strtotime($newsList[$i]['date']));
                    if ($currentDate === $thisDate) {
                        try {
                            save_new_post_msi_msi_msi($newsList[$i]);
                        } catch (Exception $e) {
                            continue;
                        }
                    } else {
                        $break = true;
                    }
                    $i++;
                }

            }
        } else {
            break;
        }


    }

    return rest_ensure_response("OK");
}


/**
 * This function is where we register our routes for our example endpoint.
 */
function prefix_register_msi_scraping_routes() {
    // register_rest_route() handles more arguments but we are going to stick to the basics for now.
    register_rest_route( 'msi_scraping', '/v1', array(
        // By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
        'methods'  => WP_REST_Server::READABLE,
        // Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
        'callback' => 'handle_msi_custom_endpoint',
        'permission_callback' => '__return_true'
    ) );
}

add_action( 'rest_api_init', 'prefix_register_msi_scraping_routes' );