<?php

namespace JPB\WpBehatExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Symfony\Component\Finder\Finder;

class WpContextInitializer implements ContextInitializer {

	private $wordpressParams;
	private $minkParams;
	private $basePath;
	private static $initialized = false;

	/**
	 * inject the wordpress extension parameters and the mink parameters
	 *
	 * @param array  $wordpressParams
	 * @param array  $minkParams
	 * @param string $basePath
	 */
	public function __construct( $wordpressParams, $minkParams, $basePath ) {
		$this->wordpressParams = $wordpressParams;
		$this->minkParams      = $minkParams;
		$this->basePath        = $basePath;
	}

	/**
	 * setup the wordpress environment / stack if the context is a wordpress context
	 *
	 * @param Context $context
	 */
	public function initializeContext( Context $context ) {
		if ( static::$initialized ) {
			return;
		}
		static::$initialized = true;
		$this->prepareEnvironment();
		$this->loadStack();
	}

	/**
	 * prepare environment variables
	 */
	private function prepareEnvironment() {
		// wordpress uses these superglobal fields everywhere...
		$urlComponents              = parse_url( $this->minkParams['base_url'] );
		$_SERVER['HTTP_HOST']       = $urlComponents['host'] . ( isset( $urlComponents['port'] ) ? ':' . $urlComponents['port'] : '' );
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';


		// we don't have a request uri in headless scenarios:
		// wordpress will try to "fix" php_self variable based on the request uri, if not present
		$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';
	}

	/**
	 * actually load the wordpress stack
	 */
	private function loadStack() {
		$finder = new Finder();

		// load the wordpress "stack"
		$finder->files()->in( $this->wordpressParams['path'] )->depth( '== 0' )->name( 'wp-load.php' );

		foreach ( $finder as $bootstrapFile ) {
			$this->simulateGlobalRequire( $bootstrapFile->getRealpath() );
		}
	}

	private function simulateGlobalRequire( $__file_to_require ) {
		// OMGWTFWP this is for multisite because YAY globals in procedural code!
		if ( ! isset( $GLOBALS['wpdb'] ) ) {
			$GLOBALS['wpdb'] = null;
		}
		foreach ( $this->getGlobalVarNames() as $gVar ) {
			$$gVar = &$GLOBALS[ $gVar ];
		}
		unset( $gVar );
		$__pre_vars = get_defined_vars();
		require $__file_to_require;
		$__after_vars = array_diff_key( get_defined_vars(), $__pre_vars );
		if ( isset( $__after_vars['__pre_vars'] ) ) {
			unset( $__after_vars['__pre_vars'] );
		}
		foreach ( $__after_vars as $var_name => $var ) {
			$GLOBALS[ $var_name ] = $var;
		}
	}

	/**
	 * @return array
	 */
	private function getGlobalVarNames() {
		return array_diff(
			array_keys( $GLOBALS ),
			array(
				'_GET',
				'_POST',
				'_COOKIE',
				'_FILES',
				'_SERVER',
				'_REQUEST',
				'GLOBALS',
				'PHP_SELF',
				'__file_to_require',
				'__pre_vars'
			)
		);
	}

}
