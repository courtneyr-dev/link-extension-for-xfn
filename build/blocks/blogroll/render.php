<?php
/**
 * Server-side render for the Blogroll block.
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

$lexfn_links              = XFN_Content_Scanner::scan_all_posts_for_xfn();
$lexfn_group_by           = $attributes['groupBy'] ?? 'relationship';
$lexfn_limit              = (int) ( $attributes['limit'] ?? 50 );
$lexfn_show_relationships = (bool) ( $attributes['showRelationships'] ?? true );

if ( count( $lexfn_links ) > $lexfn_limit ) {
	$lexfn_links = array_slice( $lexfn_links, 0, $lexfn_limit );
}

// Group links.
$lexfn_groups = [];
foreach ( $lexfn_links as $lexfn_link ) {
	if ( 'domain' === $lexfn_group_by ) {
		$lexfn_host             = wp_parse_url( $lexfn_link['url'], PHP_URL_HOST );
		$lexfn_key              = $lexfn_host ? $lexfn_host : __( 'Other', 'link-extension-for-xfn' );
		$lexfn_groups[ $lexfn_key ][] = $lexfn_link;
	} else {
		foreach ( $lexfn_link['rels'] as $lexfn_rel ) {
			$lexfn_groups[ $lexfn_rel ][] = $lexfn_link;
		}
	}
}
ksort( $lexfn_groups );

$lexfn_wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => 'xfn-blogroll',
	]
);

if ( empty( $lexfn_groups ) ) :
	?>
	<div <?php echo $lexfn_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
		<p class="xfn-blogroll__empty">
			<?php esc_html_e( 'No XFN relationships found.', 'link-extension-for-xfn' ); ?>
		</p>
	</div>
	<?php
	return;
endif;
?>
<div <?php echo $lexfn_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>>
	<dl class="xfn-blogroll__list">
		<?php foreach ( $lexfn_groups as $lexfn_group_name => $lexfn_group_links ) : ?>
			<dt class="xfn-blogroll__group-title">
				<?php echo esc_html( $lexfn_group_name ); ?>
				<span class="xfn-blogroll__count">(<?php echo count( $lexfn_group_links ); ?>)</span>
			</dt>
			<?php foreach ( $lexfn_group_links as $lexfn_link ) : ?>
				<dd class="xfn-blogroll__entry">
					<a
						href="<?php echo esc_url( $lexfn_link['url'] ); ?>"
						class="xfn-blogroll__link"
						rel="<?php echo esc_attr( implode( ' ', $lexfn_link['rels'] ) ); ?>"
					>
						<?php echo esc_html( $lexfn_link['url'] ); ?>
					</a>
					<?php if ( $lexfn_show_relationships && 'domain' === $lexfn_group_by ) : ?>
						<span class="xfn-pills">
							<?php foreach ( $lexfn_link['rels'] as $lexfn_rel ) : ?>
								<span class="xfn-pill xfn-pill-<?php echo esc_attr( $lexfn_rel ); ?>">
									<?php echo esc_html( $lexfn_rel ); ?>
								</span>
							<?php endforeach; ?>
						</span>
					<?php endif; ?>
				</dd>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</dl>
</div>
