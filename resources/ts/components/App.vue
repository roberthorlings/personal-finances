<template>
    <layout
        title="Personal finances manager"
        :menuItems="menuItems"
    >
    </layout>
</template>

<style lang="scss" scoped>
</style>

<script>
    export default {
        name: 'App',
        data: () => ({
            menuItems: [
                {icon: 'home', text: 'Home', to: '/', exact: true},
                {
                    icon: 'keyboard_arrow_up',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Tables',
                    model: true,
                    children: [
                        {icon: 'category', text: 'Categories', to: '/categories'},
                        {icon: 'account_balance', text: 'Accounts', to: '/accounts'},
                        {icon: 'money', text: 'Transactions', to: '/transactions'},
                        {icon: 'bar_chart', text: 'Reports', to: '/reports'}
                    ],
                }
            ],
        }),
    }
</script>
