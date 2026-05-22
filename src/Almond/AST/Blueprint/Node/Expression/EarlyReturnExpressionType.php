<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Expression;

enum EarlyReturnExpressionType: string {
	case onEmpty = '?!';
	case onError = '@!';
	case onExternalError = '*!';
	case onEmptyAndError = '!';
}