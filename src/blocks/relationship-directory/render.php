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
$lexfn_limit       = (int) ( $attributes['limit'] ?? 50 );
$lexfn_show_search = (bool) ( $attributes['showSearch'] ?? true );
$lexfn_show_filter = (bool) ( $attributes['showFilters'] ?? true );

// Trim to limit.
if ( count( $lexfn_links ) > $lexfn_limit ) {
	$lexfn_links = array_slice( $lexfn_links, 0, $lexfn_limit );
}

// Collect unique relationship types for filter buttons.
$lexfn_all_rels = [];
foreach ( $lexfn_links as $lexfn_link ) {
	foreach ( $lexfn_link['rels'] as $lexfn_rel ) {
		$lexfn_all_rels[ $lexfn_rel ] = true;
	}
}
ksort( $lexfn_all_rels );
$lexfn_all_rels = array_keys( $lexfn_all_rels );

// Build link data for client-side filtering.
$lexfn_link_data = [];
foreach ( $lexfn_links as $lexfn_i => $lexfn_link ) {
	$lexfn_post_title  = get_the_title( $lexfn_link['post_id'] );
	$lexfn_link_data[] = [
		'id'        => $lexfn_i,
		'url'       => $lexfn_link['url'],
		'rels'      => $lexfn_link['rels'],
		'postId'    => $lexfn_link['post_id'],
		'postTitle' => $lexfn_post_title,
	];
}

$lexfn_context = wp_json_encode(
	[
		'searchTerm'   => '',
		'activeFilter' => '',
	]
);

wp_interactivity_state(
	'xfn-directory',
	[
		'links'   => $lexfn_link_data,
		'allRels' => $lexfn_all_rels,
	]
);

$lexfn_wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => 'xfn-directory',
	]
);
?>
<div
	<?php echo $lexfn_wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() output is escaped by core. ?>
	data-wp-interactive="xfn-directory"
	data-wp-context='<?php echo esc_attr( $lexfn_context ); ?>'
>
	<?php if ( $lexfn_show_search ) : ?>
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

	<?php if ( $lexfn_show_filter && ! empty( $lexfn_all_rels ) ) : ?>
		<div class="xfn-directory__filters xfn-pills">
			<button
				type="button"
				class="xfn-pill xfn-directory__filter-btn"
				data-wp-on--click="actions.clearFilter"
				data-wp-class--xfn-pill--active="!context.activeFilter"
			>
				<?php esc_html_e( 'All', 'link-extension-for-xfn' ); ?>
			</button>
			<?php foreach ( $lexfn_all_rels as $lexfn_rel ) : ?>
				<button
					type="button"
					class="xfn-pill xfn-pill-<?php echo esc_attr( $lexfn_rel ); ?> xfn-directory__filter-btn"
					data-rel="<?php echo esc_attr( $lexfn_rel ); ?>"
					data-wp-on--click="actions.toggleFilter"
					data-wp-class--xfn-pill--active="<?php echo esc_attr( 'state.isActiveFilter_' . $lexfn_rel ); ?>"
				>
					<?php echo esc_html( $lexfn_rel ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<ul class="xfn-directory__list" role="list">
		<?php foreach ( $lexfn_link_data as $lexfn_link ) : ?>
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
					<?php foreach ( $lexfn_link['rels'] as $lexfn_rel ) : ?>
						<span class="xfn-pill xfn-pill-<?php echo esc_attr( $lexfn_rel ); ?>">
							<?php echo esc_html( $lexfn_rel ); ?>
						</span>
					<?php endforeach; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( empty( $lexfn_link_data ) ) : ?>
		<p class="xfn-directory__empty">
			<?php esc_html_e( 'No XFN relationships found.', 'link-extension-for-xfn' ); ?>
		</p>
	<?php endif; ?>
</div>
