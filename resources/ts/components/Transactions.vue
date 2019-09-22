<template>
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
                <v-dialog v-model="dialog" max-width="500px">
                    <template v-slot:activator="{ on }">
                        <v-btn color="primary" dark class="mb-2" v-on="on">New transaction</v-btn>
                    </template>
                    <v-card>
                        <v-card-title>
                            <span class="headline">{{ formTitle }}</span>
                        </v-card-title>

                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12" md="12" lg="6">
                                        <v-text-field v-model="editedItem.description" label="Description"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="12" lg="6">
                                        <v-text-field v-model="editedItem.account_id" label="Account"></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-container>
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
        <template v-slot:no-data>
            No tranasctions yet.
        </template>
    </v-data-table>
</template>

<script>
import TransactionsApi from '../apis/transactionsApi';
export default {
  name: 'Transactions',
  data: () => ({
    dialog: false,
    headers: [
        { text: 'Date',  value: 'date' },
        { text: 'Account', value: 'account.name' },
        { text: 'Description', value: 'description' },
        { text: 'Category', value: 'category.name' },
        { text: 'Actions', value: 'action', sortable: false },
    ],
    loading: true,
    items: [],
    totalItems: 0,
    options: {},
    editedItem: {}
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
            date: new Date(),
            description: '',
            account_id: ''
        }
    },
    isEditing() {
        return this.editedItem.id;
    },
    getDataFromApi () {
        this.loading = true;
        return TransactionsApi.list(this.options).then(data => {
            this.loading = false;
            this.items = data.items;
            this.totalItems = data.total;
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
