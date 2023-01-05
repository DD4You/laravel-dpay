<?php

namespace DD4You\Dpay\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallDpay
{
    protected $signature = 'dd4you:install-dpay';

    protected $description = 'Publish Dpay file';

    public function handle()
    {
        $this->info('Installing Dpay...');

        # PUBLISH configuration file
        $this->info('Publishing configuration file...');
        if (!self::isExists('config\dpay.php')) {
            self::publishConfig('dpay.php');
            $this->info('Published configuration file');
        } else {
            if ($this->shouldOverwrite('Config')) {
                $this->info('Overwriting configuration file...');
                self::publishConfig('dpay.php');
            } else {
                $this->info('Existing configuration file was not overwritten');
            }
        }
        # PUBLISH configuration file END

        $this->info('Installed Dpay');
    }

    protected static function isExists($fileName)
    {
        return File::exists(base_path($fileName));
    }
    protected static function publishConfig($fileName)
    {
        self::createFile(config_path() . DIRECTORY_SEPARATOR, $fileName, self::getContent($fileName));
    }
    protected static function getContent($fileName, $prefix = "")
    {
        // $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        return file_get_contents(__DIR__ . "/stubs/$prefix$fileName.stub");
    }
    protected function shouldOverwrite($fileName)
    {
        return $this->confirm(
            $fileName . ' file already exists. Do you want to overwrite it?',
            false
        );
    }
    protected static function createFile($path, $fileName, $contents)
    {
        if (!file_exists($path)) mkdir($path, 0755, true);

        $path = $path . $fileName;

        file_put_contents($path, $contents);
    }
}
