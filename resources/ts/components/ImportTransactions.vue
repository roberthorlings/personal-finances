<template>
    <v-dialog v-model="dialog" max-width="500px">
        <template v-slot:activator="{ on }">
            <v-btn color="primary" text icon v-on="on">
                <v-icon>mdi-import</v-icon>
            </v-btn>
        </template>
        <v-card>
            <v-card-title>
                <span class="headline">Import transactions</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-row>
                        <v-col cols="12" md="12">
                            <v-file-input show-size label="File input" v-model="selectedFile"></v-file-input>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-autocomplete
                                v-model="type"
                                :items="['firefly', 'abn']"
                                label="Import type"
                            ></v-autocomplete>
                        </v-col>
                        <v-col cols="12" md="12">
                            <v-switch v-model="dryRun" label="Dry run"></v-switch>
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <div class="flex-grow-1"></div>
                <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                <v-btn color="blue darken-1" text @click="importTransactions">Import</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>


</template>

<script>
    import TransactionsApi from '../apis/transactionsApi';

    export default {
        name: 'ImportTransactions',
        data: () => ({
            dialog: false,
            type: 'firefly',
            selectedFile: null,
            dryRun: false
        }),
        methods: {
            handleFileSelect() {
                console.log(e)
            },
            importTransactions() {
                TransactionsApi.importTransactions(this.type, this.selectedFile, this.dryRun);
                this.dialog = false;
            },
            close() {
                this.dialog = false;
            }
        }
    }
</script>
