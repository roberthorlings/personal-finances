import genericApi, {Identifyable} from "./genericApi";

const API_ENDPOINT = '/api/accounts';

export interface Account extends Identifyable {
    name: string,
    iban: string,
    created_at: Date,
    updated_at: Date
}

export default genericApi<Account>(API_ENDPOINT);
