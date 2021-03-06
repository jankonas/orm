<?php declare(strict_types = 1);

namespace Nettrine\ORM\DI;

use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand;
use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand;
use Doctrine\ORM\Tools\Console\Command\RunDqlCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceCreationException;
use Nette\DI\Statement;
use Nette\InvalidStateException;

class OrmConsoleExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		if (!$this->compiler->getExtensions(OrmExtension::class)) {
			throw new InvalidStateException(
				sprintf('You should register %s before %s.', OrmExtension::class, get_class($this))
			);
		}

		if (!class_exists('Symfony\Component\Console\Application')) {
			throw new ServiceCreationException('Missing Symfony\Component\Console\Application service');
		}

		// Skip if it's not CLI mode
		if (PHP_SAPI !== 'cli') {
			return;
		}

		$builder = $this->getContainerBuilder();
		// Helpers
		$builder->addDefinition($this->prefix('entityManagerHelper'))
			->setClass(EntityManagerHelper::class)
			->setAutowired(false);

		// Commands
		$builder->addDefinition($this->prefix('schemaToolCreateCommand'))
			->setClass(CreateCommand::class)
			->addTag('console.command', 'orm:schema-tool:create')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('schemaToolUpdateCommand'))
			->setClass(UpdateCommand::class)
			->addTag('console.command', 'orm:schema-tool:update')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('schemaToolDropCommand'))
			->setClass(DropCommand::class)
			->addTag('console.command', 'orm:schema-tool:drop')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('convertMappingCommand'))
			->setClass(ConvertMappingCommand::class)
			->addTag('console.command', 'orm:convert-mapping')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('ensureProductionSettingsCommand'))
			->setClass(EnsureProductionSettingsCommand::class)
			->addTag('console.command', 'orm:ensure-production-settings')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('generateEntitiesCommand'))
			->setClass(GenerateEntitiesCommand::class)
			->addTag('console.command', 'orm:generate-entities')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('generateProxiesCommand'))
			->setClass(GenerateProxiesCommand::class)
			->addTag('console.command', 'orm:generate-proxies')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('generateRepositoriesCommand'))
			->setClass(GenerateRepositoriesCommand::class)
			->addTag('console.command', 'orm:generate-repositories')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('infoCommand'))
			->setClass(InfoCommand::class)
			->addTag('console.command', 'orm:info')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('mappingDescribeCommand'))
			->setClass(MappingDescribeCommand::class)
			->addTag('console.command', 'orm:mapping:describe')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('runDqlCommand'))
			->setClass(RunDqlCommand::class)
			->addTag('console.command', 'orm:run-dql')
			->setAutowired(false);
		$builder->addDefinition($this->prefix('validateSchemaCommand'))
			->setClass(ValidateSchemaCommand::class)
			->addTag('console.command', 'orm:validate-schema')
			->setAutowired(false);
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		// Skip if it's not CLI mode
		if (PHP_SAPI !== 'cli') {
			return;
		}

		$builder = $this->getContainerBuilder();

		// Lookup for Symfony Console Application
		$application = $builder->getDefinitionByType('Symfony\Component\Console\Application');

		// Register helpers
		$entityManagerHelper = $this->prefix('@entityManagerHelper');
		$application->addSetup(new Statement('$service->getHelperSet()->set(?,?)', [$entityManagerHelper, 'em']));
	}

}
