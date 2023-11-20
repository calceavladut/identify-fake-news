<?php

namespace App\Translator;

use ErrorException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Translator\Tokens\GoogleTokenGenerator;
use App\Translator\Tokens\TokenProviderInterface;
use UnexpectedValueException;

/**
 * Free Google Translate API PHP Package.
 *
 * @author      Levan Velijanashvili <me@stichoza.com>
 * @link        http://stichoza.com/
 * @license     MIT
 */
class GoogleTranslate
{
    /**
     * @var \GuzzleHttp\Client HTTP Client
     */
    protected $client;

    /**
     * @var string|null Source language - from where the string should be translated
     */
    protected ?string $source = null;

    /**
     * @var string Target language - to which language string should be translated
     */
    protected string $target;

    /**
     * @var string|null Last detected source language
     */
    protected ?string $lastDetectedSource = null;

    /**
     * @var string Google Translate URL base
     */
    protected string $url = 'https://translate.google.com/translate_a/single';

    /**
     * @var array Dynamic GuzzleHttp client options
     */
    protected array $options = [];

    /**
     * @var array URL Parameters
     */
    protected array $urlParams = [
        'client' => 'gtx',
        'hl' => 'en',
        'dt' => [
            't',   // Translate
            'bd',  // Full translate with synonym ($bodyArray[1])
            'at',  // Other translate ($bodyArray[5] - in google translate page this shows when click on translated word)
            'ex',  // Example part ($bodyArray[13])
            'ld',  // I don't know ($bodyArray[8])
            'md',  // Definition part with example ($bodyArray[12])
            'qca', // I don't know ($bodyArray[8])
            'rw',  // Read also part ($bodyArray[14])
            'rm',  // I don't know
            'ss'   // Full synonym ($bodyArray[11])
        ],
        'sl' => null, // Source language
        'tl' => null, // Target language
        'q' => null, // String to translate
        'ie' => 'UTF-8', // Input encoding
        'oe' => 'UTF-8', // Output encoding
        'multires' => 1,
        'otf' => 0,
        'pc' => 1,
        'trs' => 1,
        'ssel' => 0,
        'tsel' => 0,
        'kc' => 1,
        'tk' => null,
    ];

    /**
     * @var array Regex key-value patterns to replace on response data
     */
    protected array $resultRegexes = [
        '/,+/' => ',',
        '/\[,/' => '[',
    ];

    /**
     * @var TokenProviderInterface Token provider
     */
    protected TokenProviderInterface $tokenProvider;

    /**
     * Class constructor.
     *
     * For more information about HTTP client configuration options, see "Request Options" in
     * GuzzleHttp docs: http://docs.guzzlephp.org/en/stable/request-options.html
     *
     * @param string $target Target language
     * @param string|null $source Source language
     * @param array|null $options Associative array of http client configuration options
     * @param TokenProviderInterface|null $tokenProvider
     */
    public function __construct(
        string $target = 'en',
        string $source = null,
        array $options = null,
        TokenProviderInterface $tokenProvider = null
    ) {
        $this->client = new Client();
        $this->setTokenProvider($tokenProvider ?? new GoogleTokenGenerator)
            ->setOptions($options) // Options are already set in client constructor tho.
            ->setSource($source)
            ->setTarget($target);
    }

    /**
     * Set target language for translation.
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Set source language for translation.
     */
    public function setSource(string $source = null): self
    {
        $this->source = $source ?? 'auto';

        return $this;
    }

    /**
     * Set Google Translate URL base.
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set Google Translate client param (webapp, gtx, etc.)
     */
    public function setClient(string $client): self
    {
        $this->urlParams['client'] = $client;

        return $this;
    }

    /**
     * Set GuzzleHttp client options.
     */
    public function setOptions(array $options = null): self
    {
        $this->options = $options ?? [];

        return $this;
    }

    /**
     * Set token provider.
     */
    public function setTokenProvider(TokenProviderInterface $tokenProvider): self
    {
        $this->tokenProvider = $tokenProvider;

        return $this;
    }

