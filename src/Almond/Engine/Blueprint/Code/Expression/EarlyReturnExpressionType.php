<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

enum EarlyReturnExpressionType: string {
	case onEmpty = '?!';
	case onError = '@!';
	case onExternalError = '*!';
	case onEmptyAndError = '!';
}