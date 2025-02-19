<?php

namespace Rdosgroup\GptTranslate;

use OpenAI\Laravel\Facades\OpenAI;

class OpenaiService
{
    public function translate_file($path = null, $origin = 'en', $lang = 'es', $context = '', $model = "gpt-4o")
    {
        // get file from original content
        $file_origin = $path ? $path . "/$origin.json" : base_path("lang/$origin.json");
        // decode json file into array
        $strings = json_decode(file_get_contents($file_origin), true);
        // translate each string
        $translated_strings = [];
        foreach ($strings as $string) {
            $translated_strings[$string] = $this->translate_string($string, $origin, $lang, $context, $model);
        }
        // define file path
        $file = $path . "/$lang.json";
        // if file path does not exist, create it
        if (!file_exists($file)) {
            // verify if directory exists
            if (!file_exists(dirname($file))){
                // if directory does not exist, create it
                mkdir(dirname($file), 0777, true);
            } else {
                // if directory exists, create file
                touch($file);
            }
        } else {
            // if file exists only add new strings that are not in the file
            $old_strings = json_decode(file_get_contents($file), true);
            $new_strings = array_diff($translated_strings, $old_strings);
            $translated_strings = array_merge($old_strings, $new_strings);
            $json = json_encode($translated_strings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        // save file
        return file_put_contents($file, $json);
    }

    public function translate_string($string = '', $origin = 'en', $lang = 'es', $context = '', $model = "gpt-4o")
    {
        try {
            $result = OpenAI::chat()->create([
                "model" => $model,
                "messages" => [["role" => "system", "content" => $this->prompt_system($context)], ["role" => "user", "content" => $this->prompt_header($origin, $lang)], ["role" => "user", "content" => $string]],
                "temperature" => 0.4,
                "n" => 1,
            ]);
            // if the result is not empty, return the translated string
            if ($result->choices && count($result->choices) > 0 && $result->choices[0]->message) {
                $translation = $result->choices[0]->message->content ?? $string;
                return $this->sync_vars($string, $translation);

            } else {
                return $string;
            }
        } catch (\Throwable $th) {
            return $string;
        }
    }

    public function prompt_system($context = '')
    {
        if($context != '') {
            return "You are a translator. Your job is to translate the following text into the specified language, using the given context: $context. \nPlease ensure that the case (upper/lower), spaces, and special characters remain unchanged during the translation.";
        } else {
            return "You are a translator. Your job is to translate the following text to the language specified in the prompt. \nPlease ensure that the case (upper/lower), spaces, and special characters remain unchanged during the translation.";
        }
    }

    public function prompt_header($origin = 'en', $lang = 'es')
    {
        $str_origin = "english";
        switch ($origin) {
            case 'en':
                $str_origin = "english";
                break;
            case 'es':
                $str_origin = "spanish";
                break;
            case 'fr':
                $str_origin = "french";
                break;
            case 'de':
                $str_origin = "german";
                break;
            case 'it':
                $str_origin = "italian";
                break;
            case 'pt':
                $str_origin = "portuguese";
                break;
            default:
                $str_origin = "english";
                break;
        }
        $str_lang = "english";
        switch ($lang) {
            case 'en':
                $str_origin = "english";
                break;
            case 'es':
                $str_lang = "spanish";
                break;
            case 'fr':
                $str_lang = "french";
                break;
            case 'de':
                $str_lang = "german";
                break;
            case 'it':
                $str_lang = "italian";
                break;
            case 'pt':
                $str_lang = "portuguese";
                break;
            default:
                $str_lang = "english";
                break;
        }
        return "Translate the following text from $str_origin to $str_lang, ensuring you return only the translated content without added quotes or any other extraneous details. Importantly, any word prefixed with the symbol ':' should remain unchanged";
    }

    public function sync_vars($str1, $str2) {

        // find all variables with subfix :
        preg_match_all('/:(\w+)/', $str1, $matches);
        if ($matches && isset($matches[0])) {
            // for each variable with subfix : found in str1, replace it with the same variable in str2
            foreach ($matches[0] as $match) {
                $str2 = preg_replace('/' . preg_quote($match, '/') . '/', $match, $str2, 1);
            }
        }
        // return new string with replaced variables
        return $str2;
    }

}
