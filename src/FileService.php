<?php

namespace Edeoliv\GptTranslate;

class FileService
{

    /**
     * Save array of strings into a file with json format on a given path
     */
    public function strings_file($lang = "en", $path = ".")
    {
        $strings_array = $this->strings_keys();
        $json = json_encode($strings_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
            $new_strings = array_diff($strings_array, $old_strings);
            $strings_array = array_merge($old_strings, $new_strings);
            $json = json_encode($strings_array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        return file_put_contents($file, $json);
    }


    /**
     * Generate key value array from strings
     */
    public function strings_keys()
    {
        $strings = $this->get_strings();
        // the format mus be string => string
        $keys = [];
        foreach ($strings as $string) {
            $keys[$string] = $string;
        }
        return $keys;
    }

    /**
     * Get all translation strings from all files
     */
    public function get_strings()
    {
        $files = $this->get_files();
        $strings = [];
        foreach ($files as $file) {
            $strings = array_merge($strings, $this->get_strings_in_file($file));
        }
        // remove empty strings
        $strings = array_filter($strings, function ($string) {
            return !empty($string);
        });
        // remove "\" sacapes like "\'"
        $strings = array_map(function ($string) {
            return str_replace("\\'", "'", $string);
        }, $strings);
        // remove duplicates
        $strings = array_unique($strings);
        // sort strings
        sort($strings);
        // return all strings
        return $strings;
    }

    /**
     * Get translation strings from a file
     */
    public function get_strings_in_file($file)
    {
        $content = file_get_contents($file);
        $content = str_replace("\n", "", $content);
        $content = str_replace("\r", "", $content);
        // regular expression to find all __(), @lang(), $t() and trans() calls
        $patterns = [
            '/__\(\s*\"(.*?)\"[^)]*\)/s',
            "/__\(\s*'((?:[^']|\\')*?)'\s*(?:,|\))/s",
            '/@lang\(\s*\"(.*?)\"[^)]*\)/s',
            "/@lang\(\s*'((?:[^']|\\')*?)'\s*(?:,|\))/s",
            '/\$t\(\s*\"(.*?)\"[^)]*\)/s',
            "/\\\$t\(\s*'((?:[^']|\\')*?)'\s*(?:,|\))/s",
            '/trans\(\s*\"(.*?)\"[^)]*\)/s',
            "/trans\(\s*'((?:[^']|\\')*?)'\s*(?:,|\))/s"
        ];
        $matches = [];
        // go through each pattern
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $currentMatches, PREG_SET_ORDER);
            // add all matches to the matches array
            foreach ($currentMatches as $match) {
                $matches[] = $match[1];
            }
        }
        // return all translate lines
        return $matches;
    }

    /**
     * List all files php, vue, js, ts in app and resources directories
     */
    public function get_files()
    {
        $files = [];
        $files = array_merge($files, $this->get_files_in_directory(app_path()));
        $files = array_merge($files, $this->get_files_in_directory(resource_path()));
        return $files;
    }

    /**
     * List all files php, vue, js, ts in a directory
     */
    public function get_files_in_directory($directory)
    {
        $files = [];
        $files = array_merge($files, $this->get_files_in_directory_by_extension($directory, "php"));
        $files = array_merge($files, $this->get_files_in_directory_by_extension($directory, "vue"));
        $files = array_merge($files, $this->get_files_in_directory_by_extension($directory, "js"));
        $files = array_merge($files, $this->get_files_in_directory_by_extension($directory, "ts"));
        return $files;
    }

    /**
     * List all files in a directory by extension
     */
    public function get_files_in_directory_by_extension($directory, $extension) {
        $files = [];
        // check if extension starts with a dot
        $extension = ltrim($extension, '.');
        // create new recursive directory iterator
        $dir = new \RecursiveDirectoryIterator($directory);
        $ite = new \RecursiveIteratorIterator($dir);
        // go through each file in directory
        foreach ($ite as $file_info) {
            if (!$file_info->isDir()) {
                $file_name = $file_info->getFilename();
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                if ($ext == $extension) {
                    $files[] = $file_info->getPathname();
                }
            }
        }
        // return all files
        return $files;
    }
}
