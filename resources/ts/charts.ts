import {CategoryStat, SingleStat} from "./types";
import {groupBy, sortBy, toWindows} from "./utils";

export interface CategoryDescription {
    id: number,
    name: String
}

export interface ChartSerieEntry {
    name: String,
    y: number,
    drilldown?: number
}

export const convertStatsToDrilldownData = ({id, name}: CategoryDescription, children: CategoryStat[] = [], subtotal: number = 0) => {
    const drilldownSeries = [];

    if(children.length == 0) {
        return [];
    }

    // Add drilldown series for children, if they have children as well
    children.forEach(child => {
        drilldownSeries.push(...convertStatsToDrilldownData(child.category, child.children, child.subtotal));
    });

    // Generate a drilldownserie for this node
    const data = children.map(stat => {
        const hasChildren = stat.children && stat.children.length > 0;

        // Skip this category if there is no amount
        if(Math.round(stat.total) == 0) {
            return undefined;
        }

        return {
            name: stat.category.name,
            y: stat.total,
            drilldown: hasChildren ? stat.category.id : null
        } as ChartSerieEntry;
    });

    // If the subtotal for this node is non zero, add it as well
    if(subtotal != 0) {
        data.push({
            name: 'Overig',
            y: subtotal,
        })
    }

    // Ensure that there are no undefined values in the list
    // See https://codereview.stackexchange.com/a/138289
    const validData: ChartSerieEntry[] = data.filter(s => s !== undefined) as ChartSerieEntry[];

    // Add a drilldownserie for the current node
    drilldownSeries.push({
        id,
        name,
        data: validData.sort((a, b) => (Math.abs(b.y) - Math.abs(a.y)))
    });

    return drilldownSeries;
};

export const convertCategoryStatsToSeriesData = (stats: CategoryStat[]) => {
    const includedStats = stats;

    const series = convertStatsToDrilldownData({id: 0, name: 'All'}, includedStats);
    const rootSerie = series.find(serie => serie.id === 0);

    return {
        initialSerie: rootSerie,
        drilldownSeries: series.filter(serie => serie && (!rootSerie || serie.id != rootSerie.id))
    }
};

export const convertSingleStatsToMonthlySeriesData = (stats: SingleStat[]): ChartSerieEntry[] => {
    return stats
        .sort(sortBy('year', 'month'))
        .map(stat => ({
           name: new Date(stat.year, stat.month - 1, 1).toLocaleString('default', { month: 'long', year: 'numeric'}),
           y: stat.total
        }));
};

export const convertSingleStatsToYearlySeriesData = (stats: SingleStat[]): ChartSerieEntry[] => {
    return Object.entries(groupBy('year')(stats))
        .map(([year, yearStats]) => ({
            name: year,
            y: yearStats.reduce((acc: number, current: any) => acc + current.total, 0)
        }))
        .sort(sortBy('name'));
};

export const runningAverage = (stats: ChartSerieEntry[]): ChartSerieEntry[] => {
    const windowSize = 3;
    const nameIndex = Math.floor(windowSize / 2);
    return toWindows(stats, windowSize)
        .map((window: ChartSerieEntry[]) => ({
            name: window[nameIndex].name,
            y: window.reduce((acc: number, current: any) => acc + current.y, 0) / windowSize
        }));
}
