<?php
/**
 * Server-side render for the Relationship Directory block.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (empty for dynamic blocks).
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lexfn_links = XFN_Content_Scanner::scan_all_posts_for_xfn();
$limit       = (int) ( $attributes['limit'] ?? 50 );
$show_search = (bool) ( $attributes['showSearch'] ?? true );
$show_filter = (bool) ( $attributes['showFilters'] ?? true );

// Trim to limit.
if ( count( $lexfn_links ) > $limit ) {
	$lexfn_links = array_slice( $lexfn_links, 0, $limit );
}

// Collect unique relationship types for filter buttons.
$all_rels = [];
foreach ( $lexfn_links as $lexfn_link ) {
	foreach ( $lexfn_link['rels'] as $rel ) {
		$all_rels[ $rel ] = true;
	}
}
ksort( $all_rels );
$all_rels = array_keys( $all_rels );

// Build link data for client-side filtering.
$link_data = [];
foreach ( $lexfn_links as $i => $lexfn_link ) {
	$post_title  = get_the_title( $lexfn_link['post_id'] );
	$link_data[] = [
		'id'        => $i,
		'url'       => $lexfn_link['url'],
		'rels'      => $lexfn_link['rels'],
		'postId'    => $lexfn_link['post_id'],
		'postTitle' => $post_title,
	];
}

$context = wp_json_encode(
	[
		'searchTerm'   => '',
		'activeFilter' => '',
	]
);

wp_interactivity_state(
	'xfn-directory',
	[
		'links'   => $link_data,
		'allRels' => $all_rels,
	]
);

$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => 'xfn-directory',
	]
);
?>
<div
	<?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>
	data-wp-interactive="xfn-directory"
	data-wp-context='<?php echo esc_attr( $context ); ?>'
>
	<?php if ( $show_search ) : ?>
		<div class="xfn-directory__search">
			<input
				type="search"
				class="xfn-directory__search-input"
				placeholder="<?php esc_attr_e( 'Search relationships…', 'link-extension-for-xfn' ); ?>"
				data-wp-on--input="actions.setSearchTerm"
				data-wp-bind--value="context.searchTerm"
			/>
		</div>
	<?php endif; ?>

	<?php if ( $show_filter && ! empty( $all_rels ) ) : ?>
		<div class="xfn-directory__filters xfn-pills">
			<button
				type="button"
				class="xfn-pill xfn-directory__filter-btn"
				data-wp-on--click="actions.clearFilter"
				data-wp-class--xfn-pill--active="!context.activeFilter"
			>
				<?php esc_html_e( 'All', 'link-extension-for-xfn' ); ?>
			</button>
			<?php foreach ( $all_rels as $rel ) : ?>
				<button
					type="button"
					class="xfn-pill xfn-pill-<?php echo esc_attr( $rel ); ?> xfn-directory__filter-btn"
					data-rel="<?php echo esc_attr( $rel ); ?>"
					data-wp-on--click="actions.toggleFilter"
					data-wp-class--xfn-pill--active="<?php echo esc_attr( 'state.isActiveFilter_' . $rel ); ?>"
				>
					<?php echo esc_html( $rel ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<ul class="xfn-directory__list" role="list">
		<?php foreach ( $link_data as $lexfn_link ) : ?>
			<li
				class="xfn-directory__item"
				data-link-id="<?php echo esc_attr( $lexfn_link['id'] ); ?>"
				data-wp-bind--hidden="<?php echo esc_attr( 'state.isHidden_' . $lexfn_link['id'] ); ?>"
			>
				<a
					href="<?php echo esc_url( $lexfn_link['url'] ); ?>"
					class="xfn-directory__link"
					rel="<?php echo esc_attr( implode( ' ', $lexfn_link['rels'] ) ); ?>"
				>
					<?php echo esc_html( $lexfn_link['url'] ); ?>
				</a>
				<span class="xfn-directory__meta">
					<?php
					printf(
						/* translators: %s: post title */
						esc_html__( 'from %s', 'link-extension-for-xfn' ),
						esc_html( $lexfn_link['postTitle'] )
					);
					?>
				</span>
				<span class="xfn-pills">
					<?php foreach ( $lexfn_link['rels'] as $rel ) : ?>
						<span class="xfn-pill xfn-pill-<?php echo esc_attr( $rel ); ?>">
							<?php echo esc_html( $rel ); ?>
						</span>
					<?php endforeach; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( empty( $link_data ) ) : ?>
		<p class="xfn-directory__empty">
			<?php esc_html_e( 'No XFN relationships found.', 'link-extension-for-xfn' ); ?>
		</p>
	<?php endif; ?>
</div>
