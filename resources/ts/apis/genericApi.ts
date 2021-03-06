import axios from 'axios';

export interface ListOptions {
    sortBy: string[],
    sortDesc: boolean[],
    page: number,
    itemsPerPage: number,
    skip?: number,
    filters?: object
}

export interface ListResult<T> {
    items: T[],
    total: number
}

export interface Identifyable {
    id: number
}

const itemEndpoint = (endpoint: string, id: number) => endpoint + '/' + id;
const DEFAULT_LIST_OPTIONS = {
    sortBy: [],
    sortDesc: [],
    page: 1,
    itemsPerPage: 25
};

export default <T extends Identifyable>(endpoint: string) => {
    const list = (options: ListOptions = DEFAULT_LIST_OPTIONS): Promise<ListResult<T>> =>
        axios.get(
            endpoint,
            {
                params: {
                    ...(options.filters ? options.filters : {}),
                    sortBy: options.sortBy[0],
                    sortOrder: options.sortDesc[0] ? 'desc' : 'asc',
                    page: options.page || 1,
                    per_page: options.itemsPerPage > -1 ? options.itemsPerPage : undefined,
                    skip: options.skip
                }
            }
        ).then(response => ({
            items: response.data.data,
            total: response.data.meta.total
        }));

    const get = (id: number): Promise<T> => axios.get(itemEndpoint(endpoint, id)).then(response => response.data);
    const create = (item: T): Promise<T> => axios.post(endpoint, item).then(response => response.data);
    const update = (item: T): Promise<T> => axios.put(itemEndpoint(endpoint, item.id), item).then(response => response.data);
    const destroy = (id: number): Promise<any> => axios.delete(itemEndpoint(endpoint, id));
    const store = (item: T): Promise<T> => item.id ? update(item) : create(item);

    return {
        list,
        get,
        create,
        update,
        destroy,
        store
    };
}
