<?php

namespace Walnut\Lang\Almond\Source\Implementation\Precompiler;

use Walnut\Lang\Almond\Source\Blueprint\Precompiler\CodePrecompiler;

final readonly class TemplatePrecompiler implements CodePrecompiler {
	private const array escapeOriginalChars = ['\\', "\n", "\t", "'"];
	private const array escapedChars = ['\\\\', '\n', '\t', '\`'];

	public function determineSourcePath(string $sourcePath): string {
		return $sourcePath . '.nut.html';
	}

	private function escape(string $value): string {
		return
			"'" .
			str_replace(self::escapeOriginalChars, self::escapedChars, $value) .
			"'";
	}

	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
		$sourceCode = preg_replace('^<!-- (.*?) %% (.*?) -->^', <<<CODE
		module $moduleName %% \$tpl, $2:
			
		$1 ==> Template @ UnableToRenderTemplate %% [~TemplateRenderer] :: {
			output = Template(mutable{String, ''});
			e = ^String :: output->value->APPEND(#);
			h = ^String :: output->value->APPEND(#->htmlEscape);
			-->
								
		CODE, $sourceCode);
		$sourceCode .= <<<CODE
		    <!--
		    output
		};
		CODE;
		$sourceCode = (string)preg_replace('/<!--\[(.*?)]-->/s', "<!-- e({ $1 }->asString);\n -->", $sourceCode);
		$sourceCode = (string)preg_replace('/<!--\{(.*?)}-->/s', "<!-- h({ $1 }->asString);\n -->", $sourceCode);
		$sourceCode = (string)preg_replace('/<!--%(.*?)%-->/s', "<!-- e(%templateRenderer->render($1)?);\n -->", $sourceCode);
		return (string)preg_replace_callback('/-->(.*?)<!--/s', function($matches) {
			$modifiedString = $this->escape($matches[1]);
			return "e($modifiedString);\n";
		}, $sourceCode);
	}
}