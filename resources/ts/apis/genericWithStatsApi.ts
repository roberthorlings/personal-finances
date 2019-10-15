import axios from 'axios';
import genericApi, {Identifyable} from "./genericApi";

export default <T extends Identifyable>(endpoint: string) => {
    const stats = () => axios.post(endpoint + '/stats');
    return {
        ...genericApi<T>(endpoint),
        stats
    };
}
