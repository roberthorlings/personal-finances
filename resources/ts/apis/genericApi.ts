import axios from 'axios';

export interface ListOptions {
    sortBy: string[],
    sortDesc: boolean[],
    descending: boolean,
    page: number,
    itemsPerPage: number
}

export interface ListResult<T> {
    items: T[],
    total: number
}

export interface Identifyable {
    id: number
}

const itemEndpoint = (endpoint: string, id: number) => endpoint + '/' + id;

export default <T extends Identifyable>(endpoint: string) => {
    const list = (options: ListOptions): Promise<ListResult<T>> =>
        axios.get(
            endpoint,
            {
                params: {
                    sortBy: options.sortBy[0],
                    sortOrder: options.sortDesc[0] ? 'desc' : 'asc',
                    page: options.page || 1,
                    per_page: options.itemsPerPage > -1 ? options.itemsPerPage : undefined
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
