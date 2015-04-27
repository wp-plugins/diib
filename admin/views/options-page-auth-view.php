<?php add_thickbox() ?>
<?php $current_user = wp_get_current_user() ?>
<div id="diib-signup-content" style="display:none">
    <div class="diib-signup-content">
        <p align="center"><img src="<?php echo Diib_Utils::asset( '/admin/images/logo.svg' ) ?>" width="200px"></p>
        <h2><?php _e( 'See Your Website Grow in Just 7 Days!', 'diib' ) ?></h2>
        <h3><?php _e( 'No Credit Card Required!', 'diib' ) ?></h3>
        <h4><?php _e( 'Only $9.97/month after the trial period.', 'diib' ) ?></h4>
        <h4><?php _e( 'Stop feeling like you\'re in the dark. Get clarity today!', 'diib' ) ?></h4>
        <hr>
        <div class="form" id="diib-signup-form">
            <div class="errors" id="diib-signup-errors" style="display:none">
                <p></p>
                <hr>
            </div>
            <div class="loading" id="diib-signup-loading" style="display:none">
                <p align="center"><img src="/wp-admin/images/wpspin_light-2x.gif"></p>
                <hr>
            </div>
            <p><label>
                <b><?php _e( 'Full Name', 'diib' ) ?></b><br>
                <input type="text" id="diib_signup_name" class="regular-text" value="<?php echo esc_attr( $current_user->display_name ) ?>">
            </label></p>
            <p><label>
                <b><?php _e( 'Email Address', 'diib' ) ?></b><br>
                <input type="email" id="diib_signup_email" class="regular-text" value="<?php echo esc_attr( $current_user->user_email ) ?>">
            </label></p>
            <p><label>
                <b><?php _e( 'Password', 'diib' ) ?></b><br>
                <input type="password" id="diib_signup_password" class="regular-text">
            </label></p>
            <p><label>
                <b><?php _e( 'Repeat Password', 'diib' ) ?></b><br>
                <input type="password" id="diib_signup_repeat_password" class="regular-text">
            </label></p>
            <p><label>
                    <input type="checkbox" id="diib_signup_accept_terms" data-notice="<?php echo esc_attr(__( 'You have to accept Terms of Service.', 'diib' )) ?>">
                    <?php printf( __( 'I accept the <a href="%1$s" target="_blank">Terms of Service</a>', 'diib' ), 'https://www.diib.com/terms' ) ?>
            </label></p>
            <p><button type="button" class="button button-primary" id="diib_signup_submit" data-endpoint="<?php echo DIIB_ENDPOINT ?>/api/account/signup"><?php _e( 'Sign Up Now!', 'diib' ) ?></button></p>
        </div>
        <div class="thank-you" id="diib-signup-thank-you" style="display:none">
            <p class="info"><?php _e( 'Thank you <b>%1$s</b> for giving diib a shot. We have a lot to offer to you. Instructions were sent to your email address (<b>%2$s</b>) and we encourage you to open your diib Dashboard (click below) and setup your website. Then come back to WordPress and finish setting up diib integration!', 'diib' ) ?></p>
            <p><a href="<?php echo DIIB_ENDPOINT ?>/login" target="_blank" class="button button-primary"><?php _e( 'Open Dashboard', 'diib' ) ?></a></p>
        </div>
    </div>
</div>
<div class="wrap">
    <h2><?php _e( 'diib Settings', 'diib' ) ?></h2>

    <?php include __DIR__ . '/navbar-view.php' ?>

    <h3><?php _e( 'Authorize with diib', 'diib' ) ?></h3>
    <p class="description"><?php _e( 'We use client-side OAuth2 authorization. Your email address and password is not sent via your server.', 'diib' ) ?></p>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php _e( 'Email Address', 'diib' ) ?></th>
            <td>
                <input type="text" id="diib_username" class="regular-text" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php _e( 'Password', 'diib' ) ?></th>
            <td>
                <input type="password" id="diib_password" class="regular-text" />
                <p>
                    <input type="button" id="diib_authorize_user" class="button diib-button" value="<?php echo esc_attr( __( 'Authenticate Now', 'diib' ) ) ?>" data-loading="<?php echo esc_attr( __( 'Authorizing', 'diib' ) ) ?>" data-client-id="<?php echo DIIB_OAUTH2_CLIENT_ID ?>" data-endpoint="<?php echo DIIB_OAUTH2_ENDPOINT ?>" />
                    <a href="#TB_inline?width=500&height=500&inlineId=diib-signup-content" class="button button-primary thickbox">Sign Up for Trial</a>
                </p>
            </td>
        </tr>
    </table>

    <hr>

    <form id="diib-options-page-form" method="post" action="options.php">
        <?php settings_fields( 'diib-oauth' ) ?>
        <?php do_settings_sections( 'diib-oauth' ) ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Access Token', 'diib' ) ?></th>
                <td>
                    <input type="text" readonly id="diib_access_token" name="diib_access_token" value="<?php echo esc_attr( get_option( 'diib_access_token' ) ) ?>" class="regular-text" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Expires On', 'diib' ) ?></th>
                <td>
                    <?php $expiration = get_option('diib_access_token_expiration'); ?>
                    <input type="text" readonly id="diib_access_token_expiration_human" value="<?php echo $expiration ? esc_attr( date( 'Y-m-d H:i:s', $expiration ) . ' UTC' ) : '' ?>" class="regular-text" />
                    <input type="hidden" id="diib_access_token_expiration" name="diib_access_token_expiration" value="<?php echo esc_attr(get_option('diib_access_token_expiration')) ?>" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e( 'Refresh Token', 'diib' ) ?></th>
                <td><input type="text" readonly id="diib_refresh_token" name="diib_refresh_token" value="<?php echo esc_attr( get_option( 'diib_refresh_token' ) ) ?>" class="regular-text" /></td>
            </tr>
        </table>
        <?php submit_button() ?>
    </form>
</div>