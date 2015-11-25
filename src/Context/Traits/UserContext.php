<?php

namespace JPB\WpBehatExtension\Context\Traits;

use Behat\Gherkin\Node\TableNode;

trait UserContext {

	/**
	 * @Given /^Users exist:$/
	 */
	public function usersExist( TableNode $table ) {
		$usersData = $table->getHash();
		add_filter( 'send_password_change_email', '__return_false' );
		add_filter( 'send_email_change_email', '__return_false' );
		foreach ( $usersData as $userData ) {
			if ( empty( $userData['login'] ) ) {
				throw new \InvalidArgumentException( 'You must provide a user login!' );
			}
			$user = get_user_by( 'login', $userData['login'] );
			$data = $this->getDataFromTable( $userData );
			if ( $user ) {
				$data['ID'] = $user->ID;
			}
			$result = $user ? wp_update_user( $data ) : wp_insert_user( $data );
			if ( is_wp_error( $result ) ) {
				throw new \UnexpectedValueException( 'User could not be created: ' . $result->get_error_message() );
			}
			foreach ( $this->getMetaDataFromTable( $userData ) as $key => $value ) {
				update_user_meta( $user->ID, $key, $value );
			}
		}
		remove_filter( 'send_password_change_email', '__return_false' );
		remove_filter( 'send_email_change_email', '__return_false' );
	}

	/**
	 * @param $userData
	 *
	 * @return array
	 */
	private function getDataFromTable( $userData ) {
		$data               = array( 'user_login' => $userData['login'] );
		$data['user_email'] = empty( $userData['email'] ) ? $userData['login'] . '@example.com' : $userData['email'];
		$data['user_pass']  = empty( $userData['password'] ) ? wp_generate_password() : $userData['password'];
		if ( ! empty( $userData['display_name'] ) ) {
			$data['display_name'] = $userData['display_name'];
		}
		if ( ! empty( $userData['first_name'] ) ) {
			$data['first_name'] = $userData['first_name'];
		}
		if ( ! empty( $userData['last_name'] ) ) {
			$data['last_name'] = $userData['last_name'];
		}
		if ( ! empty( $userData['role'] ) ) {
			$data['role'] = $userData['role'];
		}

		return $data;
	}

	private function getMetaDataFromTable( $userData ) {
		$meta = array();
		foreach ( $userData as $key => $value ) {
			if ( 'meta_' === substr( $key, 0, 5 ) ) {
				$meta[ substr( $key, 5 ) ] = $value;
			}
		}

		return $meta;
	}

	/**
	 * @Given User :login is a super-admin
	 */
	public function userIsASuperAdmin( $login ) {
		$user = get_user_by( 'login', $login );
		if ( ! $user ) {
			throw new \InvalidArgumentException( sprintf( 'No user found with username %s!', $login ) );
		}
		grant_super_admin( $user->ID );
	}

}
