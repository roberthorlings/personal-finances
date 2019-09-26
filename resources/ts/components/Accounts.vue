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
            <template #top>
                <v-toolbar flat color="white">
                    <v-toolbar-title>Accounts</v-toolbar-title>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <div class="flex-grow-1"></div>
                    <v-btn text icon color="primary" @click="getDataFromApi">
                        <v-icon>mdi-cached</v-icon>
                    </v-btn>
                    <v-dialog v-model="dialog" max-width="500px">
                        <template v-slot:activator="{ on }">
                            <v-btn color="primary" dark class="mb-2" v-on="on">New account</v-btn>
                        </template>
                        <v-card>
                            <v-card-title>
                                <span class="headline">{{ formTitle }}</span>
                            </v-card-title>

                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="6">
                                            <v-text-field v-model="editedItem.name" label="Account name"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="12" lg="6">
                                            <v-text-field v-model="editedItem.iban" label="IBAN"></v-text-field>
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
                No accounts yet.
            </template>
        </v-data-table>

        <v-snackbar
            v-model="error"
            :timeout="4000"
        >
            An error occurred while loading the accounts.
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
import AccountsApi from '../apis/accountsApi';
export default {
  name: 'Accounts',
  data: () => ({
    dialog: false,
    error: false,
    loading: true,
    headers: [
      {
        text: 'Name',
        align: 'left',
        value: 'name',
      },
      { text: 'IBAN', value: 'iban' },
      { text: 'Last updated', value: 'updated_at' },
      { text: 'Actions', value: 'action', sortable: false },
    ],
    items: [],
    totalItems: 0,
    options: {},
    editedItem: {}
}),

computed: {
    formTitle () {
        return this.isEditing() ? 'Edit account' : 'New account'
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
            name: '',
            iban: ''
        }
    },
    isEditing() {
        return this.editedItem.id;
    },
    getDataFromApi () {
        this.loading = true;
        this.error = false;
        return AccountsApi.list(this.options)
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

    addItem() {
        this.resetForm();
        this.dialog = true
    },

    editItem (item) {
        this.editedItem = {...item};
        this.dialog = true
    },

    deleteItem (item) {
        confirm('Are you sure you want to delete this item?') && AccountsApi.destroy(item.id).then(() => this.getDataFromApi());
    },

    close () {
        this.dialog = false;
        this.resetForm();
    },

    save () {
        AccountsApi.store(this.editedItem).then(() => this.getDataFromApi());
        this.close();
    },
  },
}
</script>
