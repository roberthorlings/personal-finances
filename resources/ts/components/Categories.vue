<template>
    <v-sheet
        elevation="1"
        color="white"
    >
        <v-toolbar flat color="white">
            <v-toolbar-title>Categories</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <div class="flex-grow-1"></div>
            <v-btn text icon color="primary" @click="getDataFromApi">
                <v-icon>mdi-cached</v-icon>
            </v-btn>
            <v-dialog v-model="dialog" max-width="500px">
                <template v-slot:activator="{ on }">
                    <v-btn color="primary" dark class="mb-2" v-on="on">New category</v-btn>
                </template>
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ formTitle }}</span>
                    </v-card-title>

                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" md="12" lg="6">
                                    <v-text-field v-model="editedItem.name" label="Category name"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="12" lg="6">
                                    <v-text-field v-model="editedItem.key" label="Category key"></v-text-field>
                                </v-col>
                                <v-col cols="12" md="12" lg="6">
                                    <v-autocomplete
                                        v-model="editedItem.parent_id"
                                        :items="autoCompleteItems"
                                        item-value="id"
                                    ></v-autocomplete>
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
        <v-treeview
            dense
            activatable
            :items="items"
        >
            <template v-slot:append="{item}">
                <v-icon
                    small
                    class="mr-2"
                    @click="addChild(item)"
                >
                    add
                </v-icon>
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

        </v-treeview>
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
    </v-sheet>
</template>

<script>
    import CategoriesApi from '../apis/categoriesApi';
    import {toFlatList} from "../utils";
    export default {
        name: 'Categories',
        data: () => ({
            dialog: false,
            error: false,
            loading: true,
            items: [],
            editedItem: {},
            formTitle: 'New category'
        }),

        computed: {
            autoCompleteItems() {
                return toFlatList(this.items);
            }
        },

        watch: {
            dialog (val) {
                val || this.close()
            }
        },

        mounted () {
            this.getDataFromApi();
            this.resetForm();
        },

        methods: {
            resetForm () {
                this.editedItem = {
                    name: '',
                    key: '',
                    parent_id: undefined
                }
            },
            getDataFromApi () {
                this.loading = true;
                this.error = false;
                return CategoriesApi.tree()
                    .then(data => {
                        this.loading = false;
                        this.items = data;
                    })
                    .catch(e => {
                        this.loading = false;
                        this.error = true;
                    });
            },
            addItem() {
                this.formTitle = 'New category';
                this.resetForm();
                this.dialog = true
            },

            addChild (item) {
                this.formTitle = 'Add child category to ' + item.name;
                this.editedItem = {parent_id: item.id};
                this.dialog = true
            },

            editItem (item) {
                this.formTitle = `Edit category ${item.name} (${item.id})`;
                this.editedItem = {...item};
                this.dialog = true
            },

            deleteItem (item) {
                confirm('Are you sure you want to delete this category?') && CategoriesApi.destroy(item.id).then(() => this.getDataFromApi());
            },

            close () {
                this.dialog = false;
                this.resetForm();
            },

            save () {
                console.log(this.editedItem);
                CategoriesApi.store(this.editedItem).then(() => this.getDataFromApi());
                this.close();
            },
        },
    }
</script>
