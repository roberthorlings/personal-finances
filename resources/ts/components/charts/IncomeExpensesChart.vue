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
        name: 'IncomeExpensesChart',
        computed: {
            options() {
                return {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: this.title,
                    },
                    plotOptions: {
                    },
                    xAxis: {
                        type: 'category',
                        crosshair: true
                    },
                    legend: {
                        enabled: false
                    },
                    yAxis: {
                        title: {
                            text: 'Amount (€)'
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                        pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>€{point.y:,.0f}</b><br/>'
                    },
                    series: this.series,
                }
            }
        },

        data: () => ({
            loading: true,
            chartdata: null,
            series: []
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

                  // We are only interested in top-level categories
                  const topLevelStats = stats.map(stat => ({...stat, children: []}));
                  const {initialSerie} = convertStatsToSeriesData(topLevelStats);

                  // Make sure to merge any category other than income or expenses
                  const mergedSerie = {
                      ...initialSerie,
                      data: this.mergeCategories(initialSerie.data)
                  };

                  this.series = [{...mergedSerie, colorByPoint: true}];
                  this.loading = false
              } catch (e) {
                  console.error(e)
              }
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

              const merged = data
                  .map(point => point.y)
                  .reduce(reducer, initial);
              console.log({data, merged});
              return [
                  {
                      name: "Inkomsten",
                      y: merged.income
                  },
                  {
                      name: "Uitgaven",
                      y: Math.abs(merged.expenses)
                  }
              ];
          }
        },
        props: {
            title: { type: String },
            year: { type: Number },
            month: { type: Number }
        },
        watch: {
            year: function() { this.updateData(); },
            month: function() { this.updateData(); }
        }
    }
</script>
