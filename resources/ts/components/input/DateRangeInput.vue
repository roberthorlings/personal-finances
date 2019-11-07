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
                :value="dateRangeText"
                :label="label"
                append-icon="event"
                readonly
                v-on="on"
            ></v-text-field>
        </template>
        <v-date-picker
            ref="picker"
            v-model="currentValue"
            :max="new Date().toISOString().substr(0, 10)"
            min="2000-01-01"
            range
        >
            <v-spacer></v-spacer>

            <v-menu offset-y>
                <template v-slot:activator="{ on }">
                    <v-btn
                        color="secondary"
                        text
                        v-on="on"
                    >
                        Presets
                    </v-btn>
                </template>
                <v-list>
                    <v-list-item
                        v-for="(preset, index) in presets"
                        :key="index"
                        @click="propagate(preset.value)"
                    >
                        <v-list-item-title>{{ preset.title }}</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
            <v-btn text color="primary" @click="propagate([])">Clear</v-btn>
            <v-btn text color="primary" @click="menu = false">Cancel</v-btn>
            <v-btn text color="primary" @click="propagate(currentValue)">OK</v-btn>
        </v-date-picker>
    </v-menu>

</template>
<script>
    import DateRange from "../../DateRange";

    export default {
        name: 'DateRangeInput',
        props: {
            value: {type: Array, default: []},
            label: {type: String}
        },
        data: () => ({
            menu: false,
            currentValue: []
        }),
        watch: {
            value: function (newVal) {
                this.currentValue = newVal;
            }
        },
        computed: {
            dateRangeText() {
                return this.value.join(' ~ ');
            },
            presets() {
                const presets = [
                    {title: 'This year', value: DateRange.current.year},
                    {title: 'Last year', value: DateRange.last.year},
                    {title: 'This month', value: DateRange.current.month},
                    {title: 'Last month', value: DateRange.last.month}
                ];

                if (this.value && this.value.length > 0) {
                    const [y, m] = this.value[0].split('-').map(s => parseInt(s, 10));
                    const relativePresets = [
                        {title: 'Previous year', value: DateRange.previous.year(y)},
                        {title: 'Next year', value: DateRange.next.year(y)},
                        {title: 'Previous month', value: DateRange.previous.month(y, m)},
                        {title: 'Next month', value: DateRange.next.month(y, m)}
                    ];

                    presets.push(...relativePresets)
                }

                return presets;
            }
        },
        methods: {
            propagate(newValue) {
                this.$emit("input", newValue.sort());
                this.menu = false;
            }
        },
    }
</script>
