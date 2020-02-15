<?php

namespace Blegrator\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

class File
{
    public static function normalizePath($path)
    {
        $wrapper = '';
        if (self::isStream($path)) {
            list($wrapper, $path) = explode('://', $path, 2);
            $wrapper .= '://';
        }

        // Standardise all paths to use /
        $path = str_replace('\\', '/', $path);

        // Replace multiple slashes down to a singular, allowing for network shares having two slashes.
        $path = preg_replace('|(?<=.)/+|', '/', $path);

        // Windows paths should uppercase the drive letter
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }

        return $wrapper . $path;
    }

    public static function isStream($path)
    {
        $scheme_separator = strpos($path, '://');
        if (false === $scheme_separator) {
            // $path isn't a stream
            return false;
        }
        $stream = substr($path, 0, $scheme_separator);
        return in_array($stream, stream_get_wrappers(), true);
    }

    public static function pathIsAbsolute($path)
    {
        /*
         * Check to see if the path is a stream and check to see if its an actual
         * path or file as realpath() does not support stream wrappers.
         */
        if (self::isStream($path) && (is_dir($path) || is_file($path))) {
            return true;
        }

        /*
         * This is definitive if true but fails if $path does not exist or contains
         * a symbolic link.
         */
        if (realpath($path) == $path) {
            return true;
        }

        if (strlen($path) == 0 || $path[0] == '.') {
            return false;
        }

        // Windows allows absolute paths like this.
        if (preg_match('#^[a-zA-Z]:\\\\#', $path)) {
            return true;
        }

        // A path starting with / or \ is absolute; anything else is relative.
        return ($path[0] == '/' || $path[0] == '\\');
    }

    public static function pathJoin($base, $path)
    {
        if (self::pathIsAbsolute($path)) {
            return $path;
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    public static function delete($path, $base_path = null)
    {
        if (is_null($base_path)) {
            $base_path = public_path();
        }
        $filePath = self::normalizePath(self::pathJoin($base_path, $path));
        if (file_exists($filePath)) {
            \Illuminate\Support\Facades\File::delete($filePath);
            return true;
        }

        return false;
    }

    public static function deleteImageWithOriginal($img_path, $fileName, $base_path = null, $originalDir = 'original')
    {
        foreach ([
                File::pathJoin($img_path, $fileName),
                File::pathJoin($img_path . '/' . $originalDir, $fileName)
            ] as $file
        ) {
            File::delete($file, $base_path);
        }
    }

    public static function uploadImage(Request $request, $field, $img_path, $q = 75, $width = null, $height = null)
    {
        $image = $request->file($field);
        $FileName = strtolower(time() . Str::random(5) . "." . $image->getClientOriginalExtension());
        $destinationPath = public_path($img_path);
        $img = new ImageManager();
        $img->make($image->path())
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // If Width > Than Not Crop
            })
            ->save($destinationPath . '/' . $FileName, $q);
        $destinationPath = public_path($img_path . '/original');
        $image->move($destinationPath, $FileName);

        return $FileName;
    }
}
