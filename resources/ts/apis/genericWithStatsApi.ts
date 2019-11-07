import axios from 'axios';
import genericApi, {Identifyable} from "./genericApi";

export default <T extends Identifyable>(endpoint: string) => {
    const generateStats = () => axios.post(endpoint + '/stats');
    return {
        ...genericApi<T>(endpoint),
        generateStats
    };
}
