<?php
/**
 * Footer del theme.
 *
 * @package Caaguazu
 */

$is_home     = caaguazu_is_home();
$current_eco = caaguazu_current_ecosystem();
$tagline  = caaguazu_opt( 'site_tagline_custom', 'Capital de la Madera' );
$about    = caaguazu_opt( 'footer_about', 'Portal oficial del departamento de Caaguazú, Paraguay.' );
$org      = caaguazu_opt( 'contact_org', 'Gobernación de Caaguazú' );
$city     = caaguazu_opt( 'contact_city', 'Coronel Oviedo, Paraguay' );
$phone    = caaguazu_opt( 'contact_phone', '+595 (0) 000 000 000' );
$email    = caaguazu_opt( 'contact_email', 'contacto@caaguazu.net' );

// Para los enlaces del footer — defaults a slugs estándar.
$eco_url    = caaguazu_page_url( 'ecosistema' );
$about_url  = caaguazu_page_url( 'sobre-caaguazu' );
$contact_url= caaguazu_page_url( 'contacto' );
$search_url = home_url( '/?s=' );

// Tomar dominios de sub-portales del ecosistema (los dos primeros, ignorando "próximamente").
$eco_subs = array();
for ( $i = 0; $i < 3; $i++ ) {
	$url = caaguazu_opt( "eco_{$i}_url", '' );
	$tag = caaguazu_opt( "eco_{$i}_tag", '' );
	if ( $url ) {
		$eco_subs[] = array( 'url' => $url, 'tag' => $tag );
	}
}
?>
</main>

<footer class="footer">
	<div class="weave-rule" aria-hidden="true"></div>
	<div class="container">
		<div class="foot-grid">
			<div class="foot-brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="name"><?php bloginfo( 'name' ); ?></a>
				<p class="tag"><?php echo esc_html( $tagline ); ?></p>
				<p><?php echo esc_html( $about ); ?></p>
				<?php echo caaguazu_newsletter_form_html(); ?>
			</div>

			<div class="foot-col">
				<?php if ( $in_tourism ) : ?>
					<h4><?php esc_html_e( 'Secciones', 'caaguazu' ); ?></h4>
					<ul>
						<?php foreach ( apply_filters( 'caaguazu_tourism_shell_items', array() ) as $s ) : ?>
							<li><a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<h4><?php caaguazu_i18n( 'footer.subportales', __( 'Sub-portales', 'caaguazu' ) ); ?></h4>
					<ul>
						<?php foreach ( $eco_subs as $s ) : ?>
							<li><a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noreferrer"><?php echo esc_html( $s['tag'] ); ?></a></li>
						<?php endforeach; ?>
						<li><a href="<?php echo esc_url( $eco_url ); ?>"><?php esc_html_e( 'Ver ecosistema completo', 'caaguazu' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="foot-col">
				<h4><?php caaguazu_i18n( 'footer.institucional', __( 'Institucional', 'caaguazu' ) ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Sobre Caaguazú', 'caaguazu' ); ?></a></li>
					<?php foreach ( caaguazu_ecosystems() as $eco ) : ?>
						<li><a href="<?php echo esc_url( caaguazu_ecosystem_hub_url( $eco ) ); ?>"><?php echo esc_html( $eco['label'] ); ?></a></li>
					<?php endforeach; ?>
					<?php if ( post_type_exists( 'cgz_local' ) ) : ?>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'cgz_local' ) ); ?>"><?php esc_html_e( 'Locales y reservas', 'caaguazu' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/cuenta/' ) ); ?>"><?php esc_html_e( 'Mi cuenta', 'caaguazu' ); ?></a></li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contacto', 'caaguazu' ); ?></a></li>
				</ul>
			</div>

			<div class="foot-col">
				<h4><?php caaguazu_i18n( 'footer.contacto', __( 'Contacto', 'caaguazu' ) ); ?></h4>
				<address>
					<?php echo esc_html( $org ); ?><br>
					<?php echo esc_html( $city ); ?><br>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><br>
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</address>
			</div>
		</div>

		<div class="foot-bottom">
			<p>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( $org ); ?> · <?php esc_html_e( 'Gobierno de la República del Paraguay', 'caaguazu' ); ?></p>
			<ul class="foot-legal">
				<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Accesibilidad', 'caaguazu' ); ?></a></li>
				<li><a href="<?php echo esc_url( $search_url ); ?>"><?php esc_html_e( 'Mapa del sitio', 'caaguazu' ); ?></a></li>
				<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Política de privacidad', 'caaguazu' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/feed/' ) ); ?>">RSS</a></li>
			</ul>
		</div>
	</div>
</footer>

<?php if ( $is_home ) : ?>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"GovernmentOrganization","name":"<?php echo esc_js( get_bloginfo( 'name' ) ); ?>","alternateName":"Ka'aguasu","url":"<?php echo esc_js( home_url( '/' ) ); ?>","areaServed":{"@type":"AdministrativeArea","name":"Caaguazú, Paraguay"},"slogan":"<?php echo esc_js( $tagline ); ?>"}
</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
