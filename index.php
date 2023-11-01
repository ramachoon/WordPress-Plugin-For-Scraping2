<?php

// An example of using php-webdriver.
// Do not forget to run composer install before. You must also have Selenium server started and listening on port 4444.

namespace Facebook\WebDriver;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverExpectedCondition;


require_once('vendor/autoload.php');

// This is where Selenium server 2/3 listens by default. For Selenium 4, Chromedriver or Geckodriver, use https://msi.hsu.edu.hk/en/news-and-announcement
$host = 'http://localhost:4444/wd/hub';
// $host = 'https://hub-cloud.browserstack.com/wd/hub';
// https://hub-cloud.browserstack.com/wd/hub
// https://hub.lambdatest.com/wd/hub

$capabilities = DesiredCapabilities::chrome();

$driver = RemoteWebDriver::create($host, $capabilities);

// navigate to Selenium page on Wikipedia
$driver->get('https://msi.hsu.edu.hk/en/news-and-announcement');


// Assuming you have already created a WebDriver instance and navigated to the desired page



echo "Start---\n";


// Assuming you have already initialized the WebDriver and navigated to the desired page

// Find all the li elements with the class "paginationjs-page"
$liElements = $driver->findElements(WebDriverBy::cssSelector('.paginationjs-page'));

// Initialize a variable to store the largest data-num value
$largestDataNum = 0;

// Loop through the li elements and compare the data-num values
foreach ($liElements as $liElement) {
    // Get the value of the data-num attribute
    $dataNum = $liElement->getAttribute('data-num');
    echo $dataNum ;
    // Convert the data-num value to an integer
    $dataNum = intval($dataNum);

    // Compare the data-num value with the current largest value
    if ($dataNum > $largestDataNum) {
        $largestDataNum = $dataNum;
    }
}

// The $largestDataNum variable now contains the largest data-num value
echo $largestDataNum;













// // Find all elements with the class name "rt-col-md-12"
// $elements = $driver->findElements(WebDriverBy::cssSelector('.rt-col-md-12'));

// // Initialize an empty array to store the news data
// $newsList = array();
// $i =0;
// // Loop through the elements and extract the desired information
// foreach ($elements as $element) {
//     // Find the title element within the current element
//     $titleElement = $element->findElement(WebDriverBy::cssSelector('.entry-title a'));

//     // Get the title text
//     $preTitle = $titleElement->getText();

//     // Initialize variables for the English and Chinese titles
//     $englishTitle = '';
//     $chineseTitle = '';

//     // Match the English title using the [:en] and [: delimiters
//     preg_match('/\[:en\](.*?)\[:/', $preTitle, $matches);
//     if (isset($matches[1])) {
//         $englishTitle = $matches[1];
//     }

//     // Match the Chinese title using the [:hk] and [: delimiters
//     preg_match('/\[:hk\](.*?)\[:/', $preTitle, $matches);
//     if (isset($matches[1])) {
//         $chineseTitle = $matches[1];
//     }

//     // Get the news item URL
//     $newsItemUrl = $titleElement->getAttribute('href');

//     // Find the cover image element within the current element
//     $imageElement = $element->findElement(WebDriverBy::cssSelector('.rt-img-responsive'));

//     // Get the source URL of the cover image
//     $featuredImgSrc = $imageElement->getAttribute('src');

//     // Find the date element within the current element
//     $dateElement = $element->findElement(WebDriverBy::cssSelector('span.date-meta'));

//     // Get the date text
//     $date = $dateElement->getText();

//     // Extract the date part from the text
//     $date = trim(substr($date, strpos($date, ' ') + 1));

//     // Create an associative array with the extracted data
//     $newsData = array(
//         'title' => $englishTitle,
//         'chineseTitle' => $chineseTitle,
//         'newsItemUrl' => $newsItemUrl,
//         'featuredImgSrc' => $featuredImgSrc,
//         'date' => $date
//     );

//     // Call the function to save the news data
//     // save_new_post_msi_msi_msi($newsData);
// }





// // // Assuming you have already defined the save_new_post_msi_msi_msi function to save the news data
// // function save_new_post_msi_msi_msi($newsData) {
// //     // Your implementation to save the news data goes here
// // }










// // write 'PHP' in the search box
// $driver->findElement(WebDriverBy::id('searchInput')) // find search input element
//     ->sendKeys('PHP') // fill the search box
//     ->submit(); // submit the whole form

// // wait until 'PHP' is shown in the page heading element
// $driver->wait()->until(
//     WebDriverExpectedCondition::elementTextContains(WebDriverBy::className('firstHeading'), 'PHP')
// );

// // print title of the current page to output
// echo "The title is '" . $driver->getTitle() . "'\n";

// // print URL of current page to output
// echo "The current URL is '" . $driver->getCurrentURL() . "'\n";

// // find element of 'History' item in menu
// $historyButton = $driver->findElement(
//     WebDriverBy::cssSelector('#ca-history a')
// );

// // read text of the element and print it to output
// echo "About to click to button with text: '" . $historyButton->getText() . "'\n";

// // click the element to navigate to revision history page
// $historyButton->click();

// // wait until the target page is loaded
// $driver->wait()->until(
//     WebDriverExpectedCondition::titleContains('Revision history')
// );

// // print the title of the current page
// echo "The title is '" . $driver->getTitle() . "'\n";

// // print the URI of the current page

// echo "The current URI is '" . $driver->getCurrentURL() . "'\n";

// // delete all cookies
// $driver->manage()->deleteAllCookies();

// // add new cookie
// $cookie = new Cookie('cookie_set_by_selenium', 'cookie_value');
// $driver->manage()->addCookie($cookie);

// // dump current cookies to output
// $cookies = $driver->manage()->getCookies();
// print_r($cookies);

// terminate the session and close the browser
$driver->quit();