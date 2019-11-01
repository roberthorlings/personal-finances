<template>
    <v-container>
        <v-row>
            <v-col cols="12" md="12" lg="6">
                <DatePickerInput
                    :value="value.date"
                    @input="update('date', $event)"
                    label="Date">
                </DatePickerInput>
            </v-col>
            <v-col cols="12" md="12" lg="6">
                <v-text-field
                    type="number"
                    :value="value.amount"
                    @input="update('amount', $event)"
                    prefix="€"
                    label="Amount">
                </v-text-field>
            </v-col>
            <v-col cols="12" lg="12">
                <v-text-field
                    :value="value.description"
                    @input="update('description', $event)"
                    label="Description">
                </v-text-field>
            </v-col>
            <v-col cols="12" md="12" lg="6">
                <v-autocomplete
                    :value="value.account_id"
                    @input="update('account_id', $event)"
                    :items="accounts"
                    item-value="id"
                    item-text="name"
                    label="Account"
                ></v-autocomplete>
            </v-col>
            <v-col cols="12" md="12" lg="6">
                <v-autocomplete
                    v-model="value.category_id"
                    @input="update('category_id', $event)"
                    :items="categories"
                    item-value="id"
                    item-text="text"
                    label="Category"
                ></v-autocomplete>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
    export default {
        name: 'TransactionForm',
        methods: {
            update(key, value) {
                this.$emit('input', {...this.value, [key]: value})
            },
        },

        props: {
            value: Object,
            categories: Array,
            accounts: Array
        }
    }
</script>
