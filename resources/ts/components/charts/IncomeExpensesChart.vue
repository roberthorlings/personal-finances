<template>
    <generic-bar-chart
        :title="title"
        :series="series"
        :legend="true"
        :height="height"
        :options="options"
    />
</template>

<script>
    import categoriesApi from "../../apis/categoriesApi";
    import GenericBarChart from "./GenericBarChart";

    export default {
        name: 'IncomeExpensesChart',
        components: {GenericBarChart},
        data: () => ({
            series: [],
            options: {
                chart: {
                    type: 'bar'
                },
                tooltip: {
                    headerFormat: '{series.name} {point.key}<br />',
                    pointFormat: '€{point.y:,.0f}'
                },
                plotOptions: {
                    bar: {
                        pointWidth: 12,
                        pointPadding: 0.1,
                        groupPadding: 0.2
                    }
                }
            }
        }),
        mounted () {
            this.loadStats();
        },
        methods: {
            loadStats: async function() {
                const stats = await Promise.all(
                    this.topLevelCategories
                        // Load statistics
                        .map(cat => this.loadCategoryStats(cat.id))
                );

                const perYear = stats
                    // Combine all stats
                    .flatMap(categoryStats => categoryStats.stats)

                    // Group by year
                    .reduce((acc, val) => {
                        if(!acc[val.year])
                            acc[val.year] = [];

                        acc[val.year].push(val);
                        return acc;
                    }, {});

                // Merge categories within a year
                const combinedPerYear = Object.assign({}, ...Object.keys(perYear).map(year => ({[year]: this.mergeCategories(perYear[year])})));

                // Create actual series
                this.series = [
                    {
                        name: "Income",
                        color: 'rgb(144, 237, 125)',
                        data: Object.keys(combinedPerYear).map(year => ({
                            name: year,
                            y: combinedPerYear[year].income
                        }))
                    },
                    {
                        name: "Expenses",
                        color: 'rgb(244, 91, 91)',
                        data: Object.keys(combinedPerYear).map(year => ({
                            name: year,
                            y: Math.abs(combinedPerYear[year].expenses)
                        }))
                    }
                ];
            },
            loadCategoryStats: async function(category) {
                return categoriesApi.categoryStats({category});
            },
          mergeCategories: function(data) {
              const initial = {
                  income: 0,
                  expenses: 0
              };
              const reducer = (total, currentValue) => (currentValue > 0 ?
                      {
                          income: total.income + currentValue,
                          expenses: total.expenses
                      } :
                      {
                          income: total.income,
                          expenses: total.expenses + currentValue
                      });

              return data
                  .map(point => point.total)
                  .reduce(reducer, initial);
          }
        },
        props: {
            title: { type: String },
            topLevelCategories: { type: Array, default: [] },
            height: { type: Number }
        },
        watch: {
            topLevelCategories: function() { this.loadStats(); }
        }
    }
</script>
