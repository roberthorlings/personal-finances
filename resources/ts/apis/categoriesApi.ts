import {Identifyable} from "./genericApi";
import axios from "axios";
import genericWithStatsApi from "./genericWithStatsApi";

const API_ENDPOINT = '/api/categories';

export interface Category extends Identifyable {
    name: string,
    parent_id: number,
    key?: string,
    children?: Category[],
    created_at: Date,
    updated_at: Date
}

export interface CategoryStat extends Identifyable {
    category: Category,
    subtotal: number,
    total: string,
    children?: CategoryStat[]
};

const tree = (): Promise<Category[]> =>
    axios.get(API_ENDPOINT + '/tree')
        .then(response => response.data.data);

const stats = (): Promise<CategoryStat[]> =>
    axios.get(API_ENDPOINT + '/stats')
        .then(response => response.data.data);

export default {
    ...genericWithStatsApi<Category>(API_ENDPOINT),
    tree,
    stats
};
