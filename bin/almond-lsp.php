#!/usr/bin/env php
<?php
require __DIR__ . '/inc/autoload.php';

// PHP errors must never reach stdout — it is reserved for JSON-RPC messages.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', 'php://stderr');

use Walnut\Lang\Almond\Runner\Implementation\Compiler;
use Walnut\Lang\Almond\LSP\Implementation\Compilation\DefaultCompilationIndexFactory;
use Walnut\Lang\Almond\LSP\Implementation\Document\InMemoryDocumentStoreFactory;
use Walnut\Lang\Almond\LSP\Implementation\Document\TwoLevelCompilationCache;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondCompletionProvider;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondDefinitionProvider;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondDiagnosticsProvider;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondFoldingRangeProvider;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondHoverProvider;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondInlayHintProvider;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondSemanticTokensProvider;
use Walnut\Lang\Almond\LSP\Implementation\Feature\AlmondSignatureHelpProvider;
use Walnut\Lang\Almond\LSP\Implementation\Server\JsonRpcLspServer;
use Walnut\Lang\Almond\LSP\Implementation\Support\LspPositionConverter;
use Walnut\Lang\Almond\LSP\Implementation\Transport\StdioTransport;

$converter = new LspPositionConverter();

$server = new JsonRpcLspServer(
    transport:               new StdioTransport(),
    diagnosticsProvider:     new AlmondDiagnosticsProvider($converter),
    hoverProvider:           new AlmondHoverProvider($converter),
    definitionProvider:      new AlmondDefinitionProvider($converter),
    completionProvider:      new AlmondCompletionProvider($converter),
    foldingRangeProvider:    new AlmondFoldingRangeProvider(),
    inlayHintProvider:       new AlmondInlayHintProvider($converter),
    signatureHelpProvider:   new AlmondSignatureHelpProvider($converter),
    semanticTokensProvider:  new AlmondSemanticTokensProvider(),
    compilationCache:        new TwoLevelCompilationCache(),
    documentStoreFactory:    new InMemoryDocumentStoreFactory(),
    compilationIndexFactory: new DefaultCompilationIndexFactory(),
    baseCompiler:            Compiler::builder(),
);

$server->run();
