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
    protected $signature = 'translate:make {--lang=} {--path=}';

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
            $lang = $this->option('lang') ?? "en";
            $path = $this->option('path') ?? base_path('lang');
            $service = new FileService();
            $service->strings_file($lang, $path);
            $this->info("File created successfully in {$path}/{$lang}.json");
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
