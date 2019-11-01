import axios from 'axios';
import {Identifyable} from "./genericApi";
import {Category} from "./categoriesApi";
import genericWithStatsApi from "./genericWithStatsApi";

const API_ENDPOINT = '/api/transactions';

export interface OpposingAccount {
    name: string,
    iban: string
}

export interface Transaction extends Identifyable {
    date: string,
    description: string,
    amount: number,
    account: Account,
    opposing_account: OpposingAccount,
    category: Category,
    created_at: Date,
    updated_at: Date
}

const importTransactions = (type: string, file: File, dryRun: boolean) => {
    var formData = new FormData();
    formData.append("file", file);
    formData.append("type", type);

    if (dryRun) {
        formData.append("dryRun", 'true');
    }

    return axios.post(API_ENDPOINT + '/import', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
};

export default {
    ...genericWithStatsApi<Transaction>(API_ENDPOINT),
    importTransactions
};
