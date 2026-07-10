<?php
/**
 * Este archivo sembraba 4 perfiles de artesanos inventados (nombres, citas
 * y oficios de relleno, no personas reales entrevistadas) como posts del
 * directorio de artesanos — que además nunca se lanzó (no está en el nav
 * ni en accesos rápidos, ver README "no se van a lanzar todavía"). Se
 * saca la siembra a propósito: nadie debería poder llegar, ni por URL
 * directa del archive, a perfiles que aparentan ser vecinos reales de
 * Caaguazú sin serlo. Si el directorio de artesanos se lanza en el
 * futuro, debe cargarse con artesanos reales, entrevistados y con su
 * consentimiento — no con este seeder.
 *
 * @package Caaguazu
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
