<template>
    <div>
        <span v-if="loading">Loading...</span>
        <slot
            v-if="!loading"
            v-bind:stats="stats"
            v-bind:bins="bins"
        />
    </div>
</template>
<script>
    import categoriesApi from "../apis/categoriesApi";
    import {toFlatList} from "../utils";

    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    /**
     * Loads all stats for the given period. If a year is given, stats are returned
     * on monthly basis. If no year is given, stats are returned on yearly basis
     */
    export default {
        name: 'WithAllStats',
        computed: {
            bins() {
                if(this.year) {
                    return [1,2,3,4,5,6,7,8,9,10,11,12]
                        .map(month => ({
                            id: month,
                            filter: { year: this.year, month },
                            text: monthNames[month - 1]
                        }))
                        .filter((v) => !this.isBinEmpty(v.id))

                } else {
                    return [2015, 2016, 2017, 2018, 2019, 2020]
                        .map(year => ({
                            id: year,
                            filter: {year},
                            text: year
                        }))
                        .filter((v) => !this.isBinEmpty(v.id))
                }
            }
        },
        data: () => ({
            loading: true,
            stats: [],
            categories: [],
            emptyBins: []
        }),
        mounted() {
            this.updateData();
        },
        methods: {
            isBinEmpty: function(id) {
                return this.emptyBins.map(b => b.id).includes(id);
            },
            updateData: async function () {
                this.loading = true;

                const categories = await this.getCategories();

                try {
                    // Generate parameters
                    const params = this.bins.map(c => c.filter);

                    // Make calls for each time period
                    const stats = await Promise.all(
                        params.map(paramSet => categoriesApi.stats(paramSet))
                    );

                    // Convert to flat lists
                    const flatLists = stats.map(periodStats => toFlatList(periodStats, '--', item => item.category.name));

                    // See which bins are empty. They can be ignored
                    this.emptyBins = this.bins
                        .map((bin, i) => ({...bin, index: i}))
                        .filter((bin, i) => !stats[i].find(s => s.total != 0));

                    const emptyBinIndices = this.emptyBins.map(b => b.index);

                    // Generate a list per category
                    this.stats = categories.map(category => {
                        const periodCategoryStats = flatLists.map(periodList => periodList.find(p => p.category.id == category.id));

                        // If no statistics for this category were found, skip it
                        if(!periodCategoryStats.find(x => x))
                            return undefined;

                        // If no statistics for this category were found, skip it
                        const filledBinStats = periodCategoryStats.filter((bin, i) => !emptyBinIndices.includes(i));

                        return {
                            ...category,
                            subtotal: filledBinStats.map(s => s ? s.subtotal : 0),
                            total: filledBinStats.map(s => s ? s.total : 0)
                        };
                    });

                    this.loading = false
                } catch (e) {
                    console.error(e)
                }
            },
            getCategories() {
                if(this.categories.length > 0)
                    return Promise.resolve(this.categories);

                return categoriesApi.tree()
                    .then(data => {
                        this.categories = toFlatList(data);
                        return this.categories;
                    })
                    .catch(e => {
                        console.error("Error while loading categories");
                        this.$emit('error', {type: 'categories', message: e.message});
                    });
            },

        },
        props: {
            year: {type: Number}
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
