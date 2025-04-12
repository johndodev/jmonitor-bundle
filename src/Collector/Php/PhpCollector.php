<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\Php;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;

class PhpCollector implements CollectorInterface
{
    private ?string $fpmStatusUrl;

    public function __construct(?string $fpmStatusUrl = null)
    {
        $this->fpmStatusUrl = $fpmStatusUrl;
    }

    public function collect(): array
    {
        return [
            'version' => phpversion(),
            'ini_file' => php_ini_loaded_file(),
            'ini_files' => $this->getIniFiles(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'date.timezone' => ini_get('date.timezone'),
            'loaded_extensions' => get_loaded_extensions(),
            'opcache' => $this->getOpcacheInfos(),
            'fpm' => 'TODO',
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

    /**
     * @return string[]
     */
    private function getIniFiles(): array
    {
        $files = php_ini_scanned_files();

        if (empty($files)) {
            return [];
        }

        return array_map('trim', explode(',', $files));
    }

    private function getOpcacheInfos(): array
    {
        if (!function_exists('opcache_get_status')) {
            return [
                'loaded' => false,
            ];
        }

        $status = opcache_get_status(false);

        if ($status === false) {
            return [
                'loaded' => true,
                'enabled' => ini_get('opcache.enable'),
                'enabled_cli' => ini_get('opcache.enable_cli'),
            ];
        }

        return [
            'enabled' => ini_get('opcache.enable'),
            'enabled_cli' => ini_get('opcache.enable_cli'),
            'cache_full' => $status['cache_full'],
            'memory_usage' => $status['memory_usage'] ?? [],
            'interned_strings_usage' => $status['interned_strings_usage'] ?? [],
            'statistics' => $status['opcache_statistics'] ?? [],
            'jit' => $status['jit'] ?? [],
        ];
    }
}
