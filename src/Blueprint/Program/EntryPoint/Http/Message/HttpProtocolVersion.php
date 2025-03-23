<?php

namespace Walnut\Lang\Blueprint\Program\EntryPoint\Http\Message;

enum HttpProtocolVersion: string {
	case http_1_0 = 'HTTP/1.0';
	case http_1_1 = 'HTTP/1.1';
	case http_2 = 'HTTP/2.0';
	case http_3 = 'HTTP/3.0';

	public static function fromName(string $name): self {
		foreach(self::cases() as $case) {
			if($case->name === $name) {
				return $case;
			}
		}
		return self::http_1_1;
	}
}