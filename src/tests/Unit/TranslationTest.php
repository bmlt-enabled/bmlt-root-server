<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use function Composer\Autoload\includeFile;

class TranslationTest extends TestCase
{
    public function testTranslationsExist()
    {
        $langPath = __DIR__ . '/../../lang';
        $englishPath = $langPath . '/en';

        $englishTranslations = collect(scandir($englishPath))
            ->reject(fn ($dir) => $dir == '.' || $dir == '..')
            ->reject(fn ($dir) => $dir == 'auth.php' || $dir == 'pagination.php' || $dir == 'passwords.php' || $dir == 'validation.php')
            ->mapWithKeys(fn ($filename, $_) => [$filename => include($englishPath . '/' . $filename)]);

        $otherLanguages = collect(scandir($langPath))
            ->reject(fn ($dir) => $dir == '.' || $dir == '..' || $dir == 'en')
            ->map(fn ($dir) => $langPath . '/' . $dir);

        foreach ($otherLanguages as $otherPath) {
            $otherTranslations = collect(scandir($otherPath))
                ->reject(fn ($dir) => $dir == '.' || $dir == '..')
                ->mapWithKeys(fn ($filename, $_) => [$filename => include($otherPath . '/' . $filename)]);

            $this->assertEquals(count($englishTranslations), count($otherTranslations));

            foreach ($englishTranslations as $filename => $translations) {
                $this->assertTrue(isset($otherTranslations[$filename]));
                $this->assertEquals(count($englishTranslations[$filename]), count($otherTranslations[$filename]));
                $englishKeys = array_keys($englishTranslations[$filename]);
                foreach ($englishKeys as $key) {
                    $this->assertTrue(isset($otherTranslations[$filename][$key]));
                }
            }
        }

        $this->assertTrue(true);
    }
}
