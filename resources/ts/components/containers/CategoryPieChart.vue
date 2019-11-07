<template>
    <div class="container">
        <highcharts
            v-if="!loading"
            :options="options"
        />
    </div>
</template>

<script>
    import categoriesApi from "../../apis/categoriesApi";

    export default {
        name: 'CategoryBarChart',
        computed: {
            options() {
                return {
                    chart: {
                        type: 'pie'
                    },
                    title: {
                        text: this.title,
                    },
                    subtitle: {
                        text: 'Click the slices to drill down'
                    },
                    plotOptions: {
                        series: {
                            dataLabels: {
                                enabled: true,
                                format: '{point.name}: €{point.y:,.0f}'
                            }
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
        async mounted () {
            try {
                const stats = await categoriesApi.stats();
                const {initialSerie, drilldownSeries} = this.convertStatsToSeriesData(stats);

                this.series = [{...initialSerie, colorByPoint: true}];
                this.drilldownSeries = drilldownSeries;
                this.loading = false
            } catch (e) {
                console.error(e)
            }
        },
        methods: {
            convertStatsToSeriesData(stats) {
                const includedStats = stats.filter(stat => stat.total < 0);

                const series = this.convertStatsToDrilldownData({id: 0, name: 'Uitgaven'}, includedStats);
                const rootSerie = series.find(serie => serie.id === 0);

                return {
                    initialSerie: rootSerie,
                    drilldownSeries: series.filter(serie => serie && serie.id != rootSerie.id)
                }
            },

            convertStatsToDrilldownData({id, name}, children = [], subtotal = 0) {
                const drilldownSeries = [];

                if(children.length == 0) {
                    return [];
                }

                // Add drilldown series for children, if they have children as well
                children.forEach(child => {
                    drilldownSeries.push(...this.convertStatsToDrilldownData(child.category, child.children, child.subtotal));
                });

                // Generate a drilldownserie for this node
                const data = children.map(stat => {
                    const hasChildren = stat.children && stat.children.length > 0;
                    return {
                        name: stat.category.name + (hasChildren ? ' (+)' : ''),
                        y: Math.abs(stat.total),
                        drilldown: hasChildren ? stat.category.id : null
                    }
                });

                // If the subtotal for this node is non zero, add it as well
                if(subtotal != 0) {
                    data.push({
                        name: 'Overig',
                        y: Math.abs(subtotal)
                    })
                }

                // Add a drilldownserie for the current node
                drilldownSeries.push({
                    id,
                    name,
                    data: data
                });

                return drilldownSeries;
            },

        }
    }
</script>
