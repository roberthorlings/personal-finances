<template>
    <v-simple-table v-if="!loadingBalances">
        <thead>
            <tr>
                <th>Account</th>
                <th>IBAN</th>
                <th>Start balance</th>
                <th>End balance</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="account in accounts">
                <td>{{account.name}}</td>
                <td>{{account.iban}}</td>
                <td><v-chip :color="account.startBalance < 0 ? 'red' : ' green'" dark>{{ account.startBalance | toCurrency }}</v-chip></td>
                <td><v-chip :color="account.endBalance < 0 ? 'red' : ' green'" dark>{{ account.endBalance | toCurrency }}</v-chip></td>
            </tr>
        </tbody>
    </v-simple-table>
</template>

<script>
    import accountsApi from "../../apis/accountsApi";
    import DateRange from "../../DateRange";

    export default {
        name: 'AccountBalances',
        data: () => ({
            loadingBalances: false,
            accounts: [],
        }),
        mounted() {
            this.getBalances();
        },
        methods: {
            getBalances: async function() {
                this.loadingBalances = true;

                if(!this.year) {
                    // If no year is given, only show current balance, which is returned
                    // by default
                    const accounts = await accountsApi.list();

                    this.accounts = accounts.items.map(account => ({
                        ...account,
                        startBalance: 0,
                        endBalance: account.balance
                    }));
                } else {
                    // The balances are measured at the end of each month. So, the start
                    // balance for january is the balance measured at december previous year
                    const startBalances = await accountsApi.stats({year: this.year - 1, month: 12});
                    const endBalances = await accountsApi.stats({year: this.year, month: 12});

                    // Combine the two lists.
                    this.accounts = startBalances.map(accountData => {
                        const startBalance = accountData.stats ? accountData.stats.balance : 0;
                        const endBalanceAccount = endBalances.find(otherAccountData => otherAccountData.account.id == accountData.account.id);
                        const endBalance = endBalanceAccount && endBalanceAccount.stats ? endBalanceAccount.stats.balance : 0;

                        return {
                            ...accountData.account,
                            startBalance,
                            endBalance
                        }
                    });
                }

                this.loadingBalances = false;
            }
        },
        props: {
            year: { type: Number }
        },
        watch: {
            year: function() { this.getBalances(); }
        }

    }
</script>
