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

$scraper->scrape('https://example.com');

echo $metadata->site;        // "example.com"
echo $metadata->title;       // "Example Domain"
echo $metadata->description; // "This domain is for use in illustrative examples in documents. You may use this    domain in literature without prior coordination or asking for permission."
echo $metadata->canonical;   // "https:\/\/example.com"
```