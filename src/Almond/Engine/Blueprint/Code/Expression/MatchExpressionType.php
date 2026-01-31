<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

enum MatchExpressionType {
	case isTrue;
	case typeOf;
	case valueOf;
	case if;
}