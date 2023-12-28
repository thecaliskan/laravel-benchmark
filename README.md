## Laravel Benchmark


### OpenSwoole

```bash
docker run -p 9801:9801/tcp ghcr.io/thecaliskan/laravel-benchmark:openswoole

wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9801/api/health-check
```

### Swoole

```bash
docker run -p 9802:9802/tcp ghcr.io/thecaliskan/laravel-benchmark:swoole

wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9802/api/health-check
```

### RoadRunner

```bash
docker run -p 9803:9803/tcp ghcr.io/thecaliskan/laravel-benchmark:roadrunner

wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9803/api/health-check
```

### FrankenPHP

```bash
docker run -p 9804:9804/tcp ghcr.io/thecaliskan/laravel-benchmark:frankenphp

wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9804/api/health-check
```
