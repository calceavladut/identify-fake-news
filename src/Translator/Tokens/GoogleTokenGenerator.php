<?php

namespace App\Translator\Tokens;

/**
 * Google Token Generator.
 *
 * Thanks to @helen5106 and @tehmaestro and few other cool guys
 * at https://github.com/Stichoza/google-translate-php/issues/32
 */
class GoogleTokenGenerator implements TokenProviderInterface
{
    /**
     * Generate and return a token.
     */
    public function generateToken(string $source, string $target, string $text): string
    {
        return $this->TL($text);
    }

    /**
     * Generate a valid Google Translate request token.
     */
    private function TL(string $a): string
    {
        $tkk = $this->TKK();
        $b = $tkk[0];

        for ($d = [], $e = 0, $f = 0; $f < $this->JS_length($a); $f++) {
            $g = $this->JS_charCodeAt($a, $f);
            if (128 > $g) {
                $d[$e++] = $g;
            } else {
                if (2048 > $g) {
                    $d[$e++] = $g >> 6 | 192;
                } else {
                    if (55296 == ($g & 64512) && $f + 1 < $this->JS_length($a) && 56320 == ($this->JS_charCodeAt($a, $f + 1) & 64512)) {
                        $g = 65536 + (($g & 1023) << 10) + ($this->JS_charCodeAt($a, ++$f) & 1023);
                        $d[$e++] = $g >> 18 | 240;
                        $d[$e++] = $g >> 12 & 63 | 128;
                    } else {
                        $d[$e++] = $g >> 12 | 224;
                    }
                    $d[$e++] = $g >> 6 & 63 | 128;
                }
                $d[$e++] = $g & 63 | 128;
            }
        }
        $a = $b;
        for ($e = 0; $e < count($d); $e++) {
            $a += $d[$e];
            $a = $this->RL($a, '+-a^+6');
        }
        $a = $this->RL($a, '+-3^+b+-f');
        $a ^= $tkk[1] ? $tkk[1] + 0 : 0;
        if (0 > $a) {
            $a = ($a & 2147483647) + 2147483648;
        }
        $a = fmod($a, pow(10, 6));

        return $a . '.' . ($a ^ $b);
    }

    private function TKK(): array
    {
        return ['406398', (561666268 + 1526272306)];
    }

    /**
     * Process token data by applying multiple operations.
     * (Params are safe, no need for multibyte functions)
     */
    private function RL($a, $b): int
    {
        for ($c = 0; $c < strlen($b) - 2; $c += 3) {
            $d = $b[$c + 2];
            $d = 'a' <= $d ? ord($d[0]) - 87 : intval($d);
            $d = '+' == $b[$c + 1] ? $this->unsignedRightShift($a, $d) : $a << $d;
            $a = '+' == $b[$c] ? ($a + $d & 4294967295) : $a ^ $d;
        }

        return $a;
    }

    /**
     * Unsigned right shift implementation
     * https://msdn.microsoft.com/en-us/library/342xfs5s(v=vs.94).aspx
     * http://stackoverflow.com/a/43359819/2953830
     */
    private function unsignedRightShift($a, $b): float|int
    {
        if ($b >= 32 || $b < -32) {
            $m = (int)($b / 32);
            $b = $b - ($m * 32);
        }

        if ($b < 0) {
            $b = 32 + $b;
        }

        if ($b == 0) {
            return (($a >> 1) & 0x7fffffff) * 2 + (($a >> $b) & 1);
        }

        if ($a < 0) {
            $a = ($a >> 1);
            $a &= 2147483647;
            $a |= 0x40000000;
            $a = ($a >> ($b - 1));
        } else {
            $a = ($a >> $b);
        }

        return $a;
    }

    /**
     * Get JS charCodeAt equivalent result with UTF-16 encoding.
     */
    private function JS_charCodeAt($str, $index): int
    {
        $utf16 = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');

        return ord($utf16[$index * 2]) + (ord($utf16[$index * 2 + 1]) << 8);
    }

    /**
     * Get JS equivalent string length with UTF-16 encoding.
     */
    private function JS_length($str): float|int
    {
        $utf16 = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');

        return strlen($utf16) / 2;
    }
}
