<template>
    <generic-bar-chart
        :title="title"
        :series="series"
        :legend="legend"
        :height="height"
    />
</template>

<script>
    import {convertSingleStatsToMonthlySeriesData, runningAverage} from "../../charts";
    import GenericBarChart from "./GenericBarChart";

    export default {
        name: 'MonthlyBarChart',
        components: {GenericBarChart},
        data: () => ({
            series: [],
        }),
        mounted() {
            this.parseStats();
        },
        methods: {
            parseStats: function() {
              if(!this.stats || !this.stats.category) {
                  this.series = [];
              } else {
                  const monthlyData = convertSingleStatsToMonthlySeriesData(this.stats.stats);
                  this.series = [
                      {
                          type: 'bar',
                          name: this.stats.category.name,
                          data: monthlyData
                      },
                      {
                          type: 'spline',
                          name: 'Average',
                          data: runningAverage(monthlyData)
                      }
                  ];
              }
          }
        },
        props: {
            title: { type: String },
            stats: { type: Object },
            legend: { type: Boolean, default: true },
            height: { type: Number }
        },
        watch: {
            stats: function() { this.parseStats(); }
        }

    }
</script>
