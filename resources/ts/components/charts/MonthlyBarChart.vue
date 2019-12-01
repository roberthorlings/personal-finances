<template>
    <generic-bar-chart
        :title="title"
        :series="series"
        :legend="legend"
        :height="height"
    />
</template>

<script>
    import { convertSingleStatsToSeriesData} from "../../charts";
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
                  this.series = [{
                      name: this.stats.category.name,
                      data: convertSingleStatsToSeriesData(this.stats.stats)
                  }];
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
