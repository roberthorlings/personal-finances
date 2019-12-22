import {Identifyable} from "./genericApi";
import genericWithStatsApi from "./genericWithStatsApi";
import {CategoryStat, StatsParams} from "../types";
import axios from "axios";
import {Category} from "./categoriesApi";

const API_ENDPOINT = '/api/accounts';

export interface Account extends Identifyable {
    name: string,
    iban: string,
    created_at: Date,
    updated_at: Date
}

const stats = (params?: StatsParams): Promise<CategoryStat[]> =>
    axios.get(API_ENDPOINT + '/stats', {params})
        .then(response => response.data.data);

export default {
    ...genericWithStatsApi<Category>(API_ENDPOINT),
    stats,
};

