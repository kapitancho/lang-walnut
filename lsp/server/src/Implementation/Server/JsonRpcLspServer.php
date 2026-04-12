<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Implementation\Server;

use Walnut\Lang\Almond\Runner\Implementation\Compiler;
use Walnut\Lang\Almond\Source\Implementation\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\InMemorySourceFinder;
use Walnut\Lang\Almond\Source\Implementation\SourceFinder\PackageBasedSourceFinder;
use Walnut\Lang\Lsp\Blueprint\Compilation\CompilationIndexFactory;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationCache;
use Walnut\Lang\Lsp\Blueprint\Document\CompilationSnapshot;
use Walnut\Lang\Lsp\Blueprint\Document\DocumentStore;
use Walnut\Lang\Lsp\Blueprint\Document\DocumentStoreFactory;
use Walnut\Lang\Lsp\Blueprint\Feature\CompletionProvider;
use Walnut\Lang\Lsp\Blueprint\Feature\DefinitionProvider;
use Walnut\Lang\Lsp\Blueprint\Feature\DiagnosticsProvider;
use Walnut\Lang\Lsp\Blueprint\Feature\FoldingRangeProvider;
use Walnut\Lang\Lsp\Blueprint\Feature\HoverProvider;
use Walnut\Lang\Lsp\Blueprint\Feature\InlayHintProvider;
use Walnut\Lang\Lsp\Blueprint\Feature\SemanticTokensProvider;
use Walnut\Lang\Lsp\Blueprint\Feature\SignatureHelpProvider;
use Walnut\Lang\Lsp\Blueprint\Server\LspServer;
use Walnut\Lang\Lsp\Blueprint\Transport\LspTransport;
use Walnut\Lang\Lsp\Implementation\Document\BasicCompilationSnapshot;

/**
 * Main LSP server loop.
 *
 * Reads JSON-RPC messages from the transport and dispatches them to the
 * appropriate handler.  All LSP lifecycle, document sync, and feature
 * requests are handled here.
 *
 * The server is wired at the composition root (walnut-lsp entry point).
 * Runtime workspace state (document store, package source finder, compiler
 * with added source finders) is initialised during the initialize handshake
 * because it depends on the workspace URI and nutcfg.json content.
 */
final class JsonRpcLspServer implements LspServer {

    private bool $shutdownCalled = false;

    // ---- Workspace state: set during handleInitialize ----
    private DocumentStore               $documentStore;
    private Compiler                    $compiler;          // base + workspace source finders added
    private PackageBasedSourceFinder|null $packageSourceFinder = null;

    public function __construct(
        private readonly LspTransport            $transport,
        private readonly DiagnosticsProvider     $diagnosticsProvider,
        private readonly HoverProvider           $hoverProvider,
        private readonly DefinitionProvider      $definitionProvider,
        private readonly CompletionProvider      $completionProvider,
        private readonly FoldingRangeProvider    $foldingRangeProvider,
        private readonly InlayHintProvider       $inlayHintProvider,
        private readonly SignatureHelpProvider   $signatureHelpProvider,
        private readonly SemanticTokensProvider  $semanticTokensProvider,
        private readonly CompilationCache        $compilationCache,
        private readonly DocumentStoreFactory    $documentStoreFactory,
        private readonly CompilationIndexFactory $compilationIndexFactory,
        private readonly Compiler                $baseCompiler,
    ) {}

    public function run(): void {
        while (true) {
            $message = $this->transport->receive();
            if ($message === null) {
                break;
            }
            $this->handleMessage($message);
        }
    }

