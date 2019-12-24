<template>
    <v-simple-table v-if="!loadingBalances">
        <thead>
            <tr>
                <th>Account</th>
                <th>IBAN</th>
                <th align="right">Start balance</th>
                <th align="right">End balance</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="account in accounts">
                <td>{{account.name}}</td>
                <td>{{account.iban}}</td>
                <td align="right">{{ account.startBalance | toCurrency }}</td>
                <td align="right">{{ account.endBalance | toCurrency }} </td>
            </tr>
        </tbody>
        <tfoot class="total">
            <tr>
                <td colspan="2">Total</td>
                <td align="right">{{ sum.startBalance | toCurrency }}</td>
                <td align="right">{{ sum.endBalance | toCurrency }}</td>
            </tr>
        </tfoot>
    </v-simple-table>
</template>

<style>
    tfoot.total td {
        border-top: 1px solid rgba(0, 0, 0, 0.12);
        font-weight: bold;
    }
</style>

<script>
    import accountsApi from "../../apis/accountsApi";
    import DateRange from "../../DateRange";

    export default {
        name: 'AccountBalances',
        computed: {
            sum() {
                return this.accounts.reduce(
                    (acc, current) => ({
                        startBalance: acc.startBalance + current.startBalance,
                        endBalance: acc.endBalance + current.endBalance
                    }),
                    {startBalance: 0, endBalance: 0}
                )
            }
        },
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

                    this.accounts = accounts.items
                        .filter(account => account.balance > 0)
                        .map(account => ({
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
                    this.accounts = startBalances
                        .map(accountData => {
                            const startBalance = accountData.stats ? accountData.stats.balance : 0;
                            const endBalanceAccount = endBalances.find(otherAccountData => otherAccountData.account.id == accountData.account.id);
                            const endBalance = endBalanceAccount && endBalanceAccount.stats ? endBalanceAccount.stats.balance : 0;

                            return {
                                ...accountData.account,
                                startBalance,
                                endBalance
                            }
                        })
                        .filter(data => data.startBalance != 0 || data.endBalance != 0);
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
