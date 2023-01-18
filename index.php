<?php

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\FileVersion;

function cloudinary($url, $params = [], $type = 'image')
{
    if (is_object($url) === true) {
        $url = $url->url();
    }

    // always convert urls to absolute urls
    $url = url($url);

    // return the plain url if cloudinary is deactivated
    if (option('cloudinary', false) === false) {
        return $url;
    }

    $url = str_replace(kirby()->url('media') . '/', '', $url);

    $defaults = [
        'q' => 'auto',
        'f' => 'auto',
        'g' => 'auto',
        'c' => 'fill',
        'cs' => 'srgb'
    ];

    if ($type == 'video') {
      $defaults = [
        'q' => 'auto:eco',
        'f' => 'mp4',
        'w'   => 1600
      ];
    }

    $defaults  = array_merge($defaults, option('cloudinary.defaults', []));

    $params  = array_merge($defaults, $params);
    unset($params['crop']);
    $options = [];

    $map = [
        'width'   => 'w',
        'height'  => 'h',
        'crop'  => 'c',
    ];

    foreach ($params as $key => $value) {
        if (isset($map[$key]) && !empty($value)) {
            $options[] = $map[$key] . '_' . $value;
        } elseif (!empty($value)) {
            $options[] = $key . '_' . $value;
        }
    }

    $options = implode(',', $options);

    return "https://res.cloudinary.com/". option("cloudinary.cloudname") ."/$type/upload/". $options . "/" . option("cloudinary.folder") . "/" . $url;

}

Kirby::plugin('getkirby/cloudinary', [
    'components' => [
        'file::version' => function (App $kirby, File $file, array $options = []) {

            static $originalComponent;

            if (option('cloudinary', false) !== false) {
                $url = cloudinary($file->mediaUrl(), $options);

                return new FileVersion([
                    'modifications' => $options,
                    'original'      => $file,
                    'root'          => $file->root(),
                    'url'           => $url,
                ]);
            }

            if ($originalComponent === null) {
                $originalComponent = (require $kirby->root('kirby') . '/config/components.php')['file::version'];
            }

            return $originalComponent($kirby, $file, $options);
        },

        'file::url' => function (App $kirby, File $file): string {

            static $originalComponent;

            if (option('cloudinary', false) !== false) {
                if ($file->type() === 'image') {
                    return cloudinary($file->mediaUrl());
                }
                if ($file->type() === 'video' && $file->filename() != 'addpmp.mp4') {
                    return cloudinary($file->mediaUrl(), [], 'video');
                }
                return $file->mediaUrl();
            }

            if ($originalComponent === null) {
                $originalComponent = (require $kirby->root('kirby') . '/config/components.php')['file::url'];
            }

            return $originalComponent($kirby, $file);
        }
    ]
]);
