<?php

namespace App\Enums;

use Illuminate\Support\Stringable;

enum EndpointEnum
{
    case HealthCheck;
    case Static;
    case HttpRequest;

    public function getRoute(): string
    {
        return match ($this) {
            self::HealthCheck => route('health-check', [], false),
            self::Static => route('static', [], false),
            self::HttpRequest => route('http-request', [], false),
        };
    }

    public function getFileName(ServerEnum $serverEnum): Stringable
    {
        return str($serverEnum->name.$this->name)->replace('PHP', 'Php')->kebab();
    }

    public function getBenchmarkDuration(): int
    {
        return match ($this) {
            default => 30,
        };
    }

    public function getKebabCase(): string
    {
        return str($this->name)->kebab();
    }

    public function getTitle(): string
    {
        return str($this->name)->snake(' ')->title();
    }
}
