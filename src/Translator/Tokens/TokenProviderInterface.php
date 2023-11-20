<?php

namespace App\Translator\Tokens;

/**
 * A nice interface for providing tokens.
 */
interface TokenProviderInterface
{
    /**
     * Generate and return a token.
     */
    public function generateToken(string $source, string $target, string $text): string;
}
