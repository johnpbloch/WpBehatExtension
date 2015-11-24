<?php

namespace JPB\WpBehatExtension\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class TransactionContext implements Context {

	/**
	 * @BeforeScenario
	 */
	public function setUpWpTransactions( BeforeScenarioScope $scope ) {
		global $wpdb;
		$wpdb->query( 'SET autocommit = 0;' );
		$wpdb->query( 'START TRANSACTION;' );
		add_filter( 'query', array( $this, '_create_temporary_tables' ) );
		add_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}

	public function _create_temporary_tables( $query ) {
		if ( 'CREATE TABLE' === substr( trim( $query ), 0, 12 ) ) {
			return substr_replace( trim( $query ), 'CREATE TEMPORARY TABLE', 0, 12 );
		}

		return $query;
	}

	public function _drop_temporary_tables( $query ) {
		if ( 'DROP TABLE' === substr( trim( $query ), 0, 10 ) ) {
			return substr_replace( trim( $query ), 'DROP TEMPORARY TABLE', 0, 10 );
		}

		return $query;
	}

	/**
	 * @AfterScenario
	 */
	public function tearDownWpTransactions( AfterScenarioScope $scope ) {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
		remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
	}

}
