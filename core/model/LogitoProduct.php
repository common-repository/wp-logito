<?php

class
LogitoProduct {

	private $productTitle;
	private $productPrice;
	private $productWeight;
	private $productID;

	function __construct( $postId = null ) {
		if ( $postId == null ) {
			return false;
		}

		// Get woocommerce product
		$product = new WC_Product( $postId );

		// Check if id belong to a woocommerce product
		if ( $product->post->post_type != "product" ) {
			return false;
		}

		$this->setProductID( "product_" . $product->get_id() );
		$this->setProductTitle( $product->get_title() );
		$this->setProductPrice( ( $product->is_on_sale() ) ? $product->get_sale_price() : $product->get_regular_price() );
		$this->setProductWeight( strval( intval( wc_get_weight( $product->get_weight(), 'g' ) ) ) );

		return true;
	}

	/**
	 * @return mixed
	 */
	public function getProductTitle() {
		return $this->productTitle;
	}

	/**
	 * @param mixed $productTitle
	 */
	public function setProductTitle( $productTitle ) {
		$this->productTitle = $productTitle;
	}

	/**
	 * @return mixed
	 */
	public function getProductPrice() {
		return $this->productPrice;
	}

	/**
	 * This method will convert price in case
	 * of different currency than IR Rials
	 *
	 * @param mixed $productPrice
	 */
	public function setProductPrice( $productPrice ) {
		// Common Toman abbreviations which used in woocommerce
		$TomanAbbreviations = array( "IRT", "IRHT" );

		if ( in_array( get_woocommerce_currency(), $TomanAbbreviations ) ) // Convert toman to rial
		{
			$productPrice = $productPrice * 10;
		}

		$this->productPrice = $productPrice;
	}

	/**
	 * @return mixed
	 */
	public function getProductWeight() {
		return $this->productWeight;
	}

	/**
	 * @param mixed $productWeight
	 */
	public function setProductWeight( $productWeight ) {
		$this->productWeight = $productWeight;
	}

	/**
	 * @return mixed
	 */
	public function getProductID() {
		return $this->productID;
	}

	/**
	 * @param mixed $productID
	 */
	public function setProductID( $productID ) {
		$this->productID = $productID;
	}
}