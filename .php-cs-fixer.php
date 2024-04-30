<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

class PhpCsFixerConfig extends Config
{
    public function __construct()
    {
        parent::__construct();


        $this
            ->setRules([ // Standard presets (High to low priority)
                '@Symfony' => true,
                '@Symfony:risky' => true,
                '@PhpCsFixer' => true,
                '@PhpCsFixer:risky' => true,
                '@PHP83Migration' => true,
                '@PHP82Migration' => true,
                '@PHP80Migration:risky' => true,
                '@PHP74Migration' => true,
                '@PHP74Migration:risky' => true,
                '@PHP73Migration' => true,
                '@PHP71Migration' => true,
                '@PHP71Migration:risky' => true,
                '@PHP70Migration' => true,
                '@PHP70Migration:risky' => true,
                '@PHP54Migration' => true,
                '@PHP56Migration:risky' => true,
                '@PER' => true,
                '@PER:risky' => true,
            ])
            ->setRiskyAllowed(true);
    }
}

return (new PhpCsFixerConfig())
    ->setCacheFile(__DIR__ . './cache/php-cs-fixer.json')
    ->setFinder(
        (new Finder())
            ->in(__DIR__),
    );