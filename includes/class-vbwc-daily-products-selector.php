<?php

/**
* Product Selector
*/
class Vbwc_Daily_Products_Selector
{
	public $active_categories;


	function __construct()
	{
		var_dump( get_option( 'wcdp_limit_to_cat' ) );
	}

	public function ajax_get_posts() {

	}
}