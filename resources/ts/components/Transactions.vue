<template>
    <div>
        <v-data-table
            :headers="headers"
            :items="items"
            :footer-props="{
                itemsPerPageOptions: [10, 20, 50, -1]
            }"
            :options.sync="options"
            :server-items-length="totalItems"
            :loading="loading"
            class="elevation-1"
        >
            <template v-slot:top>
                <v-toolbar flat color="white">
                    <v-toolbar-title>Transactions</v-toolbar-title>
                    <v-divider class="mx-4" inset vertical></v-divider>

                    <TransactionFilters
                        v-model="filters"
                        :categories="categories"
                        :accounts="accounts"
                    ></TransactionFilters>

                    <v-divider class="mx-4" inset vertical v-if="skip"></v-divider>
                    <span v-if="skip" @click="resetSkipped">
                        Skipping {{skip}} item(s)
                    </span>

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
                                <v-btn color="blue darken-1" text @click="save">Save &amp; repeat</v-btn>
                                <v-btn color="blue darken-1" text @click="saveAndClose">Save &amp; close</v-btn>
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
            <template v-slot:item.opposing_account_name="{ item }">
                {{ item.opposing_account.name }}
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
                            auto-select-first
                        ></v-autocomplete>
                    </template>
                </v-edit-dialog>
            </template>
            <template v-slot:no-data>
                No transactions yet.
            </template>
        </v-data-table>
        <KeyboardShortcuts :shortcuts="keyboardShortcuts">
            <p class="text-right body-2 mt-2">
                's, a' sorts on amount, 's, d' sort on date, 's, c' sorts on category name, 's u' sorts on date updated<br />
                'f, c' filters on no category, 'f, a' shows all<br />
                'd y' filters on this year, 'd m' filters on this month, 'd a' clears date filter<br />
                'd, ,' navigates one month back, 'd, .' navigates one month ahead<br />
                Press 'e' to categorize the first item in the list<br />
                Press 'k' to skip the first entry in the list, 'r' to reset to zero<br />
            </p>
        </KeyboardShortcuts>
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
    import TransactionFilters, {ID_NO_CATEGORY, ID_NO_FILTER} from "./TransactionFilters";
    import CategoriesApi from "../apis/categoriesApi";
    import AccountsApi from '../apis/accountsApi';
    import {toFlatList} from "../utils";
    import KeyboardShortcuts from "./input/KeyboardShortcuts";
    import DateRange from "../DateRange";

    export default {
        name: 'Transactions',
        components: {KeyboardShortcuts, ImportTransactions, TransactionForm, TransactionFilters},
        data: () => ({
            dialog: false,
            error: false,
            loading: true,
            headers: [
                {text: 'Date', value: 'date'},
                {text: 'Account', value: 'account.name'},
                {text: 'Opposing account', value: 'opposing_account_name'},
                {text: 'Description', value: 'description'},
                {text: 'Category', value: 'category.name'},
                {text: 'Amount', value: 'amount', align: 'end'},
                {text: 'Actions', value: 'action', sortable: false},
            ],
            items: [],
            totalItems: 0,
            skip: 0,
            options: {},
            filters: {
                category_id: ID_NO_FILTER,
                account_id: ID_NO_FILTER,
                date_range: []
            },
            categories: [],
            accounts: [],
            editedItem: {},

            keyboardShortcuts: []
        }),

        computed: {
            formTitle() {
                return this.isEditing() ? 'Edit transaction' : 'New transaction'
            }
        },

        watch: {
            dialog(val) {
                val || this.close()
            },
            filters: {
                handler() {
                    this.getDataFromApi();
                },
                deep: true
            },
            options: {
                handler() {
                    this.getDataFromApi();
                },
                deep: true,
            },
        },

        mounted() {
            this.getDataFromApi();
            this.getAccounts();
            this.getCategories();
            this.resetForm();

            this.keyboardShortcuts = [
                {sequence: ['e'], callback: this.categorizeFirst},
                {sequence: ['k'], callback: this.skipItem},
                {sequence: ['r'], callback: this.resetSkipped},
                {sequence: ['f', 'c'], callback: this.filterNoCategory},
                {sequence: ['f', 'a'], callback: this.filterAllCategories},
                {sequence: ['d', ','], callback: this.filterPreviousMonth},
                {sequence: ['d', '.'], callback: this.filterNextMonth},
                {sequence: ['d', 'y'], callback: this.filterThisYear},
                {sequence: ['d', 'm'], callback: this.filterThisMonth},
                {sequence: ['d', 'a'], callback: this.filterAllDates},
                {sequence: ['s', 'a'], callback: () => this.applySort('amount')},
                {sequence: ['s', 'c'], callback: () => this.applySort('category.name')},
                {sequence: ['s', 'd'], callback: () => this.applySort('date')},
                {sequence: ['s', 'u'], callback: () => this.applySort('updated_at')},
            ];
        },

        methods: {
            resetForm() {
                this.editedItem = {
                    date: new Date().toISOString().substr(0, 10),
                    description: '',
                    account: {}
                }
            },
            isEditing() {
                return this.editedItem.id;
            },
            getDataFromApi() {
                this.loading = true;
                this.error = false;

                const filters = {};
                if (this.filters.category_id !== ID_NO_FILTER) filters.category_id = this.filters.category_id;
                if (this.filters.account_id !== ID_NO_FILTER) filters.account_id = this.filters.account_id;
                if (this.filters.date_range) {
                    filters.date_start = this.filters.date_range[0];
                    if (this.filters.date_range.length == 1) {
                        filters.date_end = this.filters.date_range[0];
                    } else if (this.filters.date_range.length > 1) {
                        filters.date_end = this.filters.date_range[1];
                    }
                }

                return TransactionsApi.list({...this.options, filters, skip: this.skip})
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
            getCategories() {
                return CategoriesApi.tree()
                    .then(data => {
                        this.categories = toFlatList(data);
                    })
                    .catch(e => {
                        console.error("Error while loading categories");
                        this.$emit('error', {type: 'categories', message: e.message});
                    });
            },
            getAccounts() {
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
                return TransactionsApi.generateStats()
                    .finally(() => {
                        this.loading = false;
                    });
            },
            addItem() {
                this.resetForm();
                this.dialog = true
            },

            editItem(item) {
                this.editedItem = {...item};
                this.dialog = true
            },

            deleteItem(item) {
                confirm('Are you sure you want to delete this item?') && TransactionsApi.destroy(item.id).then(() => this.getDataFromApi());
            },

            close() {
                this.dialog = false;
                this.resetForm();
            },

            save() {
                TransactionsApi.store(this.editedItem).then(() => this.getDataFromApi());
            },

            saveAndClose() {
                this.save();
                this.close();
            },

            categorizeFirst() {
                if (this.items.length) {
                    const firstId = this.items[0].id;
                    this.$refs['category-name-' + firstId].click();
                }
            },

            filterNoCategory() {
                this.filters.category_id = ID_NO_CATEGORY;
            },

            filterAllCategories() {
                this.filters.category_id = ID_NO_FILTER;
            },

            filterAllDates() {
                this.filters.date_range = [];
            },

            filterThisYear() {
                this.filters.date_range = DateRange.current.year;
            },
            filterThisMonth() {
                this.filters.date_range = DateRange.current.month;
            },

            filterPreviousMonth() {
                if(!this.filters.date_range || this.filters.date_range.length === 0)
                    return;

                const [y, m] = this.filters.date_range[0].split('-').map(s => parseInt(s, 10));
                this.filters.date_range = DateRange.previous.month(y, m);
            },

            filterNextMonth() {
                if(!this.filters.date_range || this.filters.date_range.length === 0)
                    return;

                const [y, m] = this.filters.date_range[0].split('-').map(s => parseInt(s, 10));
                this.filters.date_range = DateRange.next.month(y, m);
            },

            skipItem(){
                this.skip = (this.skip || 0) + 1
                this.getDataFromApi();
            },

            resetSkipped() {
                this.skip = undefined;
                this.getDataFromApi();
            },

            applySort(column) {
                if (this.options.sortBy[0] === column) {
                    this.options.sortDesc[0] = !this.options.sortDesc[0]
                } else {
                    this.options.sortBy[0] = column;
                }
                this.getDataFromApi();
            },

            updateCategory(transactionId, categoryId) {
                TransactionsApi.store({id: transactionId, category_id: categoryId})
                    .then(() => this.getDataFromApi());
            },
        },
    }
</script>
