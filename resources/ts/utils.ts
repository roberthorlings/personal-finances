/**
 * Converts an array with items and its children into a single level list where children are prefixed with a '--'
 * @param items
 * @param prefix
 */
export const toFlatList = (items: Array<any>, prefix = ''): Array<any> => {
    if (!items || items.length === 0)
        return [];

    return items.reduce((list, item) => {
        return [
            ...list,
            {
                ...item,
                text: `${prefix} ${item.name}`
            },
            ...toFlatList(item.children, prefix + '--')
        ];
    }, []);
};
