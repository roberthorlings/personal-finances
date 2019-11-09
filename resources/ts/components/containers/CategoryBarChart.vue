<template>
    <highcharts
        v-if="!loading"
        :options="options"
    />
</template>

<script>
    import categoriesApi from "../../apis/categoriesApi";
    import {convertStatsToSeriesData} from "../../charts";

    export default {
        name: 'CategoryBarChart',
        computed: {
            options() {
                return {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: this.title,
                    },
                    subtitle: {
                        text: 'Click the slices to drill down'
                    },
                    plotOptions: {
                    },
                    xAxis: {
                        type: 'category',
                        crosshair: true
                    },
                    yAxis: {
                        title: {
                            text: 'Uitgaven (€)'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>€{point.y:,.0f}</b><br/>'
                    },

                    series: this.series,
                    drilldown: {
                        series: this.drilldownSeries
                    }
                }
            }
        },

        data: () => ({
            loading: true,
            chartdata: null,
            title: 'Uitgaven',
            series: [],
            drilldownSeries: []
        }),
        mounted () {
            this.updateData();
        },
        methods: {
          updateData: async function() {
              this.loading = true;
              try {
                  const stats = await categoriesApi.stats({
                      year: this.year || undefined,
                      month: this.month || undefined
                  });
                  const {initialSerie, drilldownSeries} = convertStatsToSeriesData(stats);

                  this.series = [{...initialSerie, colorByPoint: true}];
                  this.drilldownSeries = drilldownSeries;
                  this.loading = false
              } catch (e) {
                  console.error(e)
              }
          }
        },
        props: {
            year: { type: Number },
            month: { type: Number }
        },
        watch: {
            year: function() { this.updateData(); },
            month: function() { this.updateData(); }
        }
    }
</script>
