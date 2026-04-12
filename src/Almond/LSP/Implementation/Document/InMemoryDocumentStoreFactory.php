<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Document;

use Walnut\Lang\Almond\LSP\Blueprint\Document\DocumentStore;
use Walnut\Lang\Almond\LSP\Blueprint\Document\DocumentStoreFactory;

final readonly class InMemoryDocumentStoreFactory implements DocumentStoreFactory {

    public function create(string $sourceRoot): DocumentStore {
        return new InMemoryDocumentStore($sourceRoot);
    }
}
