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
$org         = caaguazu_opt( 'contact_org', 'Thiago Juan Manuel Ávalos Crosta' );
$disclaimer  = caaguazu_opt( 'contact_disclaimer', __( 'Sitio sin afiliación gubernamental', 'caaguazu' ) );
$city        = caaguazu_opt( 'contact_city', 'Ciudad de Caaguazú, Paraguay' );
$phone       = caaguazu_opt( 'contact_phone', '' );
$email       = caaguazu_opt( 'contact_email', 'thiagojuanma5@gmail.com' );

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
// Igual que en header.php: si Elementor Pro (Theme Builder) tiene un
// footer propio asignado y activo, se imprime solo y el theme no agrega
// el suyo. Sin Elementor Pro (el caso normal), esto no cambia nada.
$elementor_footer_done = function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' );
?>
</main>

<?php if ( $elementor_footer_done ) : ?>
	<?php // Footer servido por el Theme Builder de Elementor Pro. ?>
<?php else : ?>
<footer class="footer">
	<div class="weave-rule" aria-hidden="true"></div>
	<div class="container">
		<div class="foot-grid">
			<div class="foot-brand reveal" id="newsletter">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="name"><?php bloginfo( 'name' ); ?></a>
				<p class="tag"><?php echo esc_html( $tagline ); ?></p>
				<p><?php echo esc_html( $about ); ?></p>
				<?php echo caaguazu_newsletter_form_html(); ?>
			</div>

			<?php if ( $current_eco ) : ?>
				<div class="foot-col reveal">
					<h4><?php esc_html_e( 'Secciones', 'caaguazu' ); ?></h4>
					<ul>
						<?php foreach ( caaguazu_ecosystem_items( $current_eco ) as $s ) : ?>
							<li><a href="<?php echo esc_url( $s['url'] ); ?>"><?php echo esc_html( $s['label'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="foot-col reveal">
					<h4><?php caaguazu_i18n( 'footer.institucional', __( 'Institucional', 'caaguazu' ) ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ir a caaguazu.net', 'caaguazu' ); ?></a></li>
						<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Sobre Caaguazú', 'caaguazu' ); ?></a></li>
						<li><a href="<?php echo esc_url( $eco_url ); ?>"><?php esc_html_e( 'Ver ecosistema completo', 'caaguazu' ); ?></a></li>
						<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contacto', 'caaguazu' ); ?></a></li>
					</ul>
				</div>
			<?php else : ?>
				<?php /* Grupos cívicos (rediseño V2): la ciudad como portal — cada
				   columna es un área real del sitio, no una etiqueta inventada.
				   "Proyectos digitales" todavía no tiene una fuente de contenido
				   propia (CPT/página) más allá del hub de Ecosistema, así que por
				   ahora ese concepto vive dentro de "Ciudad" (enlace "Ver ecosistema
				   completo") en vez de fabricar una columna con enlaces falsos. */ ?>
				<div class="foot-col reveal">
					<h4><?php esc_html_e( 'Ciudad', 'caaguazu' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Sobre Caaguazú', 'caaguazu' ); ?></a></li>
						<li><a href="<?php echo esc_url( caaguazu_category_url( 'noticias' ) ); ?>"><?php esc_html_e( 'Noticias', 'caaguazu' ); ?></a></li>
						<li><a href="<?php echo esc_url( caaguazu_category_url( 'agenda' ) ); ?>"><?php esc_html_e( 'Agenda de la ciudad', 'caaguazu' ); ?></a></li>
						<li><a href="<?php echo esc_url( $eco_url ); ?>"><?php esc_html_e( 'Ver ecosistema completo', 'caaguazu' ); ?></a></li>
					</ul>
				</div>

				<div class="foot-col reveal">
					<h4><?php esc_html_e( 'Servicios', 'caaguazu' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( $search_url ); ?>"><?php esc_html_e( 'Buscar información', 'caaguazu' ); ?></a></li>
						<?php if ( post_type_exists( 'cgz_local' ) ) : ?>
							<li><a href="<?php echo esc_url( get_post_type_archive_link( 'cgz_local' ) ); ?>"><?php esc_html_e( 'Locales y reservas', 'caaguazu' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/cuenta/' ) ); ?>"><?php esc_html_e( 'Mi cuenta', 'caaguazu' ); ?></a></li>
						<?php endif; ?>
						<li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contacto', 'caaguazu' ); ?></a></li>
					</ul>
				</div>

				<div class="foot-col reveal">
					<h4><?php esc_html_e( 'Cultura y turismo', 'caaguazu' ); ?></h4>
					<ul>
						<?php foreach ( caaguazu_ecosystems() as $eco ) : ?>
							<li><a href="<?php echo esc_url( caaguazu_ecosystem_hub_url( $eco ) ); ?>"><?php echo esc_html( $eco['label'] ); ?></a></li>
						<?php endforeach; ?>
						<?php foreach ( $eco_subs as $s ) : ?>
							<li><a href="<?php echo esc_url( $s['url'] ); ?>" target="_blank" rel="noreferrer"><?php echo esc_html( $s['tag'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="foot-col reveal">
					<h4><?php esc_html_e( 'Educación y comunidad', 'caaguazu' ); ?></h4>
					<ul>
						<li><a href="<?php echo esc_url( caaguazu_category_url( 'educacion' ) ); ?>"><?php esc_html_e( 'Escuelas, becas y programas', 'caaguazu' ); ?></a></li>
						<li><a href="#newsletter"><?php esc_html_e( 'Recibí el boletín', 'caaguazu' ); ?></a></li>
					</ul>
				</div>
			<?php endif; ?>

			<div class="foot-col reveal">
				<h4><?php caaguazu_i18n( 'footer.contacto', __( 'Contacto', 'caaguazu' ) ); ?></h4>
				<address>
					<?php echo esc_html( $org ); ?><br>
					<?php if ( $disclaimer ) : ?><em><?php echo esc_html( $disclaimer ); ?></em><br><?php endif; ?>
					<?php echo esc_html( $city ); ?><br>
					<?php if ( $phone ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a><br><?php endif; ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				</address>
			</div>
		</div>

		<div class="foot-bottom">
			<div class="foot-bottom-copy">
				<p>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php echo esc_html( $org ); ?></p>
				<p class="pilot-badge"><?php esc_html_e( 'Piloto digital local · Caaguazú', 'caaguazu' ); ?></p>
			</div>
			<ul class="foot-legal">
				<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Accesibilidad', 'caaguazu' ); ?></a></li>
				<li><a href="<?php echo esc_url( $search_url ); ?>"><?php esc_html_e( 'Mapa del sitio', 'caaguazu' ); ?></a></li>
				<li><a href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'Política de privacidad', 'caaguazu' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/feed/' ) ); ?>">RSS</a></li>
			</ul>
		</div>
	</div>
</footer>
<?php endif; ?>

<?php
// Eco-rail: sidebar derecho colapsable (ver inc/sidebar.php). Elemento
// fixed — al final del documento para que el teclado recorra primero el
// contenido; se pinta también si Elementor sirve su propio footer (es una
// capa de navegación del sitio, no parte del footer).
caaguazu_render_eco_rail();
?>

<?php if ( $is_home ) : ?>
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebSite","name":"<?php echo esc_js( get_bloginfo( 'name' ) ); ?>","alternateName":"Ka'aguasu","url":"<?php echo esc_js( home_url( '/' ) ); ?>","about":{"@type":"AdministrativeArea","name":"Caaguazú, Paraguay"},"slogan":"<?php echo esc_js( $tagline ); ?>"}
</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
