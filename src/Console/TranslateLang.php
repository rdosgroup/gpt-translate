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
    protected $signature = 'translate:lang {--origin=} {--lang=} {--context=} {--model=gpt-4o} {--exclude=}';

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
            $context = $this->option('context') ?? config('gpt-translate.default_context') ?? '';
            $model = $this->option('model') ?? "gpt-4o";
            $exclude = $this->option('exclude') ? explode(',', $this->option('exclude')) : explode(',', config('gpt-translate.exclude_words')) ?? [];
            if (!empty($exclude)) {
                $excludeText = "IMPORTANT: Never translate the following words or phrases: '" . implode("', '", $exclude) . "'. These should always remain in their original form.";
                $context .= "\n\n" . $excludeText;
            }
            $service = new OpenaiService();
            $service->translate_file(base_path("lang"), $this->option('origin') ?? "en", $this->option('lang') ?? "es", $context, $model);
            $this->info("\File translated successfully");
            $this->info('Translation finished at ' . Carbon::now()->toDateTimeString());
        } catch (\Throwable $th) {
            $this->error($th->getMessage());
        }
    }
}
