<?php
/**
 * Plugin Name: Affiliates Custom Method - Products amount
 * Description: Custom method: general rate by products amount.
 * Version: 1.1
 * Author: eggemplo
 * Author URI: http://www.itthinx.com
 */
class ACM {

	public static function init() {
		if ( class_exists( 'Affiliates_Referral' ) ) {
			Affiliates_Referral::register_referral_amount_method( array( __CLASS__, 'products_amount' ) );
		}
	}
	/**
	 * Custom referral amount method implementation.
	 * @param int $affiliate_id
	 * @param array $parameters
	 */
	public static function products_amount( $affiliate_id = null, $parameters = null ) {
		$result = '0';
		if ( isset( $parameters['post_id'] ) ) {
			$result = self::calculate( intval( $parameters['post_id'] ), intval($parameters['base_amount'] ));
		}
		return $result;
	}
	public static function calculate( $order_id, $base_amount ) {
		$return = '0';
		if ( class_exists( 'WC_Order' ) ) {
			$order = new WC_Order();
		} else {
			$order = new woocommerce_order();
		}
		if ( $order->get_order( $order_id ) ) {
			$items = $order->get_items();
			$options = get_option( 'affiliates_woocommerce' , array() );
			$default_rate = $options['default_rate'];
			foreach( $items as $item ) {
				$product = $order->get_product_from_item( $item );
				if ( $product->exists() ) {
					$product_id = $product->id;
					$product_rate = get_post_meta( $product_id, '_affiliates_rate', true );
					if ( strlen( (string) $product_rate ) == 0 ) {
						$return = bcadd( $return, bcmul( $default_rate, $order->get_item_total($item), 2 ), AFFILIATES_REFERRAL_AMOUNT_DECIMALS );
					}
					if ( strlen( (string) $product_rate ) > 0 ) {
						$return = bcadd( $return, $product_rate, AFFILIATES_REFERRAL_AMOUNT_DECIMALS );
					}
				}
			}
		}
		return $return;
	}
}
add_action( 'init', array( 'ACM', 'init' ) );
?>
