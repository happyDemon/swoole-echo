<?php

namespace HappyDemon\SwooleEcho\Commands;


use DragonFly\ArtisanEcho\Swoole;
use Illuminate\Console\Command;

class SwooleEcho extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'echo {action : start,stop,restart}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage you echo server';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        switch($this->argument('action'))
        {
            case 'start':
                $this->start();
                break;
            case 'stop':
                $this->stop();
                break;
            case 'restart':
                $this->stop();
                $this->start();
                break;
        }
    }

    protected function start()
    {
        $this->info('[+] start echo socket server');

        // Socket handler
        $swooleServer = app(Swoole::class);
        $swooleServer->init();
    }

    protected function stop() {
        $this->info('socket service is shutting down...');
        echo shell_exec('sudo ps -ef | grep \'echo start\'  | grep -v grep | awk \'{ print $2 }\' | sudo xargs kill -9');
    }
}