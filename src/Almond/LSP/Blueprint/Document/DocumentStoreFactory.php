<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Blueprint\Document;

/**
 * Creates a DocumentStore once the source root is known.
 *
 * The source root is only available after the LSP initialize handshake
 * (it is derived from the workspace URI and nutcfg.json), so the store
 * cannot be constructed until that point.
 */
interface DocumentStoreFactory {

    /**
     * @param string $sourceRoot Absolute path to the Walnut source directory (no trailing slash).
     */
    public function create(string $sourceRoot): DocumentStore;
}
