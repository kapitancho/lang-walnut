<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Document;

use Walnut\Lang\Lsp\Blueprint\Document\DocumentStore;
use Walnut\Lang\Lsp\Blueprint\Document\DocumentStoreFactory;

final readonly class InMemoryDocumentStoreFactory implements DocumentStoreFactory {

    public function create(string $sourceRoot): DocumentStore {
        return new InMemoryDocumentStore($sourceRoot);
    }
}
