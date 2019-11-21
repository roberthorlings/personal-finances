<template>
    <div>
        <v-container fluid>
            <v-row>
                <v-col sm="3" xs="12">
                    <v-select :items="years" label="Year" v-model="year"></v-select>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12"  style="max-height: 400px;">
                    <v-card
                        class="mx-auto"
                        max-width="400"
                    >
                        <v-card-title>Total income and expenses</v-card-title>
                        <v-card-subtitle>Year {{year}}</v-card-subtitle>
                        <income-expenses-chart
                            :year="year"
                        />
                    </v-card>
                </v-col>
            </v-row>
        </v-container>

    </div>
</template>

<script>
    import IncomeExpensesChart from "./charts/IncomeExpensesChart";
    export default {
        name: 'Reports',
        data: () => ({
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
            ]
        }),
        components: {IncomeExpensesChart}
    }
</script>
