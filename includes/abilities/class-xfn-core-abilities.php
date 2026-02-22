<?php
/**
 * XFN relationship abilities for the Abilities API.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */

class XFN_Core_Abilities {

	private const EXCLUSIVITY_GROUPS = array(
		'friendship'   => array( 'contact', 'acquaintance', 'friend' ),
		'geographical' => array( 'co-resident', 'neighbor' ),
		'family'       => array( 'child', 'parent', 'sibling', 'spouse', 'kin' ),
	);

	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$this->register_set_relationships();
		$this->register_get_relationships();
		$this->register_add_relationship();
		$this->register_remove_relationship();
		$this->register_validate_relationships();
	}

	private function register_set_relationships(): void {
		wp_register_ability(
			'xfn/set_relationships',
			array(
				'label'               => __( 'Set XFN Relationships', 'link-extension-for-xfn' ),
				'description'         => __( 'Set all XFN relationships for a post, replacing any existing ones.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'post_id'       => array(
							'type'        => 'integer',
							'description' => 'The post ID.',
						),
						'relationships' => array(
							'type'        => 'array',
							'description' => 'Array of relationship objects with url and rels.',
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'url'  => array( 'type' => 'string', 'format' => 'uri' ),
									'rels' => array(
										'type'  => 'array',
										'items' => array( 'type' => 'string' ),
									),
								),
							),
						),
					),
					'required'   => array( 'post_id', 'relationships' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'applied' => array( 'type' => 'integer' ),
					),
				),
				'execute_callback'    => array( $this, 'execute_set_relationships' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => array(
					'version' => '1.0.0',
				),
			)
		);
	}

	private function register_get_relationships(): void {
		wp_register_ability(
			'xfn/get_relationships',
			array(
				'label'               => __( 'Get XFN Relationships', 'link-extension-for-xfn' ),
				'description'         => __( 'Retrieve all XFN relationships for a post.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'post_id' => array(
							'type'        => 'integer',
							'description' => 'The post ID.',
						),
					),
					'required'   => array( 'post_id' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'relationships' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'url'  => array( 'type' => 'string' ),
									'rels' => array(
										'type'  => 'array',
										'items' => array( 'type' => 'string' ),
									),
								),
							),
						),
					),
				),
				'execute_callback'    => array( $this, 'execute_get_relationships' ),
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => array(
					'version' => '1.0.0',
				),
			)
		);
	}

	private function register_add_relationship(): void {
		wp_register_ability(
			'xfn/add_relationship',
			array(
				'label'               => __( 'Add XFN Relationship', 'link-extension-for-xfn' ),
				'description'         => __( 'Add an XFN relationship to a post.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'post_id' => array(
							'type'        => 'integer',
							'description' => 'The post ID.',
						),
						'url'     => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => 'The URL to associate relationships with.',
						),
						'rels'    => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'description' => 'Array of XFN relationship values.',
						),
					),
					'required'   => array( 'post_id', 'url', 'rels' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
					),
				),
				'execute_callback'    => array( $this, 'execute_add_relationship' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => array(
					'version' => '1.0.0',
				),
			)
		);
	}

	private function register_remove_relationship(): void {
		wp_register_ability(
			'xfn/remove_relationship',
			array(
				'label'               => __( 'Remove XFN Relationship', 'link-extension-for-xfn' ),
				'description'         => __( 'Remove an XFN relationship from a post by URL.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'post_id' => array(
							'type'        => 'integer',
							'description' => 'The post ID.',
						),
						'url'     => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => 'The URL whose relationships should be removed.',
						),
					),
					'required'   => array( 'post_id', 'url' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
					),
				),
				'execute_callback'    => array( $this, 'execute_remove_relationship' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => array(
					'version' => '1.0.0',
				),
			)
		);
	}

	private function register_validate_relationships(): void {
		wp_register_ability(
			'xfn/validate_relationships',
			array(
				'label'               => __( 'Validate XFN Relationships', 'link-extension-for-xfn' ),
				'description'         => __( 'Check if a set of XFN relationships respects exclusivity rules.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'rels' => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'description' => 'Array of XFN relationship values to validate.',
						),
					),
					'required'   => array( 'rels' ),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'valid'    => array( 'type' => 'boolean' ),
						'warnings' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
				),
				'execute_callback'    => array( $this, 'execute_validate_relationships' ),
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => array(
					'version' => '1.0.0',
				),
			)
		);
	}

	public function execute_set_relationships( array $input ): array {
		$post_id       = (int) $input['post_id'];
		$relationships = (array) $input['relationships'];

		XFN_Meta_Mirror::set_relationships( $post_id, $relationships );

		$stored = XFN_Meta_Mirror::get_relationships( $post_id );

		return array(
			'success' => true,
			'applied' => count( $stored ),
		);
	}

	public function execute_get_relationships( array $input ): array {
		$post_id       = (int) $input['post_id'];
		$relationships = XFN_Meta_Mirror::get_relationships( $post_id );

		return array(
			'relationships' => $relationships,
		);
	}

	public function execute_add_relationship( array $input ): array {
		$post_id = (int) $input['post_id'];
		$url     = (string) $input['url'];
		$rels    = (array) $input['rels'];

		XFN_Meta_Mirror::add_relationship( $post_id, $url, $rels );

		return array(
			'success' => true,
		);
	}

	public function execute_remove_relationship( array $input ): array {
		$post_id = (int) $input['post_id'];
		$url     = (string) $input['url'];

		XFN_Meta_Mirror::remove_relationship( $post_id, $url );

		return array(
			'success' => true,
		);
	}

	public function execute_validate_relationships( array $input ): array {
		$rels     = (array) $input['rels'];
		$warnings = array();

		foreach ( self::EXCLUSIVITY_GROUPS as $group_name => $group_values ) {
			$matches = array_intersect( $rels, $group_values );
			if ( count( $matches ) > 1 ) {
				$warnings[] = sprintf(
					/* translators: 1: group name, 2: conflicting values */
					__( 'Exclusive group "%1$s" has multiple values: %2$s', 'link-extension-for-xfn' ),
					$group_name,
					implode( ', ', $matches )
				);
			}
		}

		return array(
			'valid'    => empty( $warnings ),
			'warnings' => $warnings,
		);
	}
}
