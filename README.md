# Kirby3 Cloudinary

## Configuration

In any config file or the default `site/config/config.php`:
```php
return [
  'cloudinary' => true,
  'cloudinary.cloudname' => 'cloudname',
  'cloudinary.folder' => 'folder',
  'cloudinary.defaults' => [
    'e_blur' => 300,
  ],
];
```
