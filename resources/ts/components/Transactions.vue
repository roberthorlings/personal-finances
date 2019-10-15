<template>
    <div>
        <v-data-table
            :headers="headers"
            :items="items"
            :options.sync="options"
            :server-items-length="totalItems"
            :loading="loading"
            class="elevation-1"
        >
            <template v-slot:top>
                <v-toolbar flat color="white">
                    <v-toolbar-title>Transactions</v-toolbar-title>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <div class="flex-grow-1"></div>
                    <v-btn text icon color="primary" @click="getDataFromApi">
                        <v-icon>mdi-cached</v-icon>
                    </v-btn>

                    <v-btn color="primary" text icon @click="computeSummaryStats">
                        <v-icon>mdi-sigma</v-icon>
                    </v-btn>

                    <ImportTransactions></ImportTransactions>

                    <v-dialog v-model="dialog" max-width="500px">
                        <template v-slot:activator="{ on }">
                            <v-btn color="primary" text icon v-on="on">
                                <v-icon>mdi-plus</v-icon>
                            </v-btn>
                        </template>
                        <v-card>
                            <v-card-title>
                                <span class="headline">{{ formTitle }}</span>
                            </v-card-title>

                            <v-card-text>
                                <TransactionForm v-model="editedItem"></TransactionForm>
                            </v-card-text>

                            <v-card-actions>
                                <div class="flex-grow-1"></div>
                                <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                                <v-btn color="blue darken-1" text @click="save">Save</v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </v-toolbar>
            </template>
            <template v-slot:item.action="{ item }">
                <v-icon
                    small
                    class="mr-2"
                    @click="editItem(item)"
                >
                    edit
                </v-icon>
                <v-icon
                    small
                    @click="deleteItem(item)"
                >
                    delete
                </v-icon>
            </template>
            <template v-slot:item.amount="{ item }">
                <v-chip :color="item.amount < 0 ? 'red' : ' green'" dark>{{ item.amount | toCurrency }}</v-chip>
            </template>
            <template v-slot:no-data>
                No transactions yet.
            </template>
        </v-data-table>
        <v-snackbar
            v-model="error"
            :timeout="4000"
        >
            An error occurred while loading the transactions.
            <v-btn
                color="pink"
                text
                @click="this.getDataFromApi"
            >
                Retry
            </v-btn>
        </v-snackbar>
    </div>
</template>

<script>
import TransactionsApi from '../apis/transactionsApi';
import TransactionForm from "./TransactionForm";
import ImportTransactions from "./ImportTransactions";
export default {
  name: 'Transactions',
    components: {ImportTransactions, TransactionForm},
    data: () => ({
    dialog: false,
    error: false,
    loading: true,
    headers: [
        { text: 'Date',  value: 'date' },
        { text: 'Account', value: 'account.name' },
        { text: 'Description', value: 'description' },
        { text: 'Category', value: 'category.name' },
        { text: 'Amount', value: 'amount', align: 'end' },
        { text: 'Actions', value: 'action', sortable: false },
    ],
    items: [],
    totalItems: 0,
    options: {},
    editedItem: {},
}),

computed: {
    formTitle () {
        return this.isEditing() ? 'Edit transaction' : 'New transaction'
    },
},

watch: {
    dialog (val) {
        val || this.close()
    },
    options: {
        handler () {
            this.getDataFromApi();
        },
        deep: true,
    },
},

mounted () {
    this.getDataFromApi();
    this.resetForm();
},

methods: {
    resetForm () {
        this.editedItem = {
            date: new Date().toISOString().substr(0, 10),
            description: '',
            account: {}
        }
    },
    isEditing() {
        return this.editedItem.id;
    },
    getDataFromApi () {
        this.loading = true;
        this.error = false;
        return TransactionsApi.list(this.options)
            .then(data => {
                this.loading = false;
                this.items = data.items;
                this.totalItems = data.total;
            })
            .catch(e => {
                this.loading = false;
                this.error = true;
            });
    },
    computeSummaryStats() {
        this.loading = true;
        return TransactionsApi.stats()
            .finally(() => {
                this.loading = false;
            });
    },
    addItem() {
        this.resetForm();
        this.dialog = true
    },

    editItem (item) {
        this.editedItem = {...item};
        this.dialog = true
    },

    deleteItem (item) {
        confirm('Are you sure you want to delete this item?') && TransactionsApi.destroy(item.id).then(() => this.getDataFromApi());
    },

    close () {
        this.dialog = false;
        this.resetForm();
    },

    save () {
        TransactionsApi.store(this.editedItem).then(() => this.getDataFromApi());
        this.close();
    },
  },
}
</script>
