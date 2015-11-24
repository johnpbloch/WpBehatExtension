<?php

namespace JPB\WpBehatExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;

class WpContext extends RawMinkContext {

	/**
	 * @Given I am not logged in
	 */
	public function iAmNotLoggedIn() {
		$this->getSession()->reset();
		$this->getSession()->visit( wp_logout_url() );
	}

	/**
	 * @Given I am logged in as :username with :password
	 */
	public function iAmLoggedInAs( $username, $password ) {
		$this->getSession()->reset();
		$this->getSession()->visit( wp_login_url() );
		$currentPage = $this->getSession()->getPage();

		$currentPage->fillField( 'user_login', $username );
		$currentPage->fillField( 'user_pass', $password );
		$currentPage->findButton( 'wp-submit' )->click();

		\PHPUnit_Framework_Assert::assertNotEquals( $this->getSession()->getCurrentUrl(), wp_login_url() );
	}

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

}
