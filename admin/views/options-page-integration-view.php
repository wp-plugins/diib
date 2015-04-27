<div class="wrap">
    <h2><?php _e( 'diib Settings', 'diib' ) ?></h2>

    <?php include __DIR__ . '/navbar-view.php' ?>

   <form id="diib-options-page-form" method="post" action="options.php">
        <?php settings_fields( 'diib-tracking' ) ?>
        <?php do_settings_sections( 'diib-tracking' ) ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Auto-Tracking', 'diib') ?></th>
                <td>
                    <p>
                        <label><input type="checkbox" id="diib_tracking_enabled" name="diib_tracking_enabled" value="1" <?php checked( $diib_tracking_enabled ) ?>> <?php _e( 'Enable automatic tracking of users activities', 'diib' ) ?></label>
                    </p>
                    <p class="description diib-description"><?php _e( 'You don\'t need to enable this if you are using a 3rd party analytics plugin or have inserted your tracking code on your own. <b>We encourage you to enable automatic tracking for Diib Tracker.</b>', 'diib' ) ?></p>
                </td>
            </tr>
        <?php if( $diib_tracking_enabled ): ?>
            <tr valign="top">
                <th scope="row"><?php _e( 'eCommerce', 'diib' ) ?></th>
                <td>
                <?php if( $woocommerce ): ?>
                    <p><label><input type="checkbox" id="diib_track_ecommerce" name="diib_track_ecommerce" value="1" <?php checked( $diib_track_ecommerce ) ?>> <?php _e( 'Enable WooCommerce integration', 'diib' ) ?></label></p>
                    <p class="description diib-description"><?php _e( 'You don\'t need to enable this if you are using a 3rd party WooCommerce analytics plugin. <b>We encourage you to enable WooCommerce integration in case you use Diib Tracker with this website.</b>', 'diib' ) ?></p>
                <?php else: ?>
                    <p class="description"><?php printf( __( 'Install <a href="%1$s">WooCommerce</a> to get automatic eCommerce activity tracking', 'diib' ), 'http://www.woothemes.com/woocommerce' ) ?></p>
                <?php endif ?>
                </td>
            </tr>
        <?php endif ?>
            <tr valign="top">
                <th scope="row"><?php _e( 'Tracker Type', 'diib' ) ?></th>
                <td>
                    <kbd><?php echo Diib_Utils::get_tracker_label() ?></kbd>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Cached Tracking Code', 'diib' ) ?></th>
                <td>
                    <div class="code diib-tracking-code"><pre><?php echo esc_html( $tracking_code ) ?></pre></div>
                </td>
            </tr>
        </table>

        <p class="submit">
            <?php submit_button( null, 'primary', 'submit', false ) ?>
            <a href="<?php echo admin_url( 'options-general.php?page=diib-settings-integration&flush_transient=1' ) ?>" class="button button-default"><?php _e( 'Invalidate Tracking Code Cache', 'diib' ) ?></a>
        </p>
    </form>

</div>