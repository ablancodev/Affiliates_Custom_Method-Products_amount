<?php
/**
 * Plugin Name: Affiliates Custom Method - Products amount
 * Description: Custom method: general rate by products amount.
 * Version: 1.1.1
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
		
		if ( function_exists( 'wc_get_order' ) ) {
		    $order = wc_get_order( $order_id );
		} else if ( class_exists( 'WC_Order' ) ) {
		    $order = new WC_Order($order_id);
		} else {
		    $order = new woocommerce_order();		   
		}		
		
		if ( is_object( $order ) ) {
			$items = $order->get_items();
			$options = get_option( 'affiliates_woocommerce' , array() );
			$default_rate = $options['default_rate'];
			if ( sizeof( $items ) > 0 ) {
    			foreach( $items as $item ) {    				
    			    $product = $item->get_product();
    				if ( $product->exists() ) {
    				    if ( method_exists( $product, 'get_id' ) ) {
    				        $product_id = $product->get_id();
    				    } else {
    				        $product_id = $product->id;
    				    }
    				    
    				    $product_rate = get_post_meta( $product_id, '_affiliates_rate', true );
    					if ( strlen( (string) $product_rate ) == 0 ) {
    						$return = bcadd( $return, bcmul( $default_rate, $order->get_line_total( $item ), 2 ), AFFILIATES_REFERRAL_AMOUNT_DECIMALS );
    					}
    					if ( strlen( (string) $product_rate ) > 0 ) {
    						$return = bcadd( $return, bcmul( $product_rate, $item->get_quantity(), AFFILIATES_REFERRAL_AMOUNT_DECIMALS ), AFFILIATES_REFERRAL_AMOUNT_DECIMALS );
    					}
    				}
    			}
		    } 
		}
		return $return;
	}
}
add_action( 'init', array( 'ACM', 'init' ) );
?>
