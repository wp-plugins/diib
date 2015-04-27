<div class="wp-filter">
    <ul class="filter-links">
        <li class="diib-filter-logo"><img src="<?php echo Diib_Utils::asset( '/admin/images/logo.svg' ) ?>"></li>
        <li class="diib-options-settings-oauth"><a href="<?php echo admin_url( 'options-general.php?page=diib-settings-oauth') ?>" class="<?php echo get_current_screen()->id != 'settings_page_diib-settings-oauth' ?: 'current' ?>"><?php _e( 'Authorization', 'diib' ) ?></a></li>
        <li class="diib-options-settings-website"><a href="<?php echo admin_url( 'options-general.php?page=diib-settings-website') ?>" class="<?php echo get_current_screen()->id != 'settings_page_diib-settings-website' ?: 'current' ?>"><?php _e( 'Website Settings', 'diib' ) ?></a></li>
        <li class="diib-options-settings-integration"><a href="<?php echo admin_url( 'options-general.php?page=diib-settings-integration') ?>" class="<?php echo get_current_screen()->id != 'settings_page_diib-settings-integration' ?: 'current' ?>"><?php _e( 'Tracking Code Integration', 'diib' ) ?></a></li>
    </ul>
</div>