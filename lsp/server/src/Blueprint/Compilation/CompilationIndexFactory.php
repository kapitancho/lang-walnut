<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Compilation;

use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResultCollector;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\PositionalLocator;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;

/**
 * Creates fresh per-compilation indices.
 *
 * A new code index and validation collector must be created for every
 * compilation run so that previous results do not bleed into the new one.
 */
interface CompilationIndexFactory {

    /**
     * Create a fresh code mapper / positional index for one compilation.
     * The returned object must satisfy all three intersection roles:
     * CodeMapper (write), SourceNodeLocator (forward lookup), PositionalLocator (offset lookup).
     *
     * @return CodeMapper&SourceNodeLocator&PositionalLocator
     */
    public function createCodeIndex(): CodeMapper&SourceNodeLocator&PositionalLocator;

    /**
     * Create a fresh validation-result collector for one compilation.
     */
    public function createValidationCollector(): ValidationResultCollector;
}
