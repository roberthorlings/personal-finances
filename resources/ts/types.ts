import {Identifyable} from "./apis/genericApi";
import {Category} from "./apis/categoriesApi";

export interface KeyboardShortcut {
    sequence: Array<String>,
    callback: Function
};

export interface StatsParams {
    year?:  number,
    month?: number
}

export interface CategoryStat extends Identifyable {
    category: Category,
    subtotal: number,
    total: number,
    children?: CategoryStat[]
};

export interface SingleStat {
    month: number,
    year: number,
    subtotal: number,
    total: number,
};
