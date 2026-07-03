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

$lexfn_url      = $attributes['url'] ?? '';
$lexfn_show_url = (bool) ( $attributes['showUrl'] ?? true );

$lexfn_wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => 'xfn-relationship-badge',
	]
);

if ( empty( $lexfn_url ) ) :
	?>
	<div <?php echo $lexfn_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
		<p class="xfn-relationship-badge__empty">
			<?php esc_html_e( 'Enter a URL to display its XFN relationships.', 'link-extension-for-xfn' ); ?>
		</p>
	</div>
	<?php
	return;
endif;

// Find relationships for the given URL across all posts.
$lexfn_links = XFN_Content_Scanner::scan_all_posts_for_xfn();
$lexfn_rels        = [];

foreach ( $lexfn_links as $lexfn_link ) {
	if ( $lexfn_link['url'] === $lexfn_url ) {
		$lexfn_rels = array_merge( $lexfn_rels, $lexfn_link['rels'] );
	}
}
$lexfn_rels = array_unique( $lexfn_rels );
sort( $lexfn_rels );

if ( empty( $lexfn_rels ) ) :
	?>
	<div <?php echo $lexfn_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
		<p class="xfn-relationship-badge__empty">
			<?php
			printf(
				/* translators: %s: URL being looked up. */
				esc_html__( 'No XFN relationships found for %s.', 'link-extension-for-xfn' ),
				'<code>' . esc_html( $lexfn_url ) . '</code>'
			);
			?>
		</p>
	</div>
	<?php
	return;
endif;
?>
<div <?php echo $lexfn_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
	<?php if ( $lexfn_show_url ) : ?>
		<a href="<?php echo esc_url( $lexfn_url ); ?>" class="xfn-relationship-badge__link" rel="<?php echo esc_attr( implode( ' ', $lexfn_rels ) ); ?>">
			<?php echo esc_html( $lexfn_url ); ?>
		</a>
	<?php endif; ?>
	<span class="xfn-pills">
		<?php foreach ( $lexfn_rels as $lexfn_rel ) : ?>
			<span class="xfn-pill xfn-pill-<?php echo esc_attr( $lexfn_rel ); ?>">
				<?php echo esc_html( $lexfn_rel ); ?>
			</span>
		<?php endforeach; ?>
	</span>
</div>