    // -------------------------------------------------------------------------
    // Dispatcher
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $message */
    private function handleMessage(array $message): void {
        $method = $message['method'] ?? null;
        $id     = $message['id']     ?? null;
        $params = $message['params'] ?? [];

        try {
            $result = match ($method) {
                'initialize'                  => $this->handleInitialize($params),
                'initialized'                 => null,
                'shutdown'                    => $this->handleShutdown(),
                'exit'                        => $this->handleExit(),
                'textDocument/didOpen'        => $this->handleDidOpen($params),
                'textDocument/didChange'      => $this->handleDidChange($params),
                'textDocument/didClose'       => $this->handleDidClose($params),
                'textDocument/didSave'        => null,
                'textDocument/hover'          => $this->handleHover($params),
                'textDocument/definition'     => $this->handleDefinition($params),
                'textDocument/completion'     => $this->handleCompletion($params),
                'textDocument/semanticTokens/full' => $this->handleSemanticTokensFull($params),
                'textDocument/foldingRange'        => $this->handleFoldingRange($params),
                'textDocument/inlayHint'      => $this->handleInlayHint($params),
                'textDocument/signatureHelp'  => $this->handleSignatureHelp($params),
                default                       => null,
            };
        } catch (\Throwable $e) {
            fwrite(STDERR, sprintf(
                "[walnut-lsp] ERROR %s %s: %s\n  at %s:%d\n",
                $method ?? '(unknown)',
                $id !== null ? "id=$id" : '(notification)',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
            ));
            fflush(STDERR);
            if ($id !== null) {
                $this->sendError($id, -32603, 'Internal error: ' . $e->getMessage());
            }
            return;
        }

        if ($id !== null) {
            $this->sendResult($id, $result);
        }
    }

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $params */
    private function handleInitialize(array $params): array {
        $rootUri    = $params['rootUri'] ?? null;
        $sourceRoot = $rootUri !== null
            ? rawurldecode(str_replace('file://', '', $rootUri)) . '/walnut-src'
            : getcwd() . '/walnut-src';

        // documentSourceRoot is used for URI→module-name conversion.
        // May be overridden once nutcfg.json has been read.
        $documentSourceRoot = $sourceRoot;

        $this->compiler = $this->baseCompiler;

        $nutcfgPath = $sourceRoot . '/../nutcfg.json';
        if (file_exists($nutcfgPath)) {
            // Build a PackageBasedSourceFinder with ABSOLUTE paths so we never
            // depend on getcwd().  For each package, prefer the almond/ copy
            // (which has JsonValue, updated HydrationError, etc.) when it exists.
            $projectRoot = dirname((string) realpath($nutcfgPath));
            $almondDir   = $projectRoot . '/almond';

            $nutcfg = json_decode((string) file_get_contents($nutcfgPath), true);

            $relSourceRoot = $nutcfg['sourceRoot'] ?? 'walnut-src';
            $absSourceRoot = $projectRoot . '/' . $relSourceRoot;

            // If the source root doesn't exist at the project level but the almond/
            // directory contains it (runtime copy), prefer the almond/ copy.
            // This handles the case where VSCode is opened at the repository root
            // but user Walnut files live in almond/<sourceRoot>/.
            if (!is_dir($absSourceRoot) && is_dir($almondDir . '/' . $relSourceRoot)) {
                $absSourceRoot = $almondDir . '/' . $relSourceRoot;
            }

            // Align the document store root with the resolved source root so that
            // URI→module-name extraction gives the right short names (e.g. "f1", not
            // "Users/…/almond/walnut-src/f1").
            $documentSourceRoot = $absSourceRoot;

            // Remap each package path: prefer almond/<relPath> when it exists.
            $absPackageRoots = [];
            foreach (($nutcfg['packages'] ?? []) as $name => $relPath) {
                $almondPath  = $almondDir   . '/' . $relPath;
                $projectPath = $projectRoot . '/' . $relPath;
                $absPackageRoots[$name] = is_dir($almondPath) ? $almondPath : $projectPath;
            }

            fwrite(STDERR, "[walnut-lsp] source root    $absSourceRoot\n");
            fflush(STDERR);

            // Stored separately — added LAST in recompileAndPublish so in-memory
            // sources take priority over disk when the same module is open.
            $this->packageSourceFinder = new PackageBasedSourceFinder(
                new PackageConfiguration($absSourceRoot, $absPackageRoots)
            );
        }

        $this->documentStore = $this->documentStoreFactory->create($documentSourceRoot);

        return [
            'capabilities' => [
                'textDocumentSync' => [
                    'openClose' => true,
                    'change'    => 1, // Full text sync
                    'save'      => false,
                ],
                'hoverProvider'        => true,
                'definitionProvider'   => true,
                'completionProvider'   => [
                    'triggerCharacters' => ['>'],
                ],
                'semanticTokensProvider' => [
                    'legend' => [
                        'tokenTypes'     => $this->semanticTokensProvider->tokenTypes(),
                        'tokenModifiers' => $this->semanticTokensProvider->tokenModifiers(),
                    ],
                    'full' => true,
                ],
                'foldingRangeProvider' => true,
                'inlayHintProvider'    => true,
                'signatureHelpProvider' => [
                    'triggerCharacters'   => ['('],
                    'retriggerCharacters' => [','],
                ],
            ],
            'serverInfo' => ['name' => 'walnut-lsp', 'version' => '0.1.0'],
        ];
    }

