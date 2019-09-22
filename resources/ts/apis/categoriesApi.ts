import genericApi, {Identifyable, ListOptions, ListResult} from "./genericApi";
import axios from "axios";

const API_ENDPOINT = '/api/categories';

export interface Category extends Identifyable {
    name: string,
    parent_id: number,
    children?: Category[],
    created_at: Date,
    updated_at: Date
}

const tree = (): Promise<Category[]> =>
    axios.get(API_ENDPOINT + '/tree')
        .then(response => response.data.data);

export default {
    ...genericApi<Category>(API_ENDPOINT),
    tree
};
