<template>
    <v-container fluid>
        <v-row>
            <v-col sm="3" xs="12">
                <v-select :items="years" label="Year" v-model="year"></v-select>
            </v-col>
        </v-row>
        <v-row>
            <v-card
                class="mx-auto"
            >
                <v-card-title>Transactions per period</v-card-title>
                <with-all-stats
                    :year="year"
                    v-slot="slotProps">
                    <v-simple-table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th v-for="bin in slotProps.bins">{{bin.text}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stat in slotProps.stats">
                                <td>{{stat.text}}</td>
                                <td v-for="value in stat.total"><v-chip :color="value < 0 ? 'red' : ' green'" dark>{{ value | toCurrency }}</v-chip></td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                </with-all-stats>
            </v-card>
        </v-row>
    </v-container>
</template>

<script>
    import WithAllStats from "./WithAllStats";

    export default {
        name: 'AllCategoriesReport',
        components: {WithAllStats},
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
            ],
        }),
    }
</script>
