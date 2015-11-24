<?php

namespace JPB\WpBehatExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;

class AuthenticationContext extends RawMinkContext {

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
		$login_url = wp_login_url();
		$this->getSession()->visit( $login_url );
		$currentPage = $this->getSession()->getPage();

		$currentPage->fillField( 'user_login', $username );
		$currentPage->fillField( 'user_pass', $password );
		$currentPage->findButton( 'wp-submit' )->click();

		\PHPUnit_Framework_Assert::assertNotEquals( $this->getSession()->getCurrentUrl(), $login_url );
	}

}
