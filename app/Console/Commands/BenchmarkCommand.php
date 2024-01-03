<?php

namespace App\Console\Commands;

use App\Enums\EndpointEnum;
use App\Enums\ServerEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class BenchmarkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:benchmark';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Benchmark';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (ServerEnum::cases() as $serverEnum) {
            foreach (EndpointEnum::cases() as $endpointEnum) {
                Process::run('docker compose down && docker compose up -d')->throw();
                sleep(3);

                $process = Process::start($serverEnum->getBenchmarkCommand($endpointEnum));

                $statistics = [];
                usleep(1000000 - now()->microsecond);
                while ($process->running()) {
                    if (isset($statistics[now()->toAtomString()])) {
                        usleep(1000000 - now()->microsecond);

                        continue;
                    }
                    $statistics[now()->toAtomString()] = Process::start($serverEnum->getStatsCommand());
                }

                $benchmarkOutput = $process->wait()->output();

                foreach ($statistics as $key => $statistic) {
                    $statistics[$key] = json_decode($statistic->wait()->output(), true);
                }

                $explodedBenchmarkOutput = explode("\nJSON Output:\n", $benchmarkOutput);

                Storage::disk('benchmark')->put(
                    $endpointEnum->getFileName($serverEnum)->append('-wrk.md'),
                    $explodedBenchmarkOutput[0]
                );

                Storage::disk('benchmark')->put(
                    $endpointEnum->getFileName($serverEnum)->append('-wrk.json'),
                    json_encode(json_decode($explodedBenchmarkOutput[1]), JSON_PRETTY_PRINT)
                );

                Storage::disk('benchmark')->put(
                    $endpointEnum->getFileName($serverEnum)->append('-statistics.json'),
                    json_encode($statistics, JSON_PRETTY_PRINT)
                );
            }
        }

        $this->generateTables();

        return true;
    }

    protected function arrayToCsv(string $filename, array $data): void
    {
        $csv = fopen(storage_path('table/'.$filename.'.csv'), 'w');
        foreach ($data as $datum) {
            fputcsv($csv, $datum, ',', '"');
        }
        fclose($csv);
    }

    protected function getBenchmarkData($serverEnum, $endpointEnum): Collection
    {
        return collect(json_decode(Storage::disk('benchmark')->get($endpointEnum->getFileName($serverEnum)->append('-wrk.json')), true));
    }

    protected function getStatisticsData($serverEnum, $endpointEnum): Collection
    {
        return collect(json_decode(Storage::disk('benchmark')->get($endpointEnum->getFileName($serverEnum)->append('-statistics.json')), true));
    }

    public function generateTables(): void
    {
        $this->generateRequestsTable();
        $this->generateRequestPerSecondTable();
        $this->generateTransferPerSecondTable();
        $this->generateLatencyDistributionTable();
        $this->generateCpuUsageTable();
        $this->generateMemoryUsageTable();
    }

    public function generateRequestPerSecondTable(): void
    {
        $data = [['Server']];

        foreach (EndpointEnum::cases() as $endpointEnum) {
            $data[0][] = $endpointEnum->getTitle();
        }

        foreach (ServerEnum::cases() as $serverEnum) {
            $data[$serverEnum->name][] = $serverEnum->getTitle();
            foreach (EndpointEnum::cases() as $endpointEnum) {
                $data[$serverEnum->name][] = $this->getBenchmarkData($serverEnum, $endpointEnum)->get('requests_per_sec');
            }
        }

        $this->arrayToCsv('request-per-second', $data);
    }

    public function generateRequestsTable(): void
    {
        $data = [['Server']];

        foreach (EndpointEnum::cases() as $endpointEnum) {
            $data[0][] = $endpointEnum->getTitle();
        }

        foreach (ServerEnum::cases() as $serverEnum) {
            $data[$serverEnum->name][] = $serverEnum->getTitle();
            foreach (EndpointEnum::cases() as $endpointEnum) {
                $data[$serverEnum->name][] = $this->getBenchmarkData($serverEnum, $endpointEnum)->get('requests');
            }
        }

        $this->arrayToCsv('requests', $data);
    }

    public function generateTransferPerSecondTable(): void
    {
        $data = [['Server']];

        foreach (EndpointEnum::cases() as $endpointEnum) {
            $data[0][] = $endpointEnum->getTitle();
        }

        foreach (ServerEnum::cases() as $serverEnum) {
            $data[$serverEnum->name][] = $serverEnum->getTitle();
            foreach (EndpointEnum::cases() as $endpointEnum) {
                $data[$serverEnum->name][] = $this->getBenchmarkData($serverEnum, $endpointEnum)->get('bytes_transfer_per_sec') / 1024 / 1024;
            }
        }

        $this->arrayToCsv('transfer-per-second', $data);
    }

    public function generateLatencyDistributionTable(): void
    {
        foreach (EndpointEnum::cases() as $endpointEnum) {
            $data = [['Server', '50%', '75%', '90%', '99%', '99.99%']];
            foreach (ServerEnum::cases() as $serverEnum) {
                $data[$serverEnum->name][] = $serverEnum->getTitle();

                foreach ($this->getBenchmarkData($serverEnum, $endpointEnum)->get('latency_distributions') as $latencyDistribution) {
                    $data[$serverEnum->name][] = $latencyDistribution['latency_in_microseconds'] / 1000;
                }
            }
            $this->arrayToCsv('latency-distribution-'.$endpointEnum->getKebabCase(), $data);
        }
    }

    public function generateCpuUsageTable(): void
    {
        foreach (EndpointEnum::cases() as $endpointEnum) {
            $data = [[
                'Server',
                ...array_map(
                    fn ($second) => str($second)->padLeft(2, 0)->prepend('00:'),
                    range(1, $endpointEnum->getBenchmarkDuration() - 1)
                ),
            ]];
            foreach (ServerEnum::cases() as $serverEnum) {
                $data[$serverEnum->name] = [
                    $serverEnum->getTitle(),
                    ...$this->getStatisticsData($serverEnum, $endpointEnum)
                        ->pluck('CPUPerc')
                        ->slice(0, $endpointEnum->getBenchmarkDuration() - 1)
                        ->map(fn ($percentage) => str_replace('%', '', $percentage)),
                ];
            }
            $this->arrayToCsv('cpu-usage-'.$endpointEnum->getKebabCase(), $data);
        }
    }

    public function generateMemoryUsageTable(): void
    {
        foreach (EndpointEnum::cases() as $endpointEnum) {
            $data = [[
                'Server',
                ...array_map(
                    fn ($second) => str($second)->padLeft(2, 0)->prepend('00:'),
                    range(1, $endpointEnum->getBenchmarkDuration())
                ),
            ]];
            foreach (ServerEnum::cases() as $serverEnum) {
                $data[$serverEnum->name] = [
                    $serverEnum->getTitle(),
                    ...$this->getStatisticsData($serverEnum, $endpointEnum)
                        ->pluck('MemUsage')
                        ->slice(0, $endpointEnum->getBenchmarkDuration())
                        ->map(fn ($usage) => str($usage)->contains('GiB /') ? explode('GiB', $usage)[0] * 1024 : explode('MiB', $usage)[0]),
                ];
            }
            $this->arrayToCsv('memory-usage-'.$endpointEnum->getKebabCase(), $data);
        }
    }
}
