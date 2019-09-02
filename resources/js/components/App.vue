<template>
  <div class="page-container">
    <md-app md-mode="reveal">
      <md-app-toolbar class="md-primary">
        <md-button class="md-icon-button" @click="menuVisible = !menuVisible">
          <md-icon>menu</md-icon>
        </md-button>
        <span class="md-title">Personal finance manager</span>
      </md-app-toolbar>

      <md-app-drawer :md-active.sync="menuVisible">
        <md-toolbar class="md-transparent" md-elevation="0">Navigation</md-toolbar>

        <md-list>
          <md-list-item to="/" exact>
            <md-icon>show_chart</md-icon>
            <span class="md-list-item-text">Overview</span>
          </md-list-item>

          <md-divider class="md-inset"></md-divider>

          <md-list-item to="/categories">
            <md-icon>category</md-icon>
            <span class="md-list-item-text">Categories</span>
          </md-list-item>

          <md-list-item>
            <md-icon>account_balance</md-icon>
            <span class="md-list-item-text">Accounts</span>
          </md-list-item>

          <md-list-item>
            <md-icon>money</md-icon>
            <span class="md-list-item-text">Transactions</span>
          </md-list-item>

        </md-list>
      </md-app-drawer>

      <md-app-content>
        <router-view>
        </router-view>
      </md-app-content>
    </md-app>
  </div>
</template>

<style lang="scss" scoped>
  .md-app {
    border: 1px solid rgba(#000, .12);
  }

   // Demo purposes only
  .md-drawer {
    width: 230px;
    max-width: calc(100vw - 125px);
  }
</style>

<script>
export default {
  name: 'App',
  data: () => ({
    menuVisible: false
  })
}
</script>
