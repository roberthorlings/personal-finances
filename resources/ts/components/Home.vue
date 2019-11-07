<template>
    <div>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Error quibusdam, non molestias et! Earum magnam,
            similique, quo recusandae placeat dicta asperiores modi sint ea repudiandae maxime? Quae non explicabo,
            neque.</p>

        <div style="max-height: 400px;">
            <category-pie-chart></category-pie-chart>
        </div>
    </div>
</template>

<script>
    import CategoryPieChart from "./containers/CategoryPieChart";
    export default {
        name: 'Home',
        components: {CategoryPieChart}
    }
</script>
