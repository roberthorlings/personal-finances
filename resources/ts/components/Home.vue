<template>
    <div>
        <v-container fluid>
            <v-row>
                <v-col sm="3" xs="12">
                    <v-select :items="years" label="Year" v-model="year"></v-select>
                </v-col>
                <v-col sm="3" xs="12">
                    <v-select :items="months" label="Month" v-model="month"></v-select>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12"  style="max-height: 400px;">
                    <category-bar-chart
                        :year="year"
                        :month="month"
                    >

                    </category-bar-chart>
                </v-col>
            </v-row>
        </v-container>

    </div>
</template>

<script>
    import CategoryBarChart from "./containers/CategoryBarChart";
    export default {
        name: 'Home',
        data: () => ({
            year: null,
            month: null,
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
            months: [
                {text: 'all months', value: null},
                {text: 'January', value: 1},
                {text: 'February', value: 2},
                {text: 'March', value: 3},
                {text: 'April', value: 4},
                {text: 'May', value: 5},
                {text: 'June', value: 6},
                {text: 'July', value: 7},
                {text: 'August', value: 8},
                {text: 'September', value: 9},
                {text: 'October', value: 10},
                {text: 'November', value: 11},
                {text: 'December', value: 12},
            ]

        }),
        components: {CategoryBarChart}
    }
</script>
