<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Document;

use Walnut\Lang\Lsp\Blueprint\Document\CompilationCache;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;

/**
 * Three-slot cache per URI: live, lastParsed, lastValid.
 *
 * @see CompilationCache
 */
final class TwoLevelCompilationCache implements CompilationCache {

    /** @var array<string, CompilationSnapshot> */
    private array $live = [];

    /** @var array<string, CompilationSnapshot> */
    private array $lastParsed = [];

    /** @var array<string, CompilationSnapshot> */
    private array $lastValid = [];

    public function store(CompilationSnapshot $snapshot): void {
        $uri = $snapshot->uri;
        $this->live[$uri] = $snapshot;
        if ($snapshot->isParsed) {
            $this->lastParsed[$uri] = $snapshot;
        }
        if ($snapshot->isValid) {
            $this->lastValid[$uri] = $snapshot;
        }
    }

    public function evict(string $uri): void {
        unset($this->live[$uri], $this->lastParsed[$uri], $this->lastValid[$uri]);
    }

    public function live(string $uri): CompilationSnapshot|null {
        return $this->live[$uri] ?? null;
    }

    public function lastParsed(string $uri): CompilationSnapshot|null {
        return $this->lastParsed[$uri] ?? null;
    }

    public function lastValid(string $uri): CompilationSnapshot|null {
        return $this->lastValid[$uri] ?? null;
    }

    public function bestSourceFor(string $moduleName): string|null {
        foreach ([$this->lastValid, $this->lastParsed, $this->live] as $tier) {
            $found = array_find(
                $tier,
                fn(CompilationSnapshot $s): bool => $s->moduleName === $moduleName
            );
            if ($found !== null) {
                return $found->sourceText;
            }
        }
        return null;
    }

    public function listUris(): array {
        return array_values(array_unique([
            ...array_keys($this->live),
            ...array_keys($this->lastParsed),
            ...array_keys($this->lastValid),
        ]));
    }
}
