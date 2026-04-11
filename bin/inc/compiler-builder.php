<?php

/**
 * Builds a Compiler instance configured from the nearest nutcfg.json.
 *
 * Search order:
 *   1. <cwd>/nutcfg.json          — project the user is running from
 *   2. <bin>/../almond/nutcfg.json — bundled fallback
 *
 * All paths are resolved to absolute so the scripts are cwd-independent.
 *
 * @return Walnut\Lang\Almond\Runner\Implementation\Compiler
 */

use Walnut\Lang\Almond\Runner\Implementation\Compiler;
use Walnut\Lang\Almond\Source\Implementation\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\PackageBasedSourceFinder;

function buildCompilerFromNutcfg(): Compiler {
    // Candidate locations for nutcfg.json, in priority order.
    $candidates = [
        getcwd() . '/nutcfg.json',
        __DIR__ . '/../../almond/nutcfg.json',
    ];

    foreach ($candidates as $nutcfgPath) {
        if (!file_exists($nutcfgPath)) {
            continue;
        }

        $data = json_decode((string) file_get_contents($nutcfgPath), true);
        if (!is_array($data) || !isset($data['sourceRoot'])) {
            continue;
        }

        // Resolve all paths relative to the directory that contains nutcfg.json.
        $configDir  = dirname((string) realpath($nutcfgPath));
        $sourceRoot = $configDir . '/' . $data['sourceRoot'];

        $packageRoots = [];
        foreach (($data['packages'] ?? []) as $name => $relPath) {
            $packageRoots[$name] = $configDir . '/' . $relPath;
        }

        return Compiler::builder()->withAddedSourceFinder(
            new PackageBasedSourceFinder(
                new PackageConfiguration($sourceRoot, $packageRoots)
            )
        );
    }

    // Should never reach here if the bundled fallback exists.
    throw new RuntimeException('No nutcfg.json found. Run the command from a Walnut project folder.');
}

/**
 * Returns the absolute path to the Walnut source root declared in the
 * nearest nutcfg.json (same search order as buildCompilerFromNutcfg).
 */
function sourceRootFromNutcfg(): string {
    $candidates = [
        getcwd() . '/nutcfg.json',
        __DIR__ . '/../../almond/nutcfg.json',
    ];

    foreach ($candidates as $nutcfgPath) {
        if (!file_exists($nutcfgPath)) {
            continue;
        }
        $data = json_decode((string) file_get_contents($nutcfgPath), true);
        if (!is_array($data) || !isset($data['sourceRoot'])) {
            continue;
        }
        $configDir = dirname((string) realpath($nutcfgPath));
        return $configDir . '/' . $data['sourceRoot'];
    }

    throw new RuntimeException('No nutcfg.json found. Run the command from a Walnut project folder.');
}
