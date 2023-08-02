<?php

namespace Rdosgroup\GptTranslate\Console;


use Illuminate\Console\Command;
use Rdosgroup\GptTranslate\FileService;

class TranslateMake extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'translate:make {--lang=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a json file with all strings to translate';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $service = new FileService();
            $service->strings_file($this->option('lang') ?? "en", base_path("lang"));
            $this->info("File created successfully");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