    private function handleShutdown(): null {
        $this->shutdownCalled = true;
        return null;
    }

    private function handleExit(): never {
        exit($this->shutdownCalled ? 0 : 1);
    }

    // -------------------------------------------------------------------------
    // Document sync
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $params */
    private function handleDidOpen(array $params): null {
        $doc     = $params['textDocument'];
        $uri     = $doc['uri'];
        $version = $doc['version'];
        $content = $doc['text'];

        $this->documentStore->update($uri, $version, $content);
        $this->recompileAndPublish($uri, $version, $content);
        return null;
    }

    /** @param array<string, mixed> $params */
    private function handleDidChange(array $params): null {
        $uri     = $params['textDocument']['uri'];
        $version = $params['textDocument']['version'];
        // Full-text sync: last entry holds the complete new text
        $content = array_last($params['contentChanges'])['text'] ?? '';

        $this->documentStore->update($uri, $version, $content);

        // Skip recompiling this version if a newer message is already waiting in
        // the input buffer — avoids queueing multiple compilations during fast typing.
        if ($this->transport->hasMore()) {
            $moduleName = $this->documentStore->uriToModuleName($uri);
            fwrite(STDERR, "[walnut-lsp] skip compile    $moduleName v$version (newer msg queued)\n");
            fflush(STDERR);
            return null;
        }

        $this->recompileAndPublish($uri, $version, $content);
        return null;
    }

    /** @param array<string, mixed> $params */
    private function handleDidClose(array $params): null {
        $uri = $params['textDocument']['uri'];
        $this->documentStore->remove($uri);
        $this->compilationCache->evict($uri);
        $this->transport->send([
            'jsonrpc' => '2.0',
            'method'  => 'textDocument/publishDiagnostics',
            'params'  => ['uri' => $uri, 'diagnostics' => []],
        ]);
        return null;
    }

    // -------------------------------------------------------------------------
    // Feature handlers
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $params */
    private function handleHover(array $params): array|null {
        $snapshot = $this->bestSnapshot($params['textDocument']['uri']);
        if ($snapshot === null) {
            return null;
        }
        return $this->hoverProvider->hover(
            $snapshot,
            $params['position']['line'],
            $params['position']['character'],
        );
    }

    /** @param array<string, mixed> $params */
    private function handleDefinition(array $params): array|null {
        $snapshot = $this->bestSnapshot($params['textDocument']['uri']);
        if ($snapshot === null) {
            return null;
        }
        return $this->definitionProvider->definition(
            $snapshot,
            $params['position']['line'],
            $params['position']['character'],
        );
    }

    /** @param array<string, mixed> $params */
    private function handleCompletion(array $params): array {
        $uri      = $params['textDocument']['uri'];
        $snapshot = $this->bestSnapshot($uri);
        if ($snapshot === null) {
            return [];
        }
        // documentStore is updated immediately on didChange (before recompile),
        // so it holds the editor's current text even when the snapshot is one
        // version behind (e.g. the user just typed '->' which caused a parse error).
        $liveSourceText = $this->documentStore->get($uri) ?? $snapshot->sourceText;

        return $this->completionProvider->completions(
            $snapshot,
            $params['position']['line'],
            $params['position']['character'],
            $params['context']['triggerCharacter'] ?? '',
            $liveSourceText,
        );
    }

    /** @param array<string, mixed> $params */
    private function handleSemanticTokensFull(array $params): array {
        $snapshot = $this->bestSnapshot($params['textDocument']['uri']);
        if ($snapshot === null) {
            return ['data' => []];
        }
        return ['data' => $this->semanticTokensProvider->semanticTokens($snapshot)];
    }

    /** @param array<string, mixed> $params */
    private function handleSignatureHelp(array $params): array|null {
        $snapshot = $this->bestSnapshot($params['textDocument']['uri']);
        if ($snapshot === null) {
            return null;
        }
        return $this->signatureHelpProvider->signatureHelp(
            $snapshot,
            $params['position']['line'],
            $params['position']['character'],
        );
    }

    /** @param array<string, mixed> $params */
    private function handleFoldingRange(array $params): array {
        $snapshot = $this->bestSnapshot($params['textDocument']['uri']);
        if ($snapshot === null) {
            return [];
        }
        return $this->foldingRangeProvider->foldingRanges($snapshot);
    }

