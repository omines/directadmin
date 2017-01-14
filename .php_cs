<?php

$header = <<<EOF
DirectAdmin API Client
(c) Omines Internetbureau B.V. - https://omines.nl/

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'header_comment' => ['header' => $header],

        'blank_line_before_return' => false,
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'phpdoc_var_without_name' => false,
    ])
    ->setFinder($finder)
    ;


/*
use Symfony\CS\AbstractFixer;
use Symfony\CS\DocBlock\DocBlock;
use Symfony\CS\Tokenizer\Tokens;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader(<<<EOF
DirectAdmin API Client
(c) Omines Internetbureau B.V. - https://omines.nl/

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF
);

return Symfony\CS\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        '-phpdoc_params',
        '-phpdoc_separation',
        '-phpdoc_var_without_name',
        '-return',
        'concat_with_spaces',
        'header_comment',
        'newline_after_open_tag',
        'short_array_syntax',
        'strict_param',
    ])
    ->finder(
        Symfony\CS\Finder::create()
            ->files()
            ->name('*.php')
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    )
    ;*/