<?php

namespace App\Enums;

enum ServerEnum
{
    case OpenSwoole;
    case Swoole;
    case RoadRunner;
    case FrankenPHP;

    public function getPort(): int
    {
        return match ($this) {
            self::OpenSwoole => 9801,
            self::Swoole => 9802,
            self::RoadRunner => 9803,
            self::FrankenPHP => 9804,
        };
    }

    public function getBenchmarkCommand(EndpointEnum $endpointEnum): string
    {
        return 'wrk -t16 -c100 -d'.$endpointEnum->getBenchmarkDuration().'s -s json.lua --latency  http://127.0.0.1:'.$this->getPort().$endpointEnum->getRoute();
    }

    public function getStatsCommand(): string
    {
        return 'docker stats '.strtolower($this->name).' --format=json --no-stream';
    }

    public function getTitle(): string
    {
        return $this->name;
    }
}
