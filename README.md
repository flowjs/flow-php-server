resumable.js php server
=======================

PHP library for handling chunk uploads. Library contains helper methods for:
 * Testing if uploaded file chunk exists.
 * Validating file chunk
 * Creating separate chunks folder
 * Validating uploaded chunks
 * Merging all chunks to a single file

 This library is compatible with HTML5 file upload library: https://github.com/resumable2/resumable.js

Advanced Usage
--------------

```php
    $file = new \Resumable\File($_REQUEST);
    $chunksDir = $file->init('./chunks_folder');
    $chunk = new \Resumable\Chunk($_REQUEST);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      if ($chunk->exists($chunksDir)) {
        header("HTTP/1.1 200 Ok");
      } else {
        header("HTTP/1.1 404 Not Found");
      }
      return ;
    }

    if (isset($_FILES['file']) && $chunk->validate($_FILES['file'])) {
      $chunk->overwrite($_FILES['file'], $chunksDir);
    } else {
      // error, invalid chunk upload request, retry
      header("HTTP/1.1 400 Bad Request");
    }
    if ($file->validate()) {
      $file->save('./final_file_name');
      $file->deleteChunks();
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
    \Resumable\Uploader::pruneChunks('./chunks_folder');
```

Cron task can by avoided by using random function execution.
```php
    if (1 == mt_rand(1, 100)) {
        \Resumable\Uploader::pruneChunks('./chunks_folder');
    }
```

Contribution
------------

Your participation in development is very welcome!

To ensure consistency throughout the source code, keep these rules in mind as you are working:
 * All features or bug fixes must be tested by one or more specs.
 * Your code should follow [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding style guide
