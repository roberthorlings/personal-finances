import Home from './components/Home';
import Categories from './components/Categories';
import Accounts from './components/Accounts';
import Transactions from "./components/Transactions";

export default [
    {path: '/', component: Home},
    {path: '/categories', component: Categories},
    {path: '/accounts', component: Accounts},
    {path: '/transactions', component: Transactions},
];
