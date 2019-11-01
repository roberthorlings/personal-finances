export interface DateRange {
    [index: number]: string;
}

const pad = (num: number, size: number): string => String(num).padStart(size, '0');
const format = (y: number, m: number, d: number) => y + '-' + pad(m, 2) + '-' + pad(d, 2);
const formatDate = (date: Date) => format(date.getFullYear(), date.getMonth() + 1, date.getDate());

const year = (y: number): DateRange => [format(y, 1, 1), format(y, 12, 31)];
const month = (y: number, m: number): DateRange => [formatDate(new Date(y, m - 1, 1)), formatDate(new Date(y, m, 0))];

const today = new Date();

export default {
    year,
    month,
    current: {
        year: year(today.getFullYear()),
        month: month(today.getFullYear(), today.getMonth() + 1)
    },
    last: {
        year: year(today.getFullYear() - 1),
        month: month(today.getFullYear(), today.getMonth())
    },
    previous: {
        year: (y: number) => year(y - 1),
        month: (y: number, m: number) => month(y, m - 1)
    },
    next: {
        year: (y: number) => year(y + 1),
        month: (y: number, m: number) => month(y, m + 1)
    }
};