    /** @param array<string, mixed> $params */
    private function handleInlayHint(array $params): array {
        $snapshot = $this->bestSnapshot($params['textDocument']['uri']);
        if ($snapshot === null) {
            return [];
        }
        $range = $params['range'] ?? [];
        $startLine = $range['start']['line'] ?? 0;
        $endLine   = $range['end']['line']   ?? PHP_INT_MAX;
        return $this->inlayHintProvider->inlayHints($snapshot, $startLine, $endLine);
    }

    // -------------------------------------------------------------------------
    // Compilation
    // -------------------------------------------------------------------------

    private function recompileAndPublish(string $uri, int $version, string $content): void {
        $moduleName  = $this->documentStore->uriToModuleName($uri);
        $codeIndex   = $this->compilationIndexFactory->createCodeIndex();
        $collector   = $this->compilationIndexFactory->createValidationCollector();

        $t0 = hrtime(true);
        fwrite(STDERR, "[walnut-lsp] compile start  $moduleName v$version\n");
        fflush(STDERR);

        // Source finder order matters: in-memory sources take priority over disk,
        // so the user's unsaved edits are compiled rather than the last saved file.
        // EmptyPrecompiler appends '.nut' to the module name before lookup, so
        // InMemorySourceFinder must be keyed with the '.nut' extension too.
        $compiler = $this->compiler
            ->withAddedSourceFinder(new InMemorySourceFinder([$moduleName . '.nut' => $content]))
            ->withAddedSourceFinder($this->buildPeerSourceFinder($moduleName));

        // Package/disk finder appended last so it only serves files not open in the editor.
        if ($this->packageSourceFinder !== null) {
            $compiler = $compiler->withAddedSourceFinder($this->packageSourceFinder);
        }

        $compilationResult = $compiler
            ->withStartModule($moduleName)
            ->withCodeMapper($codeIndex)
            ->withValidationResultCollector($collector)
            ->compile();

        $ms = intdiv(hrtime(true) - $t0, 1_000_000);
        fwrite(STDERR, "[walnut-lsp] compile done   $moduleName v$version ({$ms}ms)\n");
        fflush(STDERR);

        $snapshot = new BasicCompilationSnapshot(
            uri:               $uri,
            moduleName:        $moduleName,
            version:           $version,
            sourceText:        $content,
            compilationResult: $compilationResult,
            codeIndex:         $codeIndex,
            contextScope:      $collector,
        );
        $this->compilationCache->store($snapshot);

        $diagnostics = $this->diagnosticsProvider->diagnosticsFor($snapshot);
        fwrite(STDERR, "[walnut-lsp] diagnostics    $moduleName v$version (" . count($diagnostics) . " issue(s))\n");
        fflush(STDERR);

        $this->transport->send([
            'jsonrpc' => '2.0',
            'method'  => 'textDocument/publishDiagnostics',
            'params'  => ['uri' => $uri, 'version' => $version, 'diagnostics' => $diagnostics],
        ]);
    }

    /**
     * Build an InMemorySourceFinder containing the best available source text for
     * every open file EXCEPT the one currently being compiled.
     * This allows cross-module type checking to use the most recent valid state.
     */
    private function buildPeerSourceFinder(string $excludeModuleName): InMemorySourceFinder {
        $sources = [];
        foreach ($this->compilationCache->listUris() as $uri) {
            $snapshot = $this->compilationCache->lastValid($uri)
                     ?? $this->compilationCache->lastParsed($uri)
                     ?? $this->compilationCache->live($uri);
            if ($snapshot !== null && $snapshot->moduleName !== $excludeModuleName) {
                // Key with '.nut' extension — EmptyPrecompiler appends it before lookup.
                $sources[$snapshot->moduleName . '.nut'] = $snapshot->sourceText;
            }
        }
        return new InMemorySourceFinder($sources);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function bestSnapshot(string $uri): CompilationSnapshot|null {
        return $this->compilationCache->lastParsed($uri)
            ?? $this->compilationCache->live($uri);
    }

    private function sendResult(int|string $id, mixed $result): void {
        $this->transport->send(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result]);
    }

    private function sendError(int|string $id, int $code, string $message): void {
        $this->transport->send([
            'jsonrpc' => '2.0',
            'id'      => $id,
            'error'   => ['code' => $code, 'message' => $message],
        ]);
    }
}
