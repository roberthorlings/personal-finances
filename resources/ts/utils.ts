/**
 * Converts an array with items and its children into a single level list where children are prefixed with a '--'
 * @param items
 * @param prefix
 */
export const toFlatList = (items: Array<any>, prefix = '', nameProvider = (item: any) => item.name): Array<any> => {
    if (!items || items.length === 0)
        return [];

    return items.reduce((list, item) => {
        return [
            ...list,
            {
                ...item,
                text: `${prefix} ${nameProvider(item)}`
            },
            ...toFlatList(item.children, prefix + '--')
        ];
    }, []);
};

/**
 * Groups an array of objects based on one of their keys
 *
 * Adjusted from {@link https://gist.github.com/JamieMason/0566f8412af9fe6a1d470aa1e089a752}
 * @param key
 */
export const groupBy = (key: string) => (array: Array<any>): object =>
    array.reduce((objectsByKeyValue, obj) => {
        const value = obj[key];
        objectsByKeyValue[value] = (objectsByKeyValue[value] || []).concat(obj);
        return objectsByKeyValue;
    }, {});

/**
 * Array comparator based on one or more keys
 *
 * @param keys
 */
export const sortBy = (...keys: string[]) => (a: any, b: any) => {
    // Without keys, we can't compare
    if(!keys) {
        return 0;
    }

    for(const key of keys) {
        if(a[key] < b[key])
            return -1;
        if(b[key] < a[key])
            return 1;
    }

    return 0;
};

/**
 * Generates a sliding window over the given array
 * @param inputArray
 * @param size
 * @see https://stackoverflow.com/questions/57001515/sliding-window-over-array-in-javascript
 */
export const toWindows = (inputArray: any[], size: number) => {
    return inputArray
        .reduce((acc, _, index, arr) => {
            if (index+size > arr.length) {
                //we've reached the maximum number of windows, so don't add any more
                return acc;
            }

            //add a new window of [currentItem, maxWindowSizeItem)
            return acc.concat(
                //wrap in extra array, otherwise .concat flattens it
                [arr.slice(index, index+size)]
            );

        }, [])
}
