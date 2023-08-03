<?php

namespace Rdosgroup\GptTranslate\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Rdosgroup\GptTranslate\OpenaiService;

class TranslateLang extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'translate:lang {--origin=} {--lang=} {--context=} {--model=gpt-3.5-turbo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate a json file with all strings to translate to a given language';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Starting translation at ' . Carbon::now()->toDateTimeString());
        $this->info('Processing... Please wait.');
        try {
            $service = new OpenaiService();
            $service->translate_file(base_path("lang"), $this->option('origin') ?? "en", $this->option('lang') ?? "es", $this->option('context') ?? "", $this->option('model') ?? "gpt-3.5-turbo");
            $this->info("\File translated successfully");
            $this->info('Translation finished at ' . Carbon::now()->toDateTimeString());
        } catch (\Throwable $th) {
            $this->stopSpinner();
            $this->error($th->getMessage());
        }

    }

}
