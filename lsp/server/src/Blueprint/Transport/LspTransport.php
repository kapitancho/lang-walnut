<?php

declare(strict_types=1);

namespace Walnut\Lang\Lsp\Blueprint\Transport;

/**
 * Low-level framing for the LSP JSON-RPC wire protocol.
 *
 * The Language Server Protocol uses HTTP-like headers followed by a JSON body:
 *   Content-Length: <n>\r\n
 *   \r\n
 *   <n bytes of UTF-8 JSON>
 */
interface LspTransport {

    /**
     * Block until a complete JSON-RPC message is available, then return it
     * decoded as an associative array. Returns null when the stream closes.
     *
     * @return array<string, mixed>|null
     */
    public function receive(): array|null;

    /**
     * Encode $message as JSON and write it with the appropriate LSP framing.
     *
     * @param array<string, mixed> $message
     */
    public function send(array $message): void;

    /**
     * Return true if at least one more byte is immediately available on the
     * incoming stream (non-blocking check, no wait).
     *
     * Used by the server to skip recompiling a stale document version when
     * a newer didChange message is already waiting in the buffer.
     */
    public function hasMore(): bool;
}
