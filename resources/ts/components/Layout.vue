<template>
    <v-app id="inspire">
        <v-navigation-drawer
            v-model="drawer"
            :clipped="$vuetify.breakpoint.lgAndUp"
            app
        >
            <v-list dense>
                <template v-for="item in menuItems">
                    <v-row
                        v-if="item.heading"
                        :key="item.heading"
                        align="center"
                    >
                        <v-col cols="6">
                            <v-subheader v-if="item.heading">
                                {{ item.heading }}
                            </v-subheader>
                        </v-col>
                        <v-col
                            cols="6"
                            class="text-center"
                        >
                            <a
                                href="#!"
                                class="body-2 black--text"
                            >EDIT</a>
                        </v-col>
                    </v-row>
                    <v-list-group
                        v-else-if="item.children"
                        :key="item.text"
                        v-model="item.model"
                        :prepend-icon="item.model ? item.icon : item['icon-alt']"
                        append-icon=""
                    >
                        <template v-slot:activator>
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ item.text }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </template>
                        <v-list-item
                            v-for="(child, i) in item.children"
                            :key="i"
                            :to="child.to"
                            :exact="child.exact"
                        >
                            <v-list-item-action v-if="child.icon">
                                <v-icon>{{ child.icon }}</v-icon>
                            </v-list-item-action>
                            <v-list-item-content>
                                <v-list-item-title >
                                    {{ child.text }}
                                </v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list-group>
                    <v-list-item
                        v-else
                        :key="item.text"
                        :to="item.to"
                        :exact="item.exact"
                    >
                        <v-list-item-action>
                            <v-icon>{{ item.icon }}</v-icon>
                        </v-list-item-action>
                        <v-list-item-content>
                            <v-list-item-title>
                                {{ item.text }}
                            </v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </template>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar
            :clipped-left="$vuetify.breakpoint.lgAndUp"
            app
            :color="$vuetify.theme.themes.light.primary"
        >
            <v-toolbar-title
                style="width: 400px"
                class="ml-0 pl-4"
            >
                <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
                <span class="hidden-sm-and-down">{{ title }}</span>
            </v-toolbar-title>
            <v-text-field
                flat
                solo-inverted
                hide-details
                prepend-inner-icon="search"
                label="Search"
                class="hidden-sm-and-down"
            ></v-text-field>
            <div class="flex-grow-1"></div>
        </v-app-bar>
        <v-content>
            <v-container
                class="fill-height"
                fluid
            >
                <v-layout child-flex>
                    <router-view>
                    </router-view>
                </v-layout>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
    export default {
        props: {
            source: String,
            title: String,
            menuItems: Array
        },
        data: () => ({
            dialog: false,
            drawer: null,
        }),
    }
</script>
