<div class="wrap">
    <h2><?php _e( 'diib Settings', 'diib' ) ?></h2>

    <?php include __DIR__ . '/navbar-view.php' ?>

   <form id="diib-options-page-form" method="post" action="options.php">
        <?php settings_fields( 'diib-website' ) ?>
        <?php do_settings_sections( 'diib-website' ) ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e( 'Website', 'diib' ) ?></th>
                <td>
                    <p><?php _e( 'Please select the corresponding website from your diib profile', 'diib' ) ?></p>
                <?php foreach( $websites as $website ): ?>
                    <p><label><input type="radio" id="diib_website_id" name="diib_website_id" value="<?php echo (int)$website['id'] ?>" <?php checked( $diib_website_id, $website['id'] ) ?>> <?php echo $website['pretty_url'] ?> <small>(<?php echo $website['url'] ?>)</small> <kbd><?php echo Diib_Utils::get_tracker_label( $website['tracker']['type'] ) ?></kbd></label></p>
                <?php endforeach ?>
                </td>
            </tr>
        </table>
        <?php submit_button() ?>
    </form>

</div>