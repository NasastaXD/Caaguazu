<?php
/**
 * Splash animado de marca, mostrado una sola vez por sesión.
 * El <head> agrega .promotur-no-splash al <html> si ya se vio; el JS lo retira tras animar.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="promotur-splash" data-splash aria-hidden="true">
	<div class="promotur-splash__mark"><?php echo promotur_icon( 'marca' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<div class="promotur-splash__name"><?php bloginfo( 'name' ); ?></div>
</div>
