<template>
    <generic-bar-chart
        :title="title"
        :series="series"
        :drilldown-series="drilldownSeries"
        :legend="legend"
        :height="height"
    />
</template>

<script>
    import categoriesApi from "../../apis/categoriesApi";
    import {convertCategoryStatsToSeriesData} from "../../charts";
    import GenericBarChart from "./GenericBarChart";

    export default {
        name: 'CategoryBarChart',
        components: {GenericBarChart},
        data: () => ({
            series: [],
            drilldownSeries: []
        }),
        mounted() {
            this.parseStats();
        },
        methods: {
            parseStats: async function() {
              const {initialSerie, drilldownSeries} = convertCategoryStatsToSeriesData(this.stats);

              this.series = [{...initialSerie, colorByPoint: true}];
              this.drilldownSeries = drilldownSeries;
          }
        },
        props: {
            title: { type: String },
            stats: { type: Array },
            legend: { type: Boolean, default: true },
            height: { type: Number }
        },
        watch: {
            stats: function() { this.parseStats(); }
        }

    }
</script>
