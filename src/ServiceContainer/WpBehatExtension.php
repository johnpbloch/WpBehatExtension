<?php

namespace JPB\WpBehatExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class WpBehatExtension
	implements Extension {

	/**
	 * {@inheritDoc}
	 */
	public function process( ContainerBuilder $container ) {
		// TODO: Implement process() method.
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfigKey() {
		return 'wp';
	}

	/**
	 * {@inheritDoc}
	 */
	public function initialize( ExtensionManager $extensionManager ) {
		// TODO: Implement initialize() method.
	}

	/**
	 * {@inheritDoc}
	 */
	public function configure( ArrayNodeDefinition $builder ) {
		$builder
			->addDefaultsIfNotSet()
			->children()
				->scalarNode( 'path' )
					->defaultValue( __DIR__ . 'vendor' )
				->end()
			->end();
	}

	/**
	 * {@inheritdoc}
	 */
	public function load(ContainerBuilder $container, array $config)
	{
		$this->loadContextInitializer($container);
		$container->setParameter('wp.parameters', $config);
	}

	/**
	 * Register a Context Initializer service for the behat
	 *
	 * @param ContainerBuilder $container the service will check for JPB\WpBehat\Context\WpContext contexts
	 */
	private function loadContextInitializer(ContainerBuilder $container)
	{
		$definition = new Definition('JPB\WpBehatExtension\Context\Initializer\WpContextInitializer', array(
			'%wp.parameters%',
			'%mink.parameters%',
			'%paths.base%',
		));
		$definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
		$container->setDefinition('behat.wp.service.wp_context_initializer', $definition);
	}

}
