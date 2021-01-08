<?php

namespace Netflex\Scraper;

use Carbon\Carbon;

use JsonSerializable;

use Netflex\Support\Accessors;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Support\Jsonable;

/**
 * @property-read string $hash A unique hash identifying the scraped metadata
 * @property-read string $id An alias for $hash
 * @property string|null $langauge Document language code
 * @property string|null $site The documents domain name
 * @property string|null $title Document title
 * @property string|null $image The first encountered image, or the og:image if available
 * @property string|null $description Document description
 * @property array $keywords Keywords
 * @property string $canonical The document canonical URL, if none found, the original request URL
 * @property string|null $icon The document favicon
 * @property string|null $author The document author
 * @property string|null $copyright The document copyright
 * @property string|null $amphtml Link to the AMP version of this document
 * @property string|null $amphtml Link to the AMP version of this document
 * @property DateTimeInterface|Carbon $scraped The date and time when this document was scraped
 * @package Netflex\Scraper
 */
class Metadata implements JsonSerializable, Jsonable
{
    use Accessors;

    protected $appends = ['hash'];

    public function __construct(array $attributes = [])
    {
        $this->timestamps = [
            'scraped'
        ];

        $this->attributes = $attributes;
    }

    /**
     * @return array 
     * @throws InvalidFormatException 
     */
    public function __debugInfo()
    {
        $attributes = [
            'hash' => $this->getHashAttribute(),
        ];

        foreach ($this->attributes as $attribute => $value) {
            $attributes[$attribute] = $this->__get($attribute) ?? $value;
        }

        return $attributes;
    }

    /**
     * @return string 
     */
    public function getIdAttribute()
    {
        return $this->getHashAttribute();
    }

    /**
     * @return string 
     */
    public function getHashAttribute()
    {
        $hash = [];
        foreach ($this->attributes as $attribute => $value) {
            if ($attribute !== 'scraped') {
                $hash[$attribute] = $value;
            }
        }

        return md5(json_encode($hash));
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->__debugInfo();
    }

    /**
     * @param int $options 
     * @return string 
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
