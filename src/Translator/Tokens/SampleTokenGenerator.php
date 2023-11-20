<?php

namespace App\Translator\Tokens;

/**
 * A nice interface for providing tokens.
 */
class SampleTokenGenerator implements TokenProviderInterface
{
    /**
     * Generate a fake token just as an example.
     */
    public function generateToken(string $source, string $target, string $text): string
    {
        return sprintf('%d.%d', rand(10000, 99999), rand(10000, 99999));
    }
}
