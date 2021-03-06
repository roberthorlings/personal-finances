import Home from './components/Home';
import Categories from './components/Categories';
import Accounts from './components/Accounts';
import Transactions from "./components/Transactions";
import Reports from "./components/Reports";
import AllCategoriesReport from "./components/AllCategoriesReport";

export default [
    {path: '/', component: Home},
    {path: '/categories', component: Categories},
    {path: '/accounts', component: Accounts},
    {path: '/transactions', component: Transactions},
    {path: '/reports', component: Reports},
    {path: '/reports/category', component: AllCategoriesReport},
];
