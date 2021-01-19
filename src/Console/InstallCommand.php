<?php

namespace McCaulay\Duskless\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'duskless:install
                {--proxy= : The proxy to download the binary through (example: "tcp://127.0.0.1:9000")}
                {--ssl-no-verify : Bypass SSL certificate verification when installing through a proxy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Duskless into the application';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! is_dir(base_path('app/Browser/Pages'))) {
            mkdir(base_path('app/Browser/Pages'), 0755, true);
        }

        if (! is_dir(base_path('app/Browser/Components'))) {
            mkdir(base_path('app/Browser/Components'), 0755, true);
        }

        if (! is_dir(base_path('app/Browser/screenshots'))) {
            $this->createScreenshotsDirectory();
        }

        if (! is_dir(base_path('app/Browser/console'))) {
            $this->createConsoleDirectory();
        }

        $stubs = [
            'HomePage.stub' => base_path('app/Browser/Pages/HomePage.php'),
            'Page.stub' => base_path('app/Browser/Pages/Page.php'),
        ];

        foreach ($stubs as $stub => $file) {
            if (! is_file($file)) {
                copy(__DIR__.'/../../stubs/'.$stub, $file);
            }
        }

        $this->info('Duskless scaffolding installed successfully.');

        $this->comment('Downloading ChromeDriver binaries...');

        $driverCommandArgs = ['--all' => true];

        if ($this->option('proxy')) {
            $driverCommandArgs['--proxy'] = $this->option('proxy');
        }

        if ($this->option('ssl-no-verify')) {
            $driverCommandArgs['--ssl-no-verify'] = true;
        }

        $this->call('duskless:chrome-driver', $driverCommandArgs);
    }

    /**
     * Create the screenshots directory.
     *
     * @return void
     */
    protected function createScreenshotsDirectory()
    {
        mkdir(base_path('app/Browser/screenshots'), 0755, true);

        file_put_contents(base_path('app/Browser/screenshots/.gitignore'), '*
!.gitignore
');
    }

    /**
     * Create the console directory.
     *
     * @return void
     */
    protected function createConsoleDirectory()
    {
        mkdir(base_path('app/Browser/console'), 0755, true);

        file_put_contents(base_path('app/Browser/console/.gitignore'), '*
!.gitignore
');
    }
}
