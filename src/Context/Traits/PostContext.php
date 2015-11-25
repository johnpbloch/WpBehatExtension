<?php

namespace JPB\WpBehatExtension\Context\Traits;

use Behat\Gherkin\Node\TableNode;

trait PostContext {

	/**
	 * @Given Posts exist:
	 */
	public function postsExist( TableNode $table ) {
		$postsData = $table->getHash();
		foreach ( $postsData as $postData ) {
			$this->processPost( $postData );
		}
	}

	/**
	 * @param $postData
	 *
	 * @return array
	 */
	private function getpostDataFromTable( $postData ) {
		$author = null;
		if ( ! empty( $postData['post_author'] ) ) {
			$author = $postData['post_author'];
		} elseif ( ! empty( $postData['author'] ) ) {
			$author = $postData['post_author'] = $postData['author'];
			unset( $postData['author'] );
		}
		if ( $author && ! is_numeric( $author ) ) {
			$user = get_user_by( 'login', $author );
			if ( $user ) {
				$postData['post_author'] = $user->ID;
			}
		}

		foreach (
			[
				'author',
				'content',
				'title',
				'excerpt',
				'status',
				'type',
				'password',
				'parent',
				'name'
			] as $field
		) {
			$full_field = "post_$field";
			if ( isset( $postData[ $field ] ) && empty( $postData[ $full_field ] ) ) {
				$postData[ $full_field ] = $postData[ $field ];
				unset( $postData[ $field ] );
			}
		}
		$data = array_intersect_key( $postData, [
			'post_author'           => '',
			'post_content'          => '',
			'post_content_filtered' => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_status'           => '',
			'post_type'             => '',
			'comment_status'        => '',
			'ping_status'           => '',
			'post_password'         => '',
			'to_ping'               => '',
			'pinged'                => '',
			'post_parent'           => '',
			'menu_order'            => '',
			'guid'                  => '',
		] );

		return $data;
	}

	private function getPostMetaDataFromTable( $postData ) {
		$meta = array();
		foreach ( $postData as $key => $value ) {
			if ( 'meta_' === substr( $key, 0, 5 ) ) {
				$meta[ substr( $key, 5 ) ] = $value;
			}
		}

		return $meta;
	}

	/**
	 * @param $postData
	 */
	private function processPost( $postData ) {
		$data    = $this->getpostDataFromTable( $postData );
		$post_id = wp_insert_post( $data, true );
		if ( is_wp_error( $post_id ) ) {
			throw new \UnexpectedValueException( 'Could not save post: ' . $post_id->get_message() );
		}
		foreach ( $this->getPostMetaDataFromTable( $postData ) as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

}
