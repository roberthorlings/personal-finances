import {CategoryStat} from "./types";

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

export const convertStatsToSeriesData = (stats: CategoryStat[]) => {
    const includedStats = stats; // .filter(stat => stat.total < 0);

    const series = convertStatsToDrilldownData({id: 0, name: 'Uitgaven'}, includedStats);
    const rootSerie = series.find(serie => serie.id === 0);

    return {
        initialSerie: rootSerie,
        drilldownSeries: series.filter(serie => serie && (!rootSerie || serie.id != rootSerie.id))
    }
};
