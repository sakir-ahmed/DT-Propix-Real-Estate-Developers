<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('rejects emoji and symbol icons in application files', function () {
    $pattern = '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{25A0}-\x{25FF}\x{FE0F}]/u';
    $extensions = ['php', 'vue', 'js', 'css', 'md', 'html'];
    $offenders = [];

    foreach (['app', 'resources', 'lang', 'database', 'docs'] as $directory) {
        foreach (File::allFiles(base_path($directory)) as $file) {
            if (! in_array($file->getExtension(), $extensions, true)) {
                continue;
            }

            $contents = File::get($file->getPathname());

            if (preg_match($pattern, $contents) === 1) {
                $offenders[] = $file->getRelativePathname();
            }
        }
    }

    expect($offenders)->toBeEmpty();
});
