<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Document;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContextScope;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope;

/**
 * Used as the contextScope on snapshots where validation did not run
 * (i.e. a parse failure occurred before the analysis phase).
 */
final readonly class NoopValidationContextScope implements ValidationContextScope {

    public function typeOf(Expression|FunctionBody $expression): Type|null {
        return null;
    }

    public function scopeAt(Expression|FunctionBody $expression): VariableScope|null {
        return null;
    }
}
