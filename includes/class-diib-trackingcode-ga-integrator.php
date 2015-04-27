<?php

/**
 * This file is part of Diib WordPress Plugin.
 *
 *  (c) Diib Inc. <feedback@diib.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class Diib_TrackingCode_GA_Integrator
{
    public function tracker_specific_integrations() {
        add_action( 'woocommerce_after_shop_loop', array( $this, 'after_shop_loop' ) );
        add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'after_add_to_cart' ) );
        add_filter( 'woocommerce_get_return_url', array( $this, 'utm_nooverride_filter' ) );

        if( is_order_received_page() ) {
            global $wp;

            $order_id = isset( $wp->query_vars['order-received'] ) ? $wp->query_vars['order-received'] : 0;

            if( $order_id > 0 && get_post_meta( $order_id, '_diib_tracked', true ) != 1 ) {
                update_post_meta( $order_id, '_diib_tracked', 1 );

                add_filter( 'diib_ecommerce_tracking_code', function( $code ) use( $order_id ) {
                    return $code . "\n" . $this->track_order_js( new WC_Order( $order_id ) );
                } );
            }
        }
    }

    public function after_add_to_cart() {
        if( is_single() ) {
            global $product;

            $params = array(
                'category' => __( 'Products', 'diib' ),
                'action'   => __( 'Add to Cart', 'diib' ),
                'label'    => esc_js( $product->get_sku() ? sprintf( __( 'SKU: %1$s', 'diib' ), $product->get_sku() ) : sprintf( '#%1$s', $product->id ) ),
            );

            wc_enqueue_js( "$('.single_add_to_cart_button').click(function(){ " . $this->get_event_tracking_code( $params ) . " });" );
        }
    }

    public function after_shop_loop() {
        $params = array(
            'category' => __( 'Products', 'diib' ),
            'action'   => __( 'Add to Cart', 'diib' ),
            'label'    => "($(this).data('product_sku')) ? ('SKU: ' + $(this).data('product_sku')) : ('#' + $(this).data('product_id'))",
        );

        wc_enqueue_js( "$('.add_to_cart_button:not(.product_type_variable, .product_type_grouped)').click(function(){ " . $this->get_event_tracking_code( $params ) . " });" );
    }

    public function utm_nooverride_filter( $return_url ) {
        return add_query_arg( 'utm_nooverride', '1', remove_query_arg( 'utm_nooverride', $return_url ) );
    }

    public function get_event_tracking_code( $product ) {
        return strtr( "_gaq.push(['_trackEvent', ':category', ':action', ':label']);", array(
            ':category' => $params['category'],
            ':action'   => $params['action'],
            ':label'    => $params['label'],
        ) );
    }

    public function track_order_js( $order ) {
        $code = <<<JAVASCRIPT
  _gaq.push(['_set', 'currencyCode', ':currency']);
  _gaq.push(['_addTrans', ':order', ':store', ':total', ':tax', ':shipping', ':city', ':state', ':country']);
:items
  _gaq.push(['_trackTrans']);
JAVASCRIPT;

        $item_code = "  _gaq.push(['_addItem', ':order', ':sku', ':item', ':group', ':total', ':quantity']);";

        $output = strtr( $code, array_map( 'esc_js', array(
            ':currency' => $order->get_order_currency(),
            ':order'    => $order->get_order_numer(),
            ':store'    => get_bloginfo( 'name' ),
            ':total'    => $order->get_total(),
            ':tax'      => $order->get_total_tax(),
            ':shipping' => $order->get_total_shipping(),
            ':city'     => $order->billing_city,
            ':state'    => $order->billing_state,
            ':country'  => $order->billing_country,
        ) ) );

        $items = '';

        if( $order->get_items() ) {
            foreach( $order->get_items() as $item ) {
                $product = $order->get_product_from_item( $item );

                if( isset($product->variation_data) ) {
                    $item_group = woocommerce_get_formatted_variation( $product->variation_data, true );
                } else {
                    $cats = array();

                    $item_cats = get_the_terms( $product->id, 'product_cat' );
                    if( $item_cats ) {
                        foreach( $item_cats as $cat ) {
                            $cats[] = $cat->name;
                        }
                    }

                    $item_group = join('/', $cats);
                }

                $item_output = strtr($item_code, array_map( 'esc_js', array(
                    ':order'    => $order->get_order_numer(),
                    ':sku'      => $product->get_sku() ?: $product->id,
                    ':item'     => $item['name'],
                    ':group'    => $item_group,
                    ':total'    => $order->get_item_total( $item ),
                    ':quantity' => $item['qty'],
                ) ) );

                $items .= $item_output;
            }
        }

        return str_replace( ':items', $items, $output );
    }
}