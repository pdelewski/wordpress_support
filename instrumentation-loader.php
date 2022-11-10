<?php

require __DIR__ . '/vendor/autoload.php';

use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter;

$logger = new Logger('trace');
$logger->pushHandler(new StreamHandler(__DIR__.'trace.log', Logger::DEBUG));

$exporter = JaegerExporter::fromConnectionString('http://host.docker.internal:9412/api/v2/spans', 'QuoteService AutoInstrumentation');

$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor($exporter)
);

$scope = \OpenTelemetry\API\Common\Instrumentation\Configurator::create()
     ->withTracerProvider($tracerProvider)
     ->activate();

function shutdown($scope, $tracerProvider) {
    $scope->detach();
    $tracerProvider->shutdown();
}

register_shutdown_function('shutdown', $scope, $tracerProvider);
