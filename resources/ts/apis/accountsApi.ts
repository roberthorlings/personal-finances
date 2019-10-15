import {Identifyable} from "./genericApi";
import genericWithStatsApi from "./genericWithStatsApi";

const API_ENDPOINT = '/api/accounts';

export interface Account extends Identifyable {
    name: string,
    iban: string,
    created_at: Date,
    updated_at: Date
}

export default genericWithStatsApi<Account>(API_ENDPOINT);
