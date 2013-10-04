flow.js php server [![Build Status](https://travis-ci.org/flowjs/flow-php-server.png?branch=master)](https://travis-ci.org/flowjs/flow-php-server) [![Coverage Status](https://coveralls.io/repos/flowjs/flow-php-server/badge.png?branch=master)](https://coveralls.io/r/flowjs/flow-php-server?branch=master)
=======================

PHP library for handling chunk uploads. Library contains helper methods for:
 * Testing if uploaded file chunk exists.
 * Validating file chunk
 * Creating separate chunks folder
 * Validating uploaded chunks
 * Merging all chunks to a single file

This library is compatible with HTML5 file upload library: https://github.com/flowjs/flow.js

Advanced Usage
--------------

```php
$file = new \Flow\File($_REQUEST);
$chunksDir = $file->init('./chunks_folder');
$chunk = new \Flow\Chunk($_REQUEST);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($chunk->exists($chunksDir)) {
        header("HTTP/1.1 200 Ok");
    } else {
        header("HTTP/1.1 404 Not Found");
        return ;
    }
} else {
  if (isset($_FILES['file']) && $chunk->validate($_FILES['file'])) {
      $chunk->save($_FILES['file'], $chunksDir);
  } else {
      // error, invalid chunk upload request, retry
      header("HTTP/1.1 400 Bad Request");
      return ;
  }
}
if ($file->validate() && $file->save('./final_file_name')) {
    // File upload was completed
} else {
    // This is not a final chunk, continue to upload
}
```

Delete unfinished files
-----------------------

For this you should setup cron, which would check each chunk upload time.
If chunk is uploaded long time ago, then chunk should be deleted.

Helper method for checking this:
```php
\Flow\Uploader::pruneChunks('./chunks_folder');
```

Cron task can by avoided by using random function execution.
```php
if (1 == mt_rand(1, 100)) {
    \Flow\Uploader::pruneChunks('./chunks_folder');
}
```

Contribution
------------

Your participation in development is very welcome!

To ensure consistency throughout the source code, keep these rules in mind as you are working:
 * All features or bug fixes must be tested by one or more specs.
 * Your code should follow [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding style guide
