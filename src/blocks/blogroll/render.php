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

$links              = XFN_Content_Scanner::scan_all_posts_for_xfn();
$group_by           = $attributes['groupBy'] ?? 'relationship';
$limit              = (int) ( $attributes['limit'] ?? 50 );
$show_relationships = (bool) ( $attributes['showRelationships'] ?? true );

if ( count( $links ) > $limit ) {
	$links = array_slice( $links, 0, $limit );
}

// Group links.
$groups = array();
foreach ( $links as $link ) {
	if ( 'domain' === $group_by ) {
		$host = wp_parse_url( $link['url'], PHP_URL_HOST );
		$key  = $host ? $host : __( 'Other', 'link-extension-for-xfn' );
		$groups[ $key ][] = $link;
	} else {
		foreach ( $link['rels'] as $rel ) {
			$groups[ $rel ][] = $link;
		}
	}
}
ksort( $groups );

$wrapper_attrs = get_block_wrapper_attributes( array(
	'class' => 'xfn-blogroll',
) );

if ( empty( $groups ) ) :
	?>
	<div <?php echo $wrapper_attrs; ?>>
		<p class="xfn-blogroll__empty">
			<?php esc_html_e( 'No XFN relationships found.', 'link-extension-for-xfn' ); ?>
		</p>
	</div>
	<?php
	return;
endif;
?>
<div <?php echo $wrapper_attrs; ?>>
	<dl class="xfn-blogroll__list">
		<?php foreach ( $groups as $group_name => $group_links ) : ?>
			<dt class="xfn-blogroll__group-title">
				<?php echo esc_html( $group_name ); ?>
				<span class="xfn-blogroll__count">(<?php echo count( $group_links ); ?>)</span>
			</dt>
			<?php foreach ( $group_links as $link ) : ?>
				<dd class="xfn-blogroll__entry">
					<a
						href="<?php echo esc_url( $link['url'] ); ?>"
						class="xfn-blogroll__link"
						rel="<?php echo esc_attr( implode( ' ', $link['rels'] ) ); ?>"
					>
						<?php echo esc_html( $link['url'] ); ?>
					</a>
					<?php if ( $show_relationships && 'domain' === $group_by ) : ?>
						<span class="xfn-pills">
							<?php foreach ( $link['rels'] as $rel ) : ?>
								<span class="xfn-pill xfn-pill-<?php echo esc_attr( $rel ); ?>">
									<?php echo esc_html( $rel ); ?>
								</span>
							<?php endforeach; ?>
						</span>
					<?php endif; ?>
				</dd>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</dl>
</div>
