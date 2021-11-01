
import axios from "axios";
import qs from "qs";
import store from "../store";

//Strip the last fragment of the path, and appends the api path.
const baseUrl = location.protocol + "//" + location.host + location.pathname.replace(/\/[^\/]*$/, "") + "/api";
//console.log("LOCATION:",location);
const api = axios.create({
    baseURL: baseUrl
});
// axios.defaults.headers.common = { 'Authorization': `Bearer ${store.getters.getAccessToken}` };

// Add api method here
export default {
    createBoard: function ({ trackerId, itemId }, payload) {
        return api.post(`/trackers/${trackerId}/`, qs.stringify(payload), {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    createItem: function ({ trackerId }, payload) {
        // Sample
        // fields[fieldPermName]=value&fields[anotherFieldPermName]=anotherValue
        return api.post(`/trackers/${trackerId}/items`, qs.stringify(payload, { encode: false }), {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    getItem: function ({ trackerId, itemId }, payload) {
        return api.get(`/trackers/${trackerId}/items/${itemId}`, qs.stringify(payload), {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    setItem: function ({ trackerId, itemId }, payload) {
        return api.post(`/trackers/${trackerId}/items/${itemId}`, qs.stringify(payload, { encode: false }), {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    deleteItem: function ({ trackerId, itemId }) {
        return api.delete(`/trackers/${trackerId}/items/${itemId}`, {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    getField: function ({ trackerId, fieldId }, payload) {
        return api.get(`/trackers/${trackerId}/fields/${fieldId}`, qs.stringify(payload), {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    setField: function ({ trackerId, fieldId }, payload) {
        return api.post(`/trackers/${trackerId}/fields/${fieldId}`, qs.stringify(payload), {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    deleteField: function ({ trackerId, fieldId }) {
        return api.delete(`/trackers/${trackerId}/fields/${fieldId}`, {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    },
    getUsers: function () {
        return api.get(`/users`, {
            headers: {
                Authorization: `Bearer ${store.getters.getAccessToken}`
            }
        });
    }
};