<?php

namespace Netflex\Scraper;

use DOMXPath;
use DOMDocument;

use Exception;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

use Netflex\Scraper\Metadata;

class Scraper
{
    /** @var Client */
    private $client;

    /** @var array */
    private $options = [
        'allow_redirects' => true,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36'
        ]
    ];

    /**
     * @param null|array $options 
     */
    public function __construct(?array $options = null)
    {
        if ($options) {
            $this->options = $options;
        }

        $this->client = new Client($this->options);
    }

    /**
     * @param string $url 
     * @return Metadata|bool
     */
    public function scrape(string $url)
    {
        $attributes = [
            'site' => null,
            'title' => null,
            'image' => null,
            'description' => null,
            'keywords' => [],
            'canonical' => null,
            'icon' => null,
            'author' => null,
            'copyright' => null,
            'amphtml' => null,
            'language' => null,
            'scraped' => date(DATE_ISO8601, time())
        ];

        try {

            $response = $this->client->get($url);
            $content = (string) $response->getBody();

            if (!json_encode($content)) {
                $content = utf8_encode($content);
            }

            $document = new DOMDocument();
            @$document->loadHTML($content);

            if ($html = $document->getElementsByTagName('html')[0] ?? null) {
                if ($html->hasAttribute('lang')) {
                    $attributes['language'] = $html->getAttribute('lang');
                }
            }

            if ($title = $document->getElementsByTagName('title')[0] ?? null) {
                $attributes['title'] = $title->textContent;
            };

            if ($image = $document->getElementsByTagName('img')[0] ?? null) {
                if ($image->hasAttribute('src')) {
                    $attributes['image'] = $image->getAttribute('src');
                }
            }

            foreach (['h1', 'h2', 'h3', 'p'] as $tag) {
                if (!isset($attributes['description']) || empty($attributes['description']) || (isset($attributes['title']) && $attributes['description'] == $attributes['title'])) {
                    if ($header = $document->getElementsByTagName($tag)[0] ?? null) {
                        $attributes['description'] = trim(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', html_entity_decode(trim(str_replace(["\n", "\r"], '', $header->textContent)))));
                        if (strlen($attributes['description']) && $attributes['description'] !== $attributes['title']) {
                            break;
                        }
                    }
                }
            }

            foreach ($document->getElementsByTagName('meta') as $node) {
                if ($node->hasAttribute('name')) {
                    switch ($node->getAttribute('name')) {
                        case 'description':
                            if (strlen(trim($node->getAttribute('content'))) > 0) {
                                $attributes['description'] = $node->getAttribute('content');
                            }
                            break;
                        case 'keywords':
                            if (strlen(trim($node->getAttribute('keywords'))) > 0) {
                                $keywords = explode(',', $node->getAttribute('keywords'));
                                $attributes['keywords'] = array_map(function ($keyword) {
                                    return trim($keyword);
                                }, $keywords);
                            }
                            break;
                        case 'author':
                            if (strlen(trim($node->getAttribute('content'))) > 0) {
                                $attributes['author'] = $node->getAttribute('content');
                            }
                            break;
                        case 'copyright':
                            if (strlen(trim($node->getAttribute('content'))) > 0) {
                                $attributes['copyright'] = $node->getAttribute('content');
                            }
                            break;
                        default:
                            switch ($node->getAttribute('property')) {
                                case 'og:image':
                                    $attributes['image'] = (object)['url' => $node->getAttribute('content')];
                                    break;
                                case 'og:description':
                                    $description = trim($node->getAttribute('content'));
                                    if ($description) {
                                        $attributes['description'] = $description;
                                    }
                                    break;
                                case 'og:title':
                                    $title = trim(str_replace(["\n", "\r"], '', $node->getAttribute('content')));
                                    if ($title) {
                                        $attributes['title'] = $title;
                                    }
                                    break;
                            }
                    }
                }
            }

            foreach ($document->getElementsByTagName('link') as $node) {
                if ($node->hasAttribute('rel') && $node->hasAttribute('href')) {
                    switch ($node->getAttribute('rel')) {
                        case 'icon':
                        case 'apple-touch-icon':
                            $attributes['icon'] = $node->getAttribute('href');
                            break;
                        case 'canonical':
                            $attributes['canonical'] = $node->getAttribute('href');
                            break;
                        case 'amphtml':
                            $attributes['amphtml'] = $node->getAttribute('href');
                            break;
                    }
                }
            }

            foreach ($attributes as $attribute => $value) {
                if (is_string($value)) {
                    $attributes[$attribute] = trim(str_replace(["\n", "\r"], '', $value));
                }
            }

            if (isset($attributes['title'])) {
                $attributes['title'] = trim(str_replace(["\n", "\r"], '', $attributes['title']));
            }

            if (isset($attributes['description'])) {
                $attributes['description'] = trim($attributes['description']);
            }

            if (isset($attributes['author'])) {
                $attributes['author'] = trim($attributes['author']);
            }

            if (isset($attributes['copyright'])) {
                $attributes['copyright'] = trim($attributes['copyright']);
            }

            if (isset($attributes['amphtml'])) {
                $attributes['amphtml'] = trim($attributes['amphtml']);
            }

            if (!isset($attributes['canonical']) || empty($attribute['canonical'])) {
                $attributes['canonical'] = $url;
            }

            if (isset($attributes['canonical'])) {
                $attributes['canonical'] = trim($attributes['canonical']);
            }
        } catch (GuzzleException $e) {
            // Unable to scrape the URL
            return false;
        } catch (Exception $e) {
            // We ignore this error, as it is just one of the properties that failed parsing
        }

        $components = @parse_url($attributes['canonical'] ?? $url);
        $schema = $components['schema'] ?? 'http';
        $domain = $components['host'] ?? null;
        $path = $components['path'] ?? null;

        $attributes['site'] = $domain;

        if (isset($attributes['icon']) && !empty($attributes['icon']) && strpos($attributes['icon'], 'http') !== 0) {
            if ($attributes['icon'][0] === '/') {
                $attributes['icon'] = $schema . '://' . $domain . $attributes['icon'];
            } else {
                $path = rtrim($path, '/');
                $attributes['icon'] = $schema . '://' . $domain . $path . '/' . $attributes['icon'];
            }
        }

        if (isset($attributes['image']) && !empty($attributes['image']) && strpos($attributes['image'], 'http') !== 0) {
            if ($attributes['image'][0] === '/') {
                $attributes['image'] = $schema . '://' . $domain . $attributes['image'];
            } else {
                $path = rtrim($path, '/');
                $attributes['image'] = $schema . '://' . $domain . $path . '/' . $attributes['image'];
            }
        }

        if (!isset($attributes['icon']) || empty($attribute['icon'])) {
            $favicon = $schema . '://' . $domain . '/favicon.ico';
            try {
                $response = $this->client->get($favicon);
                $attributes['icon'] = $favicon;
            } catch (Exception $e) {
                $attributes['icon'] = null;
            }
        }

        return new Metadata($attributes);
    }
}
