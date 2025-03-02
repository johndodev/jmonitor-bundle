<?php

declare(strict_types=1);

namespace Johndodev\JmonitorBundle\Collector\Apache;

use Johndodev\JmonitorBundle\Collector\CollectorInterface;
use Johndodev\JmonitorBundle\Exceptions\CollectorException;

class ApacheCollector implements CollectorInterface
{
    private string $url;
    private array $datas = [];

    public function __construct(string $url)
    {
        if (!str_ends_with($url, '?auto')) {
            $url .= '?auto';
        }

        $this->url = $url;
    }

    public function collect(): array
    {
        $this->loadDatas();

        return [
            'server_version' => $this->getData('ServerVersion'),
            'uptime' => (int) $this->getData('Uptime'),
            'load1' => (float) $this->getData('Load1'),
            'total_accesses' => (int) $this->getData('Total Accesses'),
            'total_bytes' => (int) $this->getData('Total kBytes') * 1024,
            'requests_per_second' => (int) round((float) $this->getData('ReqPerSec')),
            'bytes_per_second' => (int) $this->getData('BytesPerSec'),
            'bytes_per_request' => (int) $this->getData('BytesPerReq'),
            'duration_per_request' => (int) $this->getData('DurationPerReq'),
            'workers' => [
                'busy' => (int) $this->getData('BusyWorkers'),
                'idle' => (int) $this->getData('IdleWorkers'),
            ],
            'scoreboard' => $this->parseScoreboard($this->getData('Scoreboard')),
        ];
    }

    public function getVersion(): int
    {
        return 1;
    }

    private function loadDatas(): void
    {
        $this->datas = [];

        $content = file_get_contents($this->url);

        if (!$content) {
            throw new CollectorException($this, 'Could not fetch data from ' . $this->url);
        }

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $parts = explode(':', $line);
            $this->datas[$parts[0]] = isset($parts[1]) ? trim($parts[1]) : null;
        }
    }

    private function getData(string $key): ?string
    {
        return $this->datas[$key] ?? null;
    }

    private function parseScoreboard(string $scoreboard): array
    {
        $result = [];

        foreach (str_split($scoreboard) as $char) {
            $result[$char] = ($result[$char] ?? 0) + 1;
        }

        return $result;
    }
}
