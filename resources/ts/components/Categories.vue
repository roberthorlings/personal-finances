<template>
    <v-sheet
        elevation="1"
        color="white"
    >
        <v-toolbar flat color="white">
            <v-toolbar-title>Categories</v-toolbar-title>
            <v-divider class="mx-4" inset vertical></v-divider>
            <div class="flex-grow-1"></div>
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
        <v-treeview
            dense
            :items="items"
        ></v-treeview>
    </v-sheet>
</template>

<script>
    import CategoriesApi from '../apis/categoriesApi';
    export default {
        name: 'Categories',
        data: () => ({
            dialog: false,
            loading: true,
            items: [],
            editedItem: {}
        }),

        computed: {
            formTitle () {
                return this.isEditing() ? 'Edit category' : 'New category'
            },
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
                    parent_id: undefined
                }
            },
            isEditing() {
                return this.editedItem.id;
            },
            getDataFromApi () {
                this.loading = true;
                return CategoriesApi.tree().then(data => {
                    this.loading = false;
                    this.items = data;
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
                confirm('Are you sure you want to delete this category?') && CategoriesApi.destroy(item.id).then(() => this.getDataFromApi());
            },

            close () {
                this.dialog = false;
                this.resetForm();
            },

            save () {
                CategoriesApi.store(this.editedItem).then(() => this.getDataFromApi());
                this.close();
            },
        },
    }
</script>
