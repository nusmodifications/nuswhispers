import axios from 'axios';
import camelCaseKeys from 'camelcase-keys';

export default axios.create({
  // Transforms response to have camelCase keys for OCD-ness.
  transformResponse: (data: any) =>
    camelCaseKeys(JSON.parse(data), { deep: true }),
});
