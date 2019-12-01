<template>
    <div>
        <v-container fluid>
            <v-row>
                <v-col sm="3" xs="12">
                    <v-select :items="years" label="Year" v-model="year"></v-select>
                </v-col>
            </v-row>
            <with-stats :year="year" v-slot="slotProps">
                <v-row>
                    <v-col md="3" sm="6" xs="12" >
                        <v-card
                            class="mx-auto"
                        >
                            <v-card-title>Total income and expenses</v-card-title>
                            <income-expenses-chart
                                :topLevelCategories="topLevelCategories"
                                :height="2 * 300 + 64 + 16"
                            />
                        </v-card>
                    </v-col>
                    <v-col md="9" sm="6" xs="12" >
                        <v-card
                            class="mx-auto"
                        >
                            <v-tabs
                                v-model="tab"
                                class="elevation-2"
                            >
                                <v-tab href="#tab-income">Income</v-tab>
                                <v-tab href="#tab-expenses">Expenses</v-tab>
                                <v-tab-item value="tab-income">
                                    <category-bar-chart
                                        :stats="getIncome(slotProps.stats)"
                                        :legend="false"
                                        :height="300"
                                    />
                                </v-tab-item>
                                <v-tab-item value="tab-expenses">
                                    <category-bar-chart
                                        :stats="getExpenses(slotProps.stats)"
                                        :legend="false"
                                        :height="300"
                                    />
                                </v-tab-item>
                            </v-tabs>
                        </v-card>
                        <v-card
                            class="mx-auto mt-4"
                        >
                            <v-card-title>
                                Per month
                                <div class="flex-grow-1"></div>
                                <span style="max-width: 300px">
                                    <v-autocomplete
                                        v-model="category"
                                        :items="categories"
                                        item-value="id"
                                        item-text="text"
                                        label="Category"
                                        auto-select-first
                                    ></v-autocomplete>
                                </span>
                            </v-card-title>
                            <div v-if="loadingMonthlyStats">Loading...</div>
                            <monthly-bar-chart
                                v-if="!loadingMonthlyStats"
                                :stats="(monthlyStats)"
                                :legend="false"
                                :height="300"
                            />
                        </v-card>
                    </v-col>
                </v-row>

            </with-stats>

        </v-container>

    </div>
</template>

<script>
    import IncomeExpensesChart from "./charts/IncomeExpensesChart";
    import CategoryBarChart from "./charts/CategoryBarChart";
    import {toFlatList} from "../utils";
    import CategoriesApi from "../apis/categoriesApi";

    export default {
        name: 'Reports',
        components: {IncomeExpensesChart, CategoryBarChart},
        data: () => ({
            topLevelCategories: [],
            categories: [],
            category: null,
            loadingMonthlyStats: false,
            monthlyStats: {},
            year: null,
            years: [
                {text: 'all years', value: null},
                {text: '2015', value: 2015},
                {text: '2016', value: 2016},
                {text: '2017', value: 2017},
                {text: '2018', value: 2018},
                {text: '2019', value: 2019},
                {text: '2020', value: 2020},
                {text: '2021', value: 2021},
            ],
            tab: 'tab-expenses'
        }),
        mounted() {
            this.getCategories();
        },
        methods: {
            getIncome(stats) {
                return stats.find(stat => stat.category.key === 'inkomsten').children;
            },
            getExpenses(stats) {
                return stats.find(stat => stat.category.key === 'uitgaven').children;
            },
            getCategories() {
                return CategoriesApi.tree()
                    .then(data => {
                        this.categories = toFlatList(data);
                        this.category = data.find(cat => cat.key === 'uitgaven').id;
                        this.topLevelCategories = data.map(cat => ({...cat, children: []}));
                    })
                    .catch(e => {
                        console.error("Error while loading categories");
                        this.$emit('error', {type: 'categories', message: e.message});
                    });
            },
            loadMonthlyStats: function() {
                CategoriesApi.categoryStats({category: this.category, year: this.year})
                    .then(data => {
                        this.monthlyStats = data;
                    })
                    .catch(e => {
                        console.error("Error while loading monthly stats for category", this.category_id);
                        this.$emit('error', {type: 'monthlyStats', message: e.message});
                    });
            }
        },
        watch: {
            category() {
                this.loadMonthlyStats();
            },
            year() {
                this.loadMonthlyStats();
            }
        }
    }
</script>
