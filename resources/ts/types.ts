import {Identifyable} from "./apis/genericApi";
import {Category} from "./apis/categoriesApi";

export interface KeyboardShortcut {
    sequence: Array<String>,
    callback: Function
};

export interface CategoryStat extends Identifyable {
    category: Category,
    subtotal: number,
    total: number,
    children?: CategoryStat[]
};
