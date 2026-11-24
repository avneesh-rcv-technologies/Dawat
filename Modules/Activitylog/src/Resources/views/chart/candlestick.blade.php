@extends('backend::layouts.app')

@section('title', 'Candlestick Chart for ' . $ip)

@section('breadcrumbs')
    @php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'System Settings', 'url' => route('settings.index')],
            ['label' => 'Advance Logging', 'url' => route('activity-log.advance-logging')],
            [
                'label' => 'Candlestick Chart',
                'url' => route('activity-log.advance-logging.candlestick-chart', ['ip' => $ip]),
            ],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Candlestick Chart'" />
@endsection

@section('content')
    <div id="candlestick-chart" style="height: 400px;"></div>
    <div id="heatmap-chart" style="height: 320px;"></div>
    {{-- add --}}
@endsection

@section('scripts')
    <script src="https://cdn.example.com/v5.3.6/script.js"
        integrity="sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/uxy9rx7HNQlGYl1kPzQho1wx4JwY8wC"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch("{{ route('activity-log.advance-logging.candlestick-data', $ip) }}")
                .then(response => response.json())
                .then(data => {
                    var options = {
                        chart: {
                            type: 'candlestick',
                            height: 400
                        },
                        series: [{
                            data: data
                        }],
                        xaxis: {
                            type: 'datetime'
                        }
                    };
                    var chart = new ApexCharts(document.querySelector("#candlestick-chart"), options);
                    chart.render();
                });

            fetch("{{ route('activity-log.advance-logging.heatmap-data', $ip) }}")
                .then(response => response.json())
                .then(data => {
                    var options = {
                        chart: {
                            type: 'heatmap',
                            height: 320
                        },
                        dataLabels: {
                            enabled: false
                        },
                        colors: ["#008FFB"],
                        series: data.series,
                        xaxis: {
                            categories: data.hours
                        },
                        yaxis: {
                            categories: data.days
                        },
                        title: {
                            text: 'Requests Heatmap (Hour x Day)'
                        }
                    };
                    var chart = new ApexCharts(document.querySelector("#heatmap-chart"), options);
                    chart.render();
                });
        });
    </script>
@endsection