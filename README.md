# Netflex Scraper

A PHP library for scraping site metadata

## Installation

```bash
composer require netflex/scraper
```

## Usage with Netflex SDK

```php
<?php

use Scraper;

$metadata = Scraper::scrape('https://example.com');

echo $metadata->site;        // "example.com"
echo $metadata->title;       // "Example Domain"
echo $metadata->description; // "This domain is for use in illustrative examples in documents. You may use this    domain in literature without prior coordination or asking for permission."
echo $metadata->canonical;   // "https:\/\/example.com"
```

## Usage standdalone

```php
<?php

use Netflex\Scraper\Scraper;

$scraper = new Scraper();

$metadata = $scraper->scrape('https://example.com');
```

## Metadata fields

| Field       | Description                                         |
|-------------|-----------------------------------------------------|
| hash        | A hash that uniquely identifies this scrape         |
| language    | The documents language code                         |
| site        | The documents domain                                |
| title       | The documents title                                 |
| image       | The primary image of the document                   |
| description | A description of the document                       |
| keywords    | An array of keywords that describes the document    |
| canonical   | The documents canonical URL                         |
| icon        | The documents icon or favicon                       |
| author      | The documents author                                |
| copyright   | The documents copyright string                      |
| amphtml     | AMP version of this document                        |
| scraped     | The date and time this scrape was performed         |