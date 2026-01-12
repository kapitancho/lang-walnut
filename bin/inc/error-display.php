<?php

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lib\Walex\SourcePosition;

function displayErrorWithPointer(
    string $errorMessage,
    ?string $moduleName,
    SourcePosition|SourceLocation|array|null $location,
    string $sourceRoot,
    string $sourceFilePath
): void {
    if (is_array($location) && count($location) > 0) {
        // Extract the target module from the error message
        $targetModule = null;
        if (preg_match('/^Module (?:not found|dependency loop detected): ([^,]+)/', $errorMessage, $matches)) {
            $targetModule = $matches[1];
        }

        // Build the full path: $location array + target module
        $fullPath = $location;
        if ($targetModule !== null) {
            // Always append the target module to complete the chain
            $fullPath[] = $targetModule;
        }

        // Check if it's a loop by seeing if target module is already in the path
        $isLoop = $targetModule !== null && in_array($targetModule, $location, true);

        // Display simplified error message
        echo "\033[0;31m✗ ERROR\033[0m", PHP_EOL;
        if ($isLoop) {
            echo "\033[0;34mDependency loop detected\033[0m", PHP_EOL;
        } else {
            echo "\033[0;34mModule not found\033[0m", PHP_EOL;
        }

        echo "\033[0;90m  Dependency path:\033[0m", PHP_EOL;

        foreach ($fullPath as $index => $modulePath) {
            $isFirst = $index === 0;
            $isLast = $index === count($fullPath) - 1;

            // Determine the indentation and connector
            if ($isFirst) {
                // First item has no prefix, just indentation
                echo "  ";
            } else {
                // Calculate indentation: first connector at 3 spaces, subsequent at +6 spaces each
                $indent = $index === 1 ? 3 : (3 + 6 * ($index - 1));
                echo "  ", str_repeat(' ', $indent), "\033[0;90m└──\033[0m ";
            }

            // Color the module name based on its status
            if ($isLast && $isLoop) {
                // Loop detected - show in orange/yellow
                echo "\033[0;33m", $modulePath, "\033[0m (LOOP)";
            } elseif ($isLast && !$isLoop) {
                // Not found - show in red
                echo "\033[0;31m", $modulePath, "\033[0m (NOT FOUND)";
            } elseif ($isFirst) {
                echo "\033[0;33m", $modulePath, "\033[0m";
            } else {
                // Normal module - show in white
                echo $modulePath;
            }

            echo PHP_EOL;
        }

        echo PHP_EOL;
        return;
    }

    echo "\033[0;31m✗ ERROR\033[0m", PHP_EOL;
    echo "\033[0;34m", $errorMessage, "\033[0m";

    if ($moduleName) {
        echo " in module \033[1;33m", $moduleName, "\033[0m";
    }

    echo PHP_EOL;

    $pos = match(true) {
        $location instanceof SourcePosition => $location,
        $location instanceof SourceLocation => $location->startPosition,
        default => null
    };

    if ($pos) {
        $sourceFile = $sourceRoot . '/' . $sourceFilePath . '.nut';
        if (file_exists($sourceFile)) {
            $sourceCode = file_get_contents($sourceFile);
            $lines = explode("\n", $sourceCode);

            // Display the error line with pointer
            if ($pos->line >= 1 && $pos->line <= count($lines)) {
                $errorLine = $lines[$pos->line - 1];
                $lineNumber = $pos->line;
                $column = $pos->column;

                echo "\033[0;90m", str_pad($lineNumber, 4, ' ', STR_PAD_LEFT), " | \033[0m", $errorLine, PHP_EOL;

                $pointerPrefix = "     | ";
                $pointer = str_repeat('-', $column - 1) . '^';
                echo "\033[0;90m", $pointerPrefix, "\033[0;31m", $pointer, "\033[0m", PHP_EOL;
            }
        }

        echo "\033[0;90m  at ", $pos, "\033[0m", PHP_EOL;
    }

    echo PHP_EOL;
}

function displayErrorsWithPointers(
	array $errorEntries,
	string $sourceRoot,
	string $sourceFilePath
): void {
	foreach ($errorEntries as $errorEntry) {
		displayErrorWithPointer(
			$errorEntry->errorMessage,
			$errorEntry->moduleName,
			$errorEntry->location,
			$sourceRoot,
			$sourceFilePath
		);
	}
}