<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Expression;

enum MatchExpressionType {
	case isTrue;
	case typeOf;
	case valueOf;
	case if;
}