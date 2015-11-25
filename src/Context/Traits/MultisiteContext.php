<?php
namespace JPB\WpBehatExtension\Context\Traits;

trait MultisiteContext {

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
