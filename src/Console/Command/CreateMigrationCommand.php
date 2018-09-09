<?php
namespace OpenPress\Console\Command;

use OpenPress\Application;
use OpenPress\Content\Loader;
use Symfony\Component\Filesystem\Filesystem;
use Phinx\Console\Command\Create as CreateCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateMigrationCommand extends CreateCommand
{
    private $container;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->container = $app->getContainer();
    }

    protected function configure()
    {
        parent::configure();
        $this->setName("db:create");
        $this->addArgument("plugin", InputArgument::REQUIRED, "The plugin to create this migration for.");
    }

    protected function getMigrationPath(InputInterface $input, OutputInterface $output)
    {
        $plugin = $input->getArgument("plugin");
        $enabledPlugins = $this->container->get(Loader::class)->getEnabledPlugins();

        if ($plugin == "root") {
            $path = __DIR__ . "/../../../app/db/migrations";
        } elseif (in_array($plugin, array_keys($enabledPlugins))) {
            $path = $enabledPlugins[$plugin]->getMigrationsDirectory();
        } else {
            $output->writeln("Plugin '{$plugin}' not found");
            exit();
        }

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($path)) {
            $fileSystem->mkdir($path, 0755);
        }

        return $path;
    }
}
