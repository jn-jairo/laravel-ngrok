<?php

$finder = PhpCsFixer\Finder::create()->in([
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);

return (new PhpCsFixer\Config())->setRules([
    'no_unused_imports' => true,
])->setFinder($finder);
