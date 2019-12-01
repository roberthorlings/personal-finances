<template>
    <div>
        <span v-if="loading">Loading...</span>
        <slot
            v-if="!loading"
            v-bind:stats="stats"
        />
    </div>
</template>
<script>
    import categoriesApi from "../apis/categoriesApi";

    export default {
        name: 'WithStats',
        data: () => ({
            loading: true,
            stats: []
        }),
        mounted() {
            this.updateData();
        },
        methods: {
            updateData: async function () {
                this.loading = true;
                try {
                    this.stats = await categoriesApi.stats({
                        year: this.year || undefined,
                        month: this.month || undefined
                    });
                    this.loading = false
                } catch (e) {
                    console.error(e)
                }
            }
        },
        props: {
            year: {type: Number},
            month: {type: Number}
        },
        watch: {
            year: function () {
                this.updateData();
            },
            month: function () {
                this.updateData();
            }
        }
    }
</script>
