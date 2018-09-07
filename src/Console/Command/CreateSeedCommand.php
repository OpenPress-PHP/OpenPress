<?php
namespace OpenPress\Console\Command;

use OpenPress\Application;
use OpenPress\Plugin\Loader;
use Phinx\Console\Command\SeedCreate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateSeedCommand extends SeedCreate
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
        $this->setName("seed:create");
        $this->addArgument("plugin", InputArgument::REQUIRED, "The plugin to create this migration for.");
    }

    protected function getSeedPath(InputInterface $input, OutputInterface $output)
    {
        $plugin = $input->getArgument("plugin");
        $enabledPlugins = $this->container->get(Loader::class)->getEnabledPlugins();

        if ($plugin == "root") {
            $path = __DIR__ . "/../../../app/db/seeds";
        } elseif (in_array($plugin, array_keys($enabledPlugins))) {
            $path = $enabledPlugins[$plugin]->getSeedsDirectory();
        }
        $path = realpath($path);

        $fileSystem = new Filesystem();
        if (!$fileSystem->exists($path)) {
            $fileSystem->mkdir($path, 0755);
        }

        return $path;
    }
}
