<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Process\Process;

class ServeCommand extends BaseServeCommand
{
    /**
     * The Vite process instance.
     *
     * @var \Symfony\Component\Process\Process|null
     */
    protected $viteProcess = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->portOffset === 0) {
            $this->components->info('Starting Vite development server...');
            
            // Start Vite dev server in the background. 
            // Process::fromShellCommandline helps resolve npm on Windows/macOS/Linux shell environments.
            $this->viteProcess = Process::fromShellCommandline('npm run dev');
            $this->viteProcess->setTimeout(null);
            
            try {
                $this->viteProcess->start();
            } catch (\Exception $e) {
                $this->components->error('Failed to start Vite dev server: ' . $e->getMessage());
            }
        }

        try {
            $status = parent::handle();
        } finally {
            if ($this->viteProcess && $this->viteProcess->isRunning()) {
                $this->components->info('Stopping Vite development server...');
                $this->viteProcess->stop();
            }
        }

        return $status;
    }
}
