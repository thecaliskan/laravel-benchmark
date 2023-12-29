## Laravel Benchmark


### Install

```bash
wget https://raw.githubusercontent.com/thecaliskan/laravel-benchmark/master/docker-compose.yml

docker compose up -d
```

### Benchmark


#### OpenSwoole
```bash
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9801/api/health-check
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9801/api/static
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9801/api/http-request
```

#### Swoole

```bash
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9802/api/health-check
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9802/api/static
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9802/api/http-request
```

#### RoadRunner

```bash
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9803/api/health-check
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9803/api/static
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9803/api/http-request
```

#### FrankenPHP

```bash
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9804/api/health-check
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9804/api/static
wrk -t8 -c16 -d30s --latency  http://127.0.0.1:9804/api/http-request
```
