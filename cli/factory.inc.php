<?php

use Walnut\Lang\Implementation\Program\EntryPoint\Cli\CliEntryPointFactory;

$cfg = @json_decode(@file_get_contents('nutcfg.json') ?? '{}', true);
$sourceRoot = $cfg['sourceRoot'] ?? __DIR__ . '/../walnut-src';
$packages = $cfg['packages'] ?? ['core' => __DIR__ . '/../core-nut-lib'];

return new CliEntryPointFactory($sourceRoot, $packages);