    /**
     * Get last detected source language.
     */
    public function getLastDetectedSource(): ?string
    {
        return $this->lastDetectedSource;
    }

    /**
     * Override translate method for static call.
     *
     * @throws ErrorException If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public static function trans(
        string $string,
        string $target = 'en',
        string $source = null,
        array $options = [],
        TokenProviderInterface $tokenProvider = null
    ): string {
        return (new self)
            ->setTokenProvider($tokenProvider ?? new GoogleTokenGenerator)
            ->setOptions($options) // Options are already set in client constructor tho.
            ->setSource($source)
            ->setTarget($target)
            ->translate($string);
    }

    /**
     * Translate text.
     *
     * This can be called from instance method translate() using __call() magic method.
     * Use $instance->translate($string) instead.
     *
     * @throws ErrorException           If the HTTP request fails
     * @throws UnexpectedValueException If received data cannot be decoded
     */
    public function translate(string $string): string
    {
        /*
         * if source lang and target lang are the same
         * just return the string without any request to google
         */
        if ($this->source == $this->target) return $string;

        $responseArray = $this->getResponse($string);

        /*
         * if response in text and the content has zero the empty returns true, lets check
         * if response is string and not empty and create array for further logic
         */
        if (is_string($responseArray) && $responseArray != '') {
            $responseArray = [$responseArray];
        }

        // Check if translation exists
        if (!isset($responseArray[0]) || empty($responseArray[0])) {
            return 0;
        }

        // Detect languages
        $detectedLanguages = [];

        // the response contains only single translation, don't create loop that will end with
        // invalid foreach and warning
        if (!is_string($responseArray)) {
            foreach ($responseArray as $item) {
                if (is_string($item)) {
                    $detectedLanguages[] = $item;
                }
            }
        }

        // Another case of detected language
        if (isset($responseArray[count($responseArray) - 2][0][0])) {
            $detectedLanguages[] = $responseArray[count($responseArray) - 2][0][0];
        }

        // Set initial detected language to null
        $this->lastDetectedSource = null;

        // Iterate and set last detected language
        foreach ($detectedLanguages as $lang) {
            if ($this->isValidLocale($lang)) {
                $this->lastDetectedSource = $lang;
                break;
            }
        }

        // the response can be sometimes an translated string.
        if (is_string($responseArray)) {
            return $responseArray;
        } else {
            if (is_array($responseArray[0])) {
                return (string)array_reduce($responseArray[0], function ($carry, $item) {
                    $carry .= $item[0];
                    return $carry;
                });
            } else {
                return (string)$responseArray[0];
            }
        }
    }

    /**
     * Get response array.
     *
     * @throws UnexpectedValueException If received data cannot be decoded
     * @throws ErrorException           If the HTTP request fails
     */
    public function getResponse(string $string): array
    {
        $queryArray = array_merge($this->urlParams, [
            'sl' => $this->source,
            'tl' => $this->target,
            'tk' => $this->tokenProvider->generateToken($this->source, $this->target, $string),
            'q' => $string
        ]);

        $queryUrl = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($queryArray));

        try {
            $response = $this->client->get($this->url, [
                    'query' => $queryUrl,
                ] + $this->options);
        } catch (RequestException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }

        $body = $response->getBody(); // Get response body

        // Modify body to avoid json errors
        $bodyJson = preg_replace(array_keys($this->resultRegexes), array_values($this->resultRegexes), $body);

        // Decode JSON data
        if (($bodyArray = json_decode($bodyJson, true)) === null) {
            throw new UnexpectedValueException('Data cannot be decoded or it is deeper than the recursion limit');
        }

        return $bodyArray;
    }

    /**
     * Check if given locale is valid.
     */
    protected function isValidLocale(string $lang): bool
    {
        return (bool)preg_match('/^([a-z]{2})(-[A-Z]{2})?$/', $lang);
    }
}
