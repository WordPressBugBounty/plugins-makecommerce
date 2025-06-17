<?php

namespace MakeCommerce\Shipping;

/**
 * All functionality that has to do with shipping and products
 * 
 * @since 3.0.0
 */

class Product extends \MakeCommerce\Shipping {

	private $loader;
	
	/**
	 * Constructs Label class, defines loader and hooks
	 * 
	 * @since 3.0.0
	 */
    public function __construct( \MakeCommerce\Loader $loader ) {
        $this->loader = $loader;
	}
}