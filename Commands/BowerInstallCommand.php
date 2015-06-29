<?php

namespace Pingpong\Modules\Commands;

use Illuminate\Console\Command;
use Pingpong\Modules\Module;
use Pingpong\Modules\Repository;

class BowerInstallCommand extends Command
{

    /**
     * @var string
     */
    protected $signature = 'module:bower-install {--production}';

    /**
     * @var string
     */
    protected $description = 'Runs `bower install` on all modules, where applicable';

    /**
     * @var Repository;
     */
    protected $modules;

    /**
     * BowerInstallCommand constructor.
     * @param Repository $module
     */
    public function __construct(Repository $modules)
    {
        parent::__construct();

        $this->modules = $modules;
    }


    public function fire()
    {
        // Check bower is installed
        if (!$this->isBowerInstalled()) return;

        foreach($this->modules->all() as $module) {
            $module = $this->modules->findOrFail($module);

            $this->handleModuleBowerInstall($module);
        }
    }


    protected function handleModuleBowerInstall(Module $module)
    {
        // Check if bower.json exists
        $path = $module->getPath();
        if (!file_exists($path . '/bower.json')) return;

        // Run bower install
        $command = "cd {$path} && bower install";

        if ($this->option('production')) {
            $command .= ' --production';
        }

        $this->info("Installing bower components for module '{$module->getName()}'");

        $result = null;
        passthru($command, $result);

        if ($result !== 0) {
            $this->error('Bower install ended with error!');
        }
    }

    /**
     * @return boolean
     */
    protected function isBowerInstalled()
    {
        $result = null;
        $output = [];
        exec('bower', $output, $result);

        if ($result === 127) {
            $this->error('Bower is not installed, or could not be found.');
            return false;
        } elseif ($result !== 0) {
            $this->error('An error occurred while checking Bower installation: ' . implode(PHP_EOL, $output));
            return false;
        }

        return true;
    }
}