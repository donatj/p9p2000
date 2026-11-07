<?php

use Corpus\PhpCsFixer\Config;
use PhpCsFixer\Finder;

$config = Config::php8x1()
	->setUnsupportedPhpVersionAllowed(true);

return $config
    ->setRules(array_merge(
        [ '@PHPUnit10x0Migration:risky' => true ],
        $config->getRules()
    ))
    ->setFinder(Finder::create()->in([ __DIR__ . '/src', __DIR__ . '/test' ]));
