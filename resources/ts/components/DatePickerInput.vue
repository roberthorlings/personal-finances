<template>
    <v-menu
        ref="menu"
        v-model="menu"
        :close-on-content-click="false"
        transition="scale-transition"
        offset-y
        min-width="290px"
    >
        <template v-slot:activator="{ on }">
            <v-text-field
                :value="value"
                :label="label"
                append-icon="event"
                readonly
                v-on="on"
            ></v-text-field>
        </template>
        <v-date-picker
            ref="picker"
            :value="value"
            :max="new Date().toISOString().substr(0, 10)"
            min="2000-01-01"
            @change="save"
        ></v-date-picker>
    </v-menu>

</template>
<script>
    export default {
        name: 'DatePickerInput',
        props: ['value', 'label'],
        data: () => ({
            menu: false,
        }),
        methods: {
            save (date) {
                this.menu = false;
                this.$emit("input", date);
            }
        },
    }
</script>
