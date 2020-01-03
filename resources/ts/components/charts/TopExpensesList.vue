<template>
    <v-list v-if="!loadingTransactions">
        <v-list-item two-line v-for="transaction in transactions" :key="transaction.id">
            <v-list-item-avatar>
                <span :title="transaction.date"><v-icon>mdi-calendar</v-icon></span>
            </v-list-item-avatar>

            <v-list-item-content>
                <v-list-item-title>{{transaction.description}}</v-list-item-title>
                <v-list-item-subtitle>{{transaction.category.name}}</v-list-item-subtitle>
            </v-list-item-content>

            <v-list-item-action>
                <v-chip dark color="red">{{ transaction.amount | toCurrency }}</v-chip>
            </v-list-item-action>
        </v-list-item>
    </v-list>
</template>

<script>
    import transactionsApi from "../../apis/transactionsApi";
    import DateRange from "../../DateRange";

    export default {
        name: 'TopExpensesList',
        data: () => ({
            loadingTransactions: false,
            transactions: [],
        }),
        mounted() {
            this.getTransactions();
        },
        methods: {
            getTransactions: function() {
                const options = {
                    sortBy: ['amount'],
                    sortDesc: [false],

                    // As we filter out the transfers, we are never sure how much transactions to retrieve
                    // For that reason, load enough
                    itemsPerPage: this.numTransactions * 10
                };

                if(this.year) {
                    const range = DateRange.year(this.year);
                    options.filters = {
                        date_start: range[0],
                        date_end: range[1]
                    };
                }

                this.loadingTransactions = true;
                return transactionsApi.list(options)
                    .then(data => {
                        this.loadingTransactions = false;

                        // Filter transactions that are categorized as transfer between accounts
                        this.transactions = data.items
                            .filter(transaction => !transaction.category || transaction.category.key != 'overboekingen')
                            .slice(0, this.numTransactions);
                    })
                    .catch(e => {
                        console.error("Error while loading transactions", e);
                        this.$emit('error', {type: 'transactions', message: e.message});
                    });
            }
        },
        props: {
            year: { type: Number },
            numTransactions: { type: Number, default: 10 }
        },
        watch: {
            year: function() { this.getTransactions(); }
        }

    }
</script>
