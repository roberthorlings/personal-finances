<template>
    <highcharts
        :options="chartOptions"
    />
</template>

<script>
    export default {
        name: 'GenericBarChart',
        computed: {
            subtitleText() {
                return this.subtitle || '';
            },
            chartOptions() {
                return {
                    chart: {
                        height: this.height,
                        type: 'column'
                    },
                    title: {
                        text: this.title,
                    },
                    subtitle: {
                        text: this.subtitleText
                    },
                    plotOptions: {
                    },
                    legend: {
                        enabled: this.legend
                    },
                    xAxis: {
                        type: 'category',
                        crosshair: true
                    },
                    yAxis: {
                        title: {
                            text: 'Bedrag (€)'
                        }
                    },
                    tooltip: {
                        pointFormat: '€{point.y:,.0f}'
                    },

                    series: this.series,
                    drilldown: {
                        series: this.drilldownSeries
                    },
                    ...this.options
                }
            }
        },
        props: {
            title: { type: String },
            subtitle: { type: String },
            series: { type: Array },
            drilldownSeries: { type: Array, default: () => [] },
            options: { type: Object, default: () => {} },
            legend: { type: Boolean, default: true },
            height: { type: Number }
        },
    }
</script>
