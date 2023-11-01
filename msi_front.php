<?php

defined('ABSPATH') or die("Cannot access pages directly.");

function msi_update_news() {    

    if(is_product()) {

        $product = wc_get_product();

        $product->save();

    }

}

add_action( 'wp', 'msi_update_news' );

