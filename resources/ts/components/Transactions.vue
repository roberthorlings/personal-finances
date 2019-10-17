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

                    <v-edit-dialog
                        @open="editedFilters = {...filters}"
                        @save="filters = {...editedFilters}"
                    >
                        <v-chip
                            v-for="filter in filterDescription"
                            :key="filter.label"
                            v-if="filterDescription.length"
                            :color="filter.color"
                            text-color="white"
                            class="mx-2"
                        >
                            <v-avatar left>
                                <v-icon>{{filter.icon}}</v-icon>
                            </v-avatar>
                            {{filter.name}}
                        </v-chip>
                        <span v-if="!filterDescription.length">
                            No filters
                        </span>

                        <template v-slot:input>
                            <div class="my-4">
                                <v-autocomplete
                                    v-model="editedFilters.category_id"
                                    :items="categoryFilters"
                                    item-value="id"
                                    item-text="text"
                                    label="Category"
                                ></v-autocomplete>
                                <v-autocomplete
                                    v-model="editedFilters.account_id"
                                    :items="accountFilters"
                                    item-value="id"
                                    item-text="name"
                                    label="Account"
                                ></v-autocomplete>
                            </div>
                        </template>
                    </v-edit-dialog>
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
                                <TransactionForm
                                    v-model="editedItem"
                                    :categories="categories"
                                    :accounts="accounts"
                                >
                                </TransactionForm>
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
            <template v-slot:item.category.name="props">
                <v-edit-dialog
                    :return-value.sync="props.item.category_id"
                    @save="updateCategory(props.item.id, props.item.category_id)"
                >
                    <div :ref="'category-name-' + props.item.id">
                        {{ props.item.category ? props.item.category.name : '' }}
                    </div>
                    <template v-slot:input>
                        <v-autocomplete
                            v-model="props.item.category_id"
                            :items="categories"
                            item-value="id"
                            item-text="text"
                            label="Category"
                        ></v-autocomplete>
                    </template>
                </v-edit-dialog>
            </template>
            <template v-slot:no-data>
                No transactions yet.
            </template>
        </v-data-table>
        <p class="text-right body-2 mt-2">Press 'n' to filter on no category, 'a' to sort on amount, 'e' to categorize the first item in the list</p>
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
import CategoriesApi from "../apis/categoriesApi";
import AccountsApi from '../apis/accountsApi';
import {toFlatList} from "../utils";

const ID_NO_FILTER = -1;
const ID_NO_CATEGORY = 0;

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
    filters: {
        category_id: ID_NO_FILTER,
        account_id: ID_NO_FILTER
    },
    categories: [],
    accounts: [],
    editedItem: {},
    editedFilters: {}
}),

computed: {
    formTitle () {
        return this.isEditing() ? 'Edit transaction' : 'New transaction'
    },
    categoryFilters () {
        return [
            {id: ID_NO_FILTER, text: '[All categories]'},
            {id: ID_NO_CATEGORY, text: '[No category]', name: 'No category'},
            ...this.categories
        ];
    },
    accountFilters () {
        return [
            {id: ID_NO_FILTER, name: '[All accounts]'},
            ...this.accounts
        ];
    },
    filterDescription () {
        const filters = [];
        if(this.filters.category_id !== ID_NO_FILTER) {
            filters.push({
                ...this.categoryFilters.find(f => f.id === this.filters.category_id),
                label: 'Category',
                icon: 'category',
                color: 'indigo'
            });
        }
        if(this.filters.account_id !== ID_NO_FILTER) {
            filters.push({
                ...this.accountFilters.find(f => f.id === this.filters.account_id),
                label: 'Account',
                icon: 'account_balance',
                color: 'orange'
            });
        }

        return filters;
    }
},

watch: {
    dialog (val) {
        val || this.close()
    },
    filters: {
        handler() {
            this.getDataFromApi();
        },
        deep: true
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
    this.getAccounts();
    this.getCategories();
    this.resetForm();
},

    created() {
        window.addEventListener("keypress", this.onKeyPress);
    },

    destroyed() {
      window.removeEventListener("keypress", this.onKeyPress);
    },

methods: {
    onKeyPress(e) {
        const key = String.fromCharCode(e.keyCode);

        if(key.toLowerCase() === 'e') {
            this.categorizeFirst();
        }
        if(key.toLowerCase() === 'n') {
            this.filters.category_id = ID_NO_CATEGORY;
        }

        if(key.toLowerCase() === 'a') {
            if(this.options.sortBy[0] === 'amount') {
                this.options.sortDesc[0] = !this.options.sortDesc[0]
            } else {
                this.options.sortBy[0] = 'amount';
            }
            this.getDataFromApi();
        }
    },
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
    appyFilters() {
      this.filters.category_id = this.editedFilters.category_id;
      this.filters.account_id = this.editedFilters.account_id;
    },
    getDataFromApi () {
        this.loading = true;
        this.error = false;

        const filters = {};
        if(this.filters.category_id !== ID_NO_FILTER) filters.category_id = this.filters.category_id;
        if(this.filters.account_id !== ID_NO_FILTER) filters.account_id = this.filters.account_id;

        return TransactionsApi.list({...this.options, filters})
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
    getCategories () {
        return CategoriesApi.tree()
            .then(data => {
                this.categories = toFlatList(data);
            })
            .catch(e => {
                console.error("Error while loading categories");
                this.$emit('error', {type: 'categories', message: e.message});
            });
    },
    getAccounts () {
        return AccountsApi.list()
            .then(data => {
                this.accounts = data.items;
            })
            .catch(e => {
                console.error("Error while loading accounts");
                this.$emit('error', {type: 'accounts', message: e.message});
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

    categorizeFirst() {
        if(this.items.length) {
            const firstId = this.items[0].id
            this.$refs['category-name-' + firstId].click();
        }
    },

    updateCategory(transactionId, categoryId) {
        TransactionsApi.store({id: transactionId, category_id: categoryId})
            .then(() => this.getDataFromApi());
    },
  },
}
</script>
