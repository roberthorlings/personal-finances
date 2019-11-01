<template>
    <v-edit-dialog
        @open="editedFilters = {...value}"
        @save="save()"
        large
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
                    auto-select-first
                ></v-autocomplete>
                <v-autocomplete
                    v-model="editedFilters.account_id"
                    :items="accountFilters"
                    item-value="id"
                    item-text="name"
                    label="Account"
                ></v-autocomplete>
                <DateRangeInput
                    v-model="editedFilters.date_range"
                    label="Date range"
                ></DateRangeInput>
            </div>
        </template>
    </v-edit-dialog>
</template>

<script>
    export const ID_NO_FILTER = -1;
    export const ID_NO_CATEGORY = 0;

    export default {
        name: 'TransactionFilters',
        data: () => ({
            editedFilters: {}
        }),
        props: {
            value: Object,
            categories: Array,
            accounts: Array
        },

        computed: {
            categoryFilters() {
                return [
                    {id: ID_NO_FILTER, text: '[All categories]'},
                    {id: ID_NO_CATEGORY, text: '[No category]', name: 'No category'},
                    ...this.categories
                ];
            },
            accountFilters() {
                return [
                    {id: ID_NO_FILTER, name: '[All accounts]'},
                    ...this.accounts
                ];
            },
            filterDescription() {
                const filters = [];
                if (this.value.category_id !== ID_NO_FILTER) {
                    filters.push({
                        ...this.categoryFilters.find(f => f.id === this.value.category_id),
                        label: 'Category',
                        icon: 'category',
                        color: 'indigo'
                    });
                }
                if (this.value.account_id !== ID_NO_FILTER) {
                    filters.push({
                        ...this.accountFilters.find(f => f.id === this.value.account_id),
                        label: 'Account',
                        icon: 'account_balance',
                        color: 'orange'
                    });
                }

                if (this.value.date_range && this.value.date_range.length > 0) {
                    filters.push({
                        name: this.value.date_range.join(' ~ '),
                        label: 'Date range',
                        icon: 'date_range',
                        color: 'yellow'
                    });
                }

                return filters;
            }
        },

        methods: {
            save() {
                this.$emit('input', this.editedFilters);
            },
        },
    }
</script>
