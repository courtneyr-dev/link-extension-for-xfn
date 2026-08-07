<?php
/**
 * XFN relationship abilities for the Abilities API.
 *
 * @package LinkExtensionForXFN
 * @since   1.0.0
 */

/**
 * Registers abilities that manage XFN relationships through post meta.
 */
class XFN_Core_Abilities {

	/**
	 * Register all meta-based XFN abilities.
	 *
	 * Names carry a `-meta` qualifier because XFN_Content_Abilities registers
	 * the same verbs against post_content. The two are separate storage
	 * backends, not duplicates, and a shared name would mean core rejects
	 * whichever one registers second.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		$this->register_set_relationships();
		$this->register_get_relationships();
		$this->register_add_relationship();
		$this->register_remove_relationship();
	}

	/**
	 * Register the xfn/set-meta-relationships ability.
	 */
	private function register_set_relationships(): void {
		wp_register_ability(
			'xfn/set-meta-relationships',
			[
				'label'               => __( 'Set XFN Relationships', 'link-extension-for-xfn' ),
				'description'         => __( 'Set all XFN relationships for a post, replacing any existing ones.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'post_id'       => [
							'type'        => 'integer',
							'description' => 'The post ID.',
						],
						'relationships' => [
							'type'        => 'array',
							'description' => 'Array of relationship objects with url and rels.',
							'items'       => [
								'type'       => 'object',
								'properties' => [
									'url'  => [
										'type'   => 'string',
										'format' => 'uri',
									],
									'rels' => [
										'type'  => 'array',
										'items' => [ 'type' => 'string' ],
									],
								],
							],
						],
					],
					'required'   => [ 'post_id', 'relationships' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
						'applied' => [ 'type' => 'integer' ],
					],
				],
				'execute_callback'    => [ $this, 'execute_set_relationships' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Register the xfn/get-meta-relationships ability.
	 */
	private function register_get_relationships(): void {
		wp_register_ability(
			'xfn/get-meta-relationships',
			[
				'label'               => __( 'Get XFN Relationships', 'link-extension-for-xfn' ),
				'description'         => __( 'Retrieve all XFN relationships for a post.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'post_id' => [
							'type'        => 'integer',
							'description' => 'The post ID.',
						],
					],
					'required'   => [ 'post_id' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'relationships' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'url'  => [ 'type' => 'string' ],
									'rels' => [
										'type'  => 'array',
										'items' => [ 'type' => 'string' ],
									],
								],
							],
						],
					],
				],
				'execute_callback'    => [ $this, 'execute_get_relationships' ],
				'permission_callback' => function () {
					return current_user_can( 'read' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Register the xfn/add-meta-relationship ability.
	 */
	private function register_add_relationship(): void {
		wp_register_ability(
			'xfn/add-meta-relationship',
			[
				'label'               => __( 'Add XFN Relationship', 'link-extension-for-xfn' ),
				'description'         => __( 'Add an XFN relationship to a post.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'post_id' => [
							'type'        => 'integer',
							'description' => 'The post ID.',
						],
						'url'     => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => 'The URL to associate relationships with.',
						],
						'rels'    => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Array of XFN relationship values.',
						],
					],
					'required'   => [ 'post_id', 'url', 'rels' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
					],
				],
				'execute_callback'    => [ $this, 'execute_add_relationship' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Register the xfn/remove-meta-relationship ability.
	 */
	private function register_remove_relationship(): void {
		wp_register_ability(
			'xfn/remove-meta-relationship',
			[
				'label'               => __( 'Remove XFN Relationship', 'link-extension-for-xfn' ),
				'description'         => __( 'Remove an XFN relationship from a post by URL.', 'link-extension-for-xfn' ),
				'category'            => XFN_Abilities_Manager::CATEGORY_SLUG,
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'post_id' => [
							'type'        => 'integer',
							'description' => 'The post ID.',
						],
						'url'     => [
							'type'        => 'string',
							'format'      => 'uri',
							'description' => 'The URL whose relationships should be removed.',
						],
					],
					'required'   => [ 'post_id', 'url' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success' => [ 'type' => 'boolean' ],
					],
				],
				'execute_callback'    => [ $this, 'execute_remove_relationship' ],
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'meta'                => [
					'show_in_rest' => true,
					'version'      => '1.0.0',
				],
			]
		);
	}

	/**
	 * Execute the xfn/set-meta-relationships ability.
	 *
	 * @param array $input Validated ability input.
	 * @return array Ability result.
	 */
	public function execute_set_relationships( array $input ): array {
		$post_id       = (int) $input['post_id'];
		$relationships = (array) $input['relationships'];

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'success' => false,
				'error'   => 'Post not found.',
			];
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return [
				'success' => false,
				'error'   => 'Insufficient permissions for this post.',
			];
		}

		XFN_Meta_Mirror::set_relationships( $post_id, $relationships );

		$stored = XFN_Meta_Mirror::get_relationships( $post_id );

		return [
			'success' => true,
			'applied' => count( $stored ),
		];
	}

	/**
	 * Execute the xfn/get-meta-relationships ability.
	 *
	 * @param array $input Validated ability input.
	 * @return array Ability result.
	 */
	public function execute_get_relationships( array $input ): array {
		$post_id = (int) $input['post_id'];

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'relationships' => [],
				'error'         => 'Post not found.',
			];
		}

		if ( ! current_user_can( 'read_post', $post_id ) ) {
			return [
				'relationships' => [],
				'error'         => 'Insufficient permissions for this post.',
			];
		}

		$relationships = XFN_Meta_Mirror::get_relationships( $post_id );

		return [
			'relationships' => $relationships,
		];
	}

	/**
	 * Execute the xfn/add-meta-relationship ability.
	 *
	 * @param array $input Validated ability input.
	 * @return array Ability result.
	 */
	public function execute_add_relationship( array $input ): array {
		$post_id = (int) $input['post_id'];
		$url     = (string) $input['url'];
		$rels    = (array) $input['rels'];

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'success' => false,
				'error'   => 'Post not found.',
			];
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return [
				'success' => false,
				'error'   => 'Insufficient permissions for this post.',
			];
		}

		XFN_Meta_Mirror::add_relationship( $post_id, $url, $rels );

		return [
			'success' => true,
		];
	}

	/**
	 * Execute the xfn/remove-meta-relationship ability.
	 *
	 * @param array $input Validated ability input.
	 * @return array Ability result.
	 */
	public function execute_remove_relationship( array $input ): array {
		$post_id = (int) $input['post_id'];
		$url     = (string) $input['url'];

		$post = get_post( $post_id );
		if ( ! $post ) {
			return [
				'success' => false,
				'error'   => 'Post not found.',
			];
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return [
				'success' => false,
				'error'   => 'Insufficient permissions for this post.',
			];
		}

		XFN_Meta_Mirror::remove_relationship( $post_id, $url );

		return [
			'success' => true,
		];
	}
}
