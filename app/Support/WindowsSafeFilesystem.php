<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;

class WindowsSafeFilesystem extends Filesystem
{
    /**
     * Write the contents of a file, replacing it atomically if possible.
     * On Windows, handles file locking and concurrent blade compilation race conditions gracefully.
     *
     * @param  string  $path
     * @param  string  $content
     * @param  int|null  $mode
     * @return void
     */
    public function replace($path, $content, $mode = null)
    {
        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        if (! is_null($mode)) {
            @chmod($tempPath, $mode);
        } else {
            @chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        // Attempt normal rename first
        if (@rename($tempPath, $path)) {
            return;
        }

        // Windows race condition handling
        if (DIRECTORY_SEPARATOR === '\\') {
            // Retry a few times with short delay in case another thread is releasing the file
            for ($i = 0; $i < 5; $i++) {
                usleep(25000); // 25ms
                if (@rename($tempPath, $path)) {
                    return;
                }
            }

            // If the destination file already exists and is locked/in-use by another worker,
            // another process has already compiled or is actively serving the view.
            // We can safely delete the temporary file and proceed without crashing.
            if (file_exists($path)) {
                @unlink($tempPath);
                return;
            }

            // Fallback: try copy and unlink
            if (@copy($tempPath, $path)) {
                @unlink($tempPath);
                return;
            }

            @unlink($tempPath);
            return;
        }

        rename($tempPath, $path);
    }
}
