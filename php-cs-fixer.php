<?php

$finder = PhpCsFixer\Finder::create()->in([
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);

return (new PhpCsFixer\Config())->setRules([
    'no_unused_imports' => true,
    'trailing_comma_in_multiline' => [
        'after_heredoc' => true,
        'elements' => [
            'arguments',
            'arrays',
            'match',
            'parameters',
        ],
    ],
])->setFinder($finder);
