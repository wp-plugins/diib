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

class Diib_TrackingCode_Piwik_Integrator
{
    const VAR_NAME = '_paq';

    public function tracker_specific_integrations() {
        add_action( 'woocommerce_after_single_product_summary', array( $this, 'product_view' ) );
        add_action( 'woocommerce_after_shop_loop', array( $this, 'category_view' ) );
        add_action( 'woocommerce_after_cart', array( $this, 'update_cart' ) );

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

    public function update_cart() {
        add_filter( 'diib_ecommerce_tracking_code', function( $code ) use( $update_cart_code ) {
            global $woocommerce;

            $update_cart_code = <<<JAVASCRIPT
  var cartItems = [], cartRevenue = 0;
:items
  for (var i=0; i<cartItems.length; i++) {
    :var:.push(['addEcommerceItem', cartItems[i].sku, cartItems[i].title, cartItems[i].categories, cartItems[i].price, cartItems[i].quantity]);
    cartRevenue += cartItems[i].price * cartItems[i].quantity;
  }
  :var:.push(['trackEcommerceCartUpdate', cartRevenue]);
JAVASCRIPT;

            $item_code = "cartItems.push({ sku: ':sku', title: ':item', price: :total, quantity: :quantity, categories: :group });";

            $cart_content = $woocommerce->cart->get_cart();

            $items = '';
            foreach( $cart_content as $item ) {
                $categories = get_the_terms( $item['product_id'], 'product_cat' );
                $categories = array_map( function( $element ) {
                    return sprintf( "'%s'", esc_js( $element->name ) );
                }, $categories );
                $categories = sprintf( '[%s]', implode( "', '", $categories ) );

                $items .= strtr( $item_code, array(
                    ':sku'      => esc_js( ( $sku = $item['data']->get_sku() ) ? $sku : $item['product_id'] ),
                    ':price'    => $item['data']->get_price(),
                    ':item'     => $item['data']->get_title(),
                    ':quantity' => $item['quantity'],
                    ':group'    => $categories,
                ) );
            }

            return $code . "\n" . $this->apply_variable_name( str_replace( ':items', $items, $update_cart_code ) );
        });
    }

    public function category_view() {
        add_filter( 'diib_ecommerce_tracking_code', function( $code ) {
            global $wp_query;

            if( isset( $wp_query->query_vars['product_cat'] ) && !empty( $wp_query->query_vars['product_cat'] ) ) {
                return $code . "\n" . $this->apply_variable_name( sprintf(
                    "  :var:.push(['setEcommerceView', false, false, '%s']);",
                    esc_js( $wp_query->queried_object->name )
                ) );
            }

            return $code;
        });
    }

    public function product_view() {
        add_filter( 'diib_ecommerce_tracking_code', function( $code ) {
            global $product;

            $categories = get_the_terms( $product->post->ID, 'product_cat' );
            $categories = array_map( function( $element ) {
                return sprintf( "'%s'", esc_js( $element->name ) );
            }, $categories );
            $categories = sprintf( '[%s]', implode( "', '", $categories ) );

            return $code . "\n" . $this->apply_variable_name( sprintf(
                "  :var:.push(['setEcommerceView', '%s', '%s', %s, %.2f]);",
                $product->get_sku(),
                esc_js( $product->get_title() ),
                $categories,
                $product->get_price()
            ) );
        });
    }

    public function track_order_js( $order ) {
        $code = ":items\n:var:.push(['trackEcommerceOrder', ':order', ':total', ':subtotal', ':tax', ':shipping']);";
        $item_code = "  :var:.push(['addEcommerceItem', ':sku', ':item', [':group'], ':total', ':quantity']);";

        $output = strtr( $code, array_map( 'esc_js', array(
            ':order'    => $order->get_order_numer(),
            ':total'    => $order->get_total(),
            ':subtotal' => $order->get_total() - $order->get_total_shipping(),
            ':tax'      => $order->get_total_tax(),
            ':shipping' => $order->get_total_shipping(),
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

                $item_output = strtr( $item_code, array_map( 'esc_js', array(
                    ':sku'      => $product->get_sku() ?: $product->id,
                    ':item'     => $item['name'],
                    ':group'    => $item_group,
                    ':total'    => $order->get_item_total( $item ),
                    ':quantity' => $item['qty'],
                ) ) );

                $items .= $item_output;
            }
        }

        return $this->apply_variable_name( str_replace( ':items', $items, $output ) );
    }

    protected function apply_variable_name( $code ) {
        return str_replace( ':var:', static::VAR_NAME, $code );
    }
}