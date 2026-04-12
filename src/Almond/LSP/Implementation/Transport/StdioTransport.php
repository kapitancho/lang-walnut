<?php

declare(strict_types=1);

namespace Walnut\Lang\Almond\LSP\Implementation\Transport;

use RuntimeException;
use Walnut\Lang\Almond\LSP\Blueprint\Transport\LspTransport;

/**
 * LSP transport over stdin / stdout (the standard for local servers).
 *
 * Wire format (same as HTTP headers):
 *   Content-Length: <n>\r\n
 *   \r\n
 *   <n bytes of UTF-8 encoded JSON>
 */
final class StdioTransport implements LspTransport {

    public function receive(): array|null {
        $contentLength = null;

        // Read headers
        while (true) {
            $line = fgets(STDIN);
            if ($line === false) {
                fwrite(STDERR, "[walnut-lsp] stdin EOF (stream closed)\n");
                fflush(STDERR);
                return null; // stream closed
            }
            $line = rtrim($line);
            if ($line === '') {
                break; // end of headers
            }
            if (stripos($line, 'Content-Length:') === 0) {
                $contentLength = (int) trim(substr($line, 15));
            }
        }

        if ($contentLength === null || $contentLength <= 0) {
            return null;
        }

        $body = '';
        $remaining = $contentLength;
        while ($remaining > 0) {
            $chunk = fread(STDIN, $remaining);
            if ($chunk === false || $chunk === '') {
                return null;
            }
            $body .= $chunk;
            $remaining -= strlen($chunk);
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function send(array $message): void {
        $body = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ?: throw new RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        $length = strlen($body);
        fwrite(STDOUT, "Content-Length: {$length}\r\n\r\n{$body}");
        fflush(STDOUT);
    }

    public function hasMore(): bool {
        $read   = [STDIN];
        $write  = null;
        $except = null;
        // Zero timeout: purely non-blocking peek at the stream buffer.
        return (bool) stream_select($read, $write, $except, 0, 0);
    }
}
