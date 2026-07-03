<?php
/**
 * Server-side render for the Relationship Badge block.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url      = $attributes['url'] ?? '';
$show_url = (bool) ( $attributes['showUrl'] ?? true );

$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => 'xfn-relationship-badge',
	]
);

if ( empty( $url ) ) :
	?>
	<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
		<p class="xfn-relationship-badge__empty">
			<?php esc_html_e( 'Enter a URL to display its XFN relationships.', 'link-extension-for-xfn' ); ?>
		</p>
	</div>
	<?php
	return;
endif;

// Find relationships for the given URL across all posts.
$lexfn_links = XFN_Content_Scanner::scan_all_posts_for_xfn();
$rels        = [];

foreach ( $lexfn_links as $lexfn_link ) {
	if ( $lexfn_link['url'] === $url ) {
		$rels = array_merge( $rels, $lexfn_link['rels'] );
	}
}
$rels = array_unique( $rels );
sort( $rels );

if ( empty( $rels ) ) :
	?>
	<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
		<p class="xfn-relationship-badge__empty">
			<?php
			printf(
				/* translators: %s: URL being looked up. */
				esc_html__( 'No XFN relationships found for %s.', 'link-extension-for-xfn' ),
				'<code>' . esc_html( $url ) . '</code>'
			);
			?>
		</p>
	</div>
	<?php
	return;
endif;
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
	<?php if ( $show_url ) : ?>
		<a href="<?php echo esc_url( $url ); ?>" class="xfn-relationship-badge__link" rel="<?php echo esc_attr( implode( ' ', $rels ) ); ?>">
			<?php echo esc_html( $url ); ?>
		</a>
	<?php endif; ?>
	<span class="xfn-pills">
		<?php foreach ( $rels as $rel ) : ?>
			<span class="xfn-pill xfn-pill-<?php echo esc_attr( $rel ); ?>">
				<?php echo esc_html( $rel ); ?>
			</span>
		<?php endforeach; ?>
	</span>
</div>
