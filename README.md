# PHP-Unsplash
PHP SDK for running queries against the millions of icons provided by
[Unsplash](https://unsplash.com). Includes recursive searches.

### Supports
- Searches
- Download tracking

### Sample Search
``` php
$client = new onassar\Unsplash\Unsplash();
$client->setAPIKey('***');
$client->setLimit(10);
$client->setOffset(0);
$results = $client->search('love') ?? array();
print_r($results);
exit(0);
```

### Sample Download Tracking
``` php
$client = new onassar\Unsplash\Unsplash();
$client->setAPIKey('***');
$tracked = $client->trackDownload('photo:id') ?? false;
echo $tracked;
exit(0);
```

### Note
Requires
[PHP-RemoteRequests](https://github.com/onassar/PHP-RemoteRequests).
