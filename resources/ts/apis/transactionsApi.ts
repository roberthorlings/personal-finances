import genericApi, {Identifyable} from "./genericApi";
import {Category} from "./categoriesApi";

const API_ENDPOINT = '/api/transactions';

export interface OpposingAccount {
    name: string,
    iban: string
}

export interface Transaction extends Identifyable {
    date: Date,
    description: string,
    amount: number,
    account: Account,
    opposing_account: OpposingAccount,
    category: Category,
    created_at: Date,
    updated_at: Date
}
export default genericApi<Transaction>(API_ENDPOINT);